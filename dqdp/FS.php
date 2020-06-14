<?php
// write, read to/from file descriptor
// mkdir, rmdir, creat, unlink, link, symlink
// stat, utimes, chmod, chown, chgrp
// Path names are case sensitive, components are separated with forward slash (/).
// INSERT INTO fs (fs_id, fs_fsid, fs_uid, fs_depth, fs_type, fs_name, fs_fullpath, fs_fullpath_hash) VALUES (1, NULL, NULL, 0, 1, '/', '/', SHA1('/'))

namespace dqdp;

use dqdp\DBA;

class FS implements DBA\TransInterface {
	var $uid;
	protected $Ent;

	function __construct($uid = NULL){
		$this->uid = $uid;
		$this->Ent = new FS\Entity;
	}

	function set_trans(DBA $dba) {
		$this->Ent->set_trans($dba);
		return $this;
	}

	function get_trans(): DBA {
		return $this->Ent->get_trans();
	}

	# Nav īsti skaisti, bet lai neaizmirstās pielikt uid
	private function defaults_params($params){
		if($this->uid){
			$params['fs_uid'] = $this->uid;
		}
		return $params;
	}

	private function get_single($params = []){
		return $this->Ent->get_single($this->defaults_params($params));
	}

	private function get_all($params = []){
		return $this->Ent->get_all($this->defaults_params($params));
	}

	function get_by_fullpath($path, $params = []){
		$params["fs_fullpath_hash"] = $this->path($path);

		return $this->get_single($params);
	}

	function read($path){
		if($data = $this->get_by_fullpath($path, ['get_contents'=>1, 'fs_type'=>0])){
			return $data['fs_contents'];
		}

		return false;
	}

	function write($path, $contents){
		if($data = $this->get_by_fullpath($path)){
			if($data['fs_type'] == 1){
				return false;
			}
			$params = [
				'fs_id'=>$data['fs_id'],
				'fs_fullpath'=>$data['fs_fullpath'],
				'fs_contents'=>$contents,
				'fs_size'=>strlen($contents),
			];
		} else {
			$parts = explode("/", $this->path($path));
			$file_name = array_pop($parts);
			$fn_parts = explode(".", $file_name);
			$fs_ext = array_pop($fn_parts);
			$fs_name = join(".", $fn_parts);
			$dir = join("/", $parts);
			if($fs_fsid = $this->mkdir($dir)){
				$params = [
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

		if(!isset($params)){
			return false;
		}

		if($fs_mime = get_mime($contents)){
			$params['fs_mime'] = $fs_mime;
		}

		return $this->Ent->save($params);
	}

	function is_dir($path){
		$params = ["fs_type"=>1];

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function file_exists($path){
		$params = ["fields"=>["fs_fsid"]];

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function rm($path){
		$sql = "DELETE FROM $this->Table WHERE fs_fullpath_hash = SHA1(?)";

		return $this->get_trans()->Execute($sql, [$path]) ? true : false;
	}

	function dirmax($path){
		$params = ["get_dir_max"=>$this->path($path).'/'];

		return $this->get_all($params);
	}

	function rmtree($path){
		$ret = true;
		$max = $this->dirmax($path);

		foreach($max as $entry){
			if(!($ret = $this->rm($entry['fs_fullpath']))){
				return false;
			}
		}

		return $ret;
	}

	function mkdir($path){
		$fs_fsid = NULL;
		$fs_fullpath = '';
		$parts = explode("/", $this->path($path));
		foreach($parts as $i=>$p){
			$fs_fullpath .= "$p/";
			if($exists = $this->get_by_fullpath($fs_fullpath)){
				$fs_fsid = $exists['fs_id'];
				if($exists['fs_type'] == 0){ // fail, file
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

	function scandir($path, $params = []){
		return ($data = $this->scandirraw($path, $params)) ? array_getk($data, 'fs_name') : false;
	}

	function scandirraw($path, $params = []){
		$d = $this->get_by_fullpath($this->path($path));
		// if($sort == SCANDIR_SORT_ASCENDING){
		// 	$orderby = "$this->Table.fs_fullpath ASC";
		// } elseif($sort == SCANDIR_SORT_DESCENDING){
		// 	$orderby = "$this->Table.fs_fullpath DESC";
		// }

		$params['fs_fsid'] = $d['fs_id'];

		return $this->get_all($params);
	}

	private function path($path){
		return "/".trim($path, "/");
	}
}
