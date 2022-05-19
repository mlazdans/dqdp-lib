<?php declare(strict_types = 1);

namespace dqdp;

use dqdp\DBA;
use dqdp\DBA\AbstractDBA;

// write, read to/from file descriptor
// mkdir, rmdir, creat, unlink, link, symlink
// stat, utimes, chmod, chown, chgrp
// Path names are case sensitive, components are separated with forward slash (/).

class FS implements DBA\TransactionInterface {
	var $uid;
	protected $Ent;

	function __construct($uid = NULL){
		$this->uid = $uid;
		$this->Ent = new FS\Entity;
	}

	function set_trans(AbstractDBA $dba) {
		$this->Ent->set_trans($dba);

		return $this;
	}

	function get_trans(): AbstractDBA {
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
		$params = $this->defaults_params($params);
		//$params["fs_fullpath_hash"] = $this->path($path);
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
		$params = $this->defaults_params();
		$params->fs_type = 1;

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function file_exists($path) : bool {
		$params = $this->defaults_params();
		$params->fields = ["fs_fsid"];

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function rm($path){
		$params = $this->defaults_params();
		$params->fs_fullpath = $path;
		//$sql = "DELETE FROM $this->Table WHERE fs_fullpath_hash = SHA1(?)";
		$sql = "DELETE FROM $this->Table WHERE fs_fullpath = ?";

		return $this->get_trans()->Execute($sql, $path) ? true : false;
	}

	function dirmax($path){
		$params = $this->defaults_params();
		$params->get_dir_max = $this->path($path);
		if($params->get_dir_max != '/'){
			$params->get_dir_max .= '/';
		}

		return $this->get_all($params);
	}

	function rmtree($path){
		$max = $this->dirmax($path);
		$ret = $this->Ent->delete(getbyk($max, 'fs_id'));

		return $ret;
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
		$d = $this->get_by_fullpath($this->path($path), $this->defaults_params());

		if(!$d){
			return false;
		}

		// if($sort == SCANDIR_SORT_ASCENDING){
		// 	$orderby = "$this->Table.fs_fullpath ASC";
		// } elseif($sort == SCANDIR_SORT_DESCENDING){
		// 	$orderby = "$this->Table.fs_fullpath DESC";
		// }

		$params = $this->defaults_params($params);
		$params->fs_fsid = get_prop($d, 'fs_id');

		return $this->get_all($params);
	}

	private function path($path){
		return "/".trim($path, "/");
	}
}
