<?php

// mount, umount the file system
// open, close file descriptor
// write, read to/from file descriptor
// mkdir, rmdir, creat, unlink, link, symlink
// fcntl (byte range locks, etc.)
// stat, utimes, chmod, chown, chgrp
// Path names are case sensitive, components are separated with forward slash (/).
// INSERT INTO fs (fs_id, fs_fsid, fs_uid, fs_depth, fs_type, fs_name, fs_fullpath, fs_fullpath_hash) VALUES (1, NULL, NULL, 0, 1, '/', '/', SHA1('/'))

namespace dqdp;

use dqdp\DB\MySQLEntity;
use dqdp\SQL\Select;

class FS extends MySQLEntity {
	var $uid;
	//var $cwd = "/";
	var $db;

	function __construct($uid){
		$this->Table = 'fs';
		$this->PK = 'fs_id';
		$this->uid = $uid;
	}

	function sql_select(){
		return (new Select("fs_id, fs_fsid, fs_uid, fs_depth, fs_type, fs_name, fs_fullpath, fs_fullpath_hash, fs_entered, fs_updated"))->From($this->Table);
	}

	function set_filters($sql, $DATA = null){
		$DATA = eoe($DATA);

		$filters = ['fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_fullpath', 'fs_contents', 'fs_entered', 'fs_updated'];
		$this->set_null_filters($sql, $DATA, $filters, "$this->Table.");

		# TODO: old pass get rid off
		if($DATA->isset('fs_fullpath_hash')){
			$sql->Where(["fs_fullpath_hash = SHA1(?)", $DATA->fs_fullpath_hash]);
		}

		if($DATA->get_dir_max){
			$sql
			->Where("fs_type = 1")
			->Where(["fs_fullpath LIKE ?", $DATA->get_dir_max."%"])
			->OrderBy("fs_depth DESC")
			;
		}

		if($DATA->get_contents){
			$sql->Select("fs_contents");
		}

		parent::set_filters($sql, $DATA);
	}

	function save(){
		list($DATA) = func_get_args();

		$DATA = eo($DATA);

		$fields = ['fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_fullpath', 'fs_fullpath_hash', 'fs_contents', 'fs_entered', 'fs_updated'];

		if(!$DATA->isset('fs_fullpath_hash')){
			$fs_fullpath = $DATA->fs_fullpath;
			$DATA->fs_fullpath_hash = function() use ($fs_fullpath) {
				return ["SHA1(?)", $fs_fullpath];
			};
		}

		return parent::save($fields, $DATA);
	}

	// function cd($path){
	// 	$this->cwd = $path;
	// }

	function get_by_fullpath($path, $params = []){
		$params["fs_fullpath_hash"] = $this->path($path);

		return $this->get_all_single($params);
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
			];
			return $this->save($params);
		} else {
			$parts = explode("/", $this->path($path));
			$file_name = array_pop($parts);
			$dir = join("/", $parts);
			if($fs_fsid = $this->mkdir($dir)){
				$params = [
					'fs_fsid'=>$fs_fsid,
					'fs_uid'=>$this->uid,
					'fs_depth'=>count($parts),
					'fs_type'=>0,
					'fs_name'=>$file_name,
					'fs_fullpath'=>"$dir/$file_name",
					'fs_contents'=>$contents,
				];
				return $this->save($params);
			}
		}

		return false;
	}

	function is_dir($path){
		$params = ["fs_type"=>1];

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function file_exists($path){
		$params = ["fields"=>["fs_fsid"]];

		return $this->get_by_fullpath($path, $params) ? true : false;
	}

	function rmdir($path){
		$sql = "DELETE FROM $this->Table WHERE fs_fullpath_hash = SHA1(?)";

		return $this->get_trans()->PrepareAndExecute($sql, [$path]) ? true : false;
	}

	function dir_max($path){
		$params = ["get_dir_max"=>$this->path($path)];

		return $this->get_all($params);
	}

	function rmdir_tree($path){
		$ret = true;
		$max = $this->dir_max($path);

		foreach($max as $entry){
			if(!($ret = $this->rmdir($entry['fs_fullpath']))){
				return false;
			}
		}

		return $ret;
	}

	function mkdir($path){
		$fs_fsid = NULL;
		$fs_fullpath = '';
		// $parts = array_filter(explode("/", $this->path($path)), function($v){
		// 	return !empty(trim($v));
		// });
		$parts = explode("/", $this->path($path));
		foreach($parts as $i=>$p){
			$fs_fullpath .= "$p/";
			print "$fs_fullpath!\n";
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
					'fs_fullpath'=>$this->path($fs_fullpath),
				];
				if(!($fs_fsid = $this->save($params))){
					return false;
				}
			}
		}

		return $fs_fsid;
	}

	// private function full_path($path){
	// 	return "/".join("/", $this->path($path));
	// }

	private function path($path){
		// $parts = array_filter(explode("/", trim($path, "/")), function($v){
		// 	return !empty(trim($v));
		// });

		// return $parts;
		//return "/".join("/", $parts);
		return "/".trim($path, "/");
		//return "/".trim($this->cwd, "/").trim($path, "/");
	}
}