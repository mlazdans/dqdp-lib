<?php declare(strict_types = 1);

namespace dqdp;

use dqdp\DBA\DBA;
use dqdp\DBA\TransactionInterface;

// write, read to/from file descriptor
// mkdir, rmdir, creat, unlink, link, symlink
// stat, utimes, chmod, chown, chgrp
// Path names are case sensitive, components are separated with forward slash (/).

# TODO: fs_type const
# TODO: do not return content by default

class FS implements TransactionInterface {
	var $uid;
	protected $Ent;

	function __construct($uid = NULL){
		$this->uid = $uid;
		$this->Ent = new FS\Entity;
	}

	function set_trans(DBA $dba): FS {
		$this->Ent->set_trans($dba);

		return $this;
	}

	function get_trans(): DBA {
		return $this->Ent->get_trans();
	}

	# Nav īsti skaisti, bet lai neaizmirstās pielikt uid
	private function defaults_params($params = null){
		$params = eo($params);
		if($this->uid){
			$params->fs_uid = $this->uid;
		}

		return $params;
	}

	private function get_single($params = null){
		return $this->Ent->get_single($this->defaults_params($params));
	}

	private function get_all($params = null){
		return $this->Ent->get_all($this->defaults_params($params));
	}

	function get_by_fullpath(string $path, $params = null): array {
		$params = eo($params);
		$params->fs_fullpath = $this->path($path);

		return ($data = $this->get_single($params)) ? $data : [];
	}

	function read($path){
		if($data = eo($this->get_by_fullpath($path, ['get_contents'=>1, 'fs_type'=>0]))){
			return $data->fs_contents;
		}

		return false;
	}

	function write($path, $contents){
		$exists = eo($this->get_by_fullpath($path));
		if($exists->fs_id){
			if($exists->fs_type == 1){
				return false;
			}

			$DATA = [
				'fs_id'=>$exists->fs_id,
				'fs_fullpath'=>$exists->fs_fullpath,
				'fs_contents'=>$contents,
				'fs_size'=>strlen($contents),
				'fs_updated'=>function(){
					return "CURRENT_TIMESTAMP";
				}
			];
		} else {
			$parts = $this->explode_path($path);
			$file_name = array_pop($parts);
			$pi = pathinfo($file_name);
			//$fn_parts = explode(".", $file_name);
			//printr("\n", $pi);
			// $fs_ext = array_pop($fn_parts);
			// $fs_name = join(".", $fn_parts);
			$fs_name = $pi['filename']??null;
			$fs_ext = $pi['extension']??null;
			$dir = join("/", $parts);
			if($fs_fsid = $this->mkdir($dir)){
				$DATA = [
					'fs_fsid'=>$fs_fsid,
					'fs_uid'=>$this->uid,
					'fs_depth'=>count($parts),
					'fs_type'=>0,
					'fs_name'=>$fs_name,
					'fs_ext'=>$fs_ext,
					'fs_fullname'=>$file_name,
					'fs_fullpath'=>"$dir/$file_name",
					'fs_contents'=>$contents,
					'fs_size'=>strlen($contents),
				];
			}
		}

		if(!isset($DATA)){
			return false;
		}

		if($fs_mime = get_mime($contents)){
			$DATA['fs_mime'] = $fs_mime;
		}

		return $this->Ent->save($DATA);
	}

	function is_dir($path) : bool {
		return $this->get_by_fullpath($path, ['fs_type'=>1]) ? true : false;
	}

	function file_exists($path) : bool {
		return $this->get_by_fullpath($path, ['fields'=>["fs_fsid"]]) ? true : false;
	}

	function rm($path){
		# TODO: add default params!!!
		$params = $this->defaults_params();
		$params->fs_fullpath = $path;

		return $this->get_trans()->query("DELETE FROM fs WHERE fs_fullpath = ?", $path) ? true : false;
	}

	function dirmax($path){
		$get_dir_max = $this->path($path);
		if($get_dir_max != '/'){
			$get_dir_max .= '/';
		}

		return $this->get_all(['get_dir_max'=>$get_dir_max]);
	}

	function rmtree($path){
		$max = $this->dirmax($path);

		return $this->Ent->delete(getbyk($max, 'fs_id'));
	}

	private function explode_path($path){
		if($path == "/"){
			return [''];
		} else {
			return explode("/", $this->path($path));
		}
	}

	function mkdir($path){
		$fs_fsid = NULL;
		$fs_fullpath = '';
		$parts = $this->explode_path($this->path($path));
		foreach($parts as $i=>$p){
			$fs_fullpath .= "$p/";
			// $exists = eo(($e = $this->get_by_fullpath($fs_fullpath)) ? ktolower($e) : []);
			$exists = eo($this->get_by_fullpath($fs_fullpath));

			if(!is_empty($exists)){
				$fs_fsid = $exists->fs_id;
				if($exists->fs_type == 0){ // fail, file
					return false;
				}
			} else {
				$params = [
					'fs_fsid'=>$fs_fsid,
					'fs_uid'=>$this->uid,
					'fs_depth'=>$i,
					'fs_type'=>1,
					'fs_name'=>$p,
					'fs_fullname'=>$p,
					'fs_fullpath'=>$this->path($fs_fullpath),
				];

				if(!($fs_fsid = $this->Ent->save($params))){
					return false;
				}
			}
		}

		return $fs_fsid;
	}

	function scandir($path, $params = null){
		if(($data = $this->scandirraw($path, $params)) === false){
			return false;
		}

		return getbyk($data, 'fs_fullname');
	}

	function scandirraw($path, $params = null){
		$d = eo($this->get_by_fullpath($this->path($path)));

		if(is_empty($d)){
			return false;
		}

		// if($sort == SCANDIR_SORT_ASCENDING){
		// 	$orderby = "$this->Table.fs_fullpath ASC";
		// } elseif($sort == SCANDIR_SORT_DESCENDING){
		// 	$orderby = "$this->Table.fs_fullpath DESC";
		// }

		$params = eo($params);
		$params->fs_fsid = get_prop($d, 'fs_id');

		return $this->get_all($params);
	}

	private function path($path){
		return "/".trim($path, "/");
	}
}
