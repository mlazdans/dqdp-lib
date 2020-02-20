<?php
/*
CREATE TABLE `fs` (
	`fs_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`fs_fsid` bigint(20) unsigned DEFAULT NULL,
	`fs_uid` int(10) unsigned DEFAULT '0',
	`fs_depth` int(10) unsigned NOT NULL,
	`fs_type` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`fs_name` varchar(32) NOT NULL,
	`fs_ext` varchar(32) DEFAULT NULL,
	`fs_fullname` varchar(64) NOT NULL,
	`fs_fullpath` varchar(2048) NOT NULL,
	`fs_fullpath_hash` varchar(40) NOT NULL,
	`fs_contents` longblob,
	`fs_size` bigint(20) unsigned DEFAULT NULL,
	`fs_mime` varchar(64) DEFAULT NULL,
	`fs_entered` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
	`fs_updated` timestamp(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
	PRIMARY KEY (`fs_id`),
	UNIQUE KEY `u_fs_fullpath_hash` (`fs_fullpath_hash`) USING BTREE,
	FOREIGN KEY (`fs_fsid`) REFERENCES `fs` (`fs_id`),
	FOREIGN KEY (`fs_uid`) REFERENCES `logins` (`l_id`) ON DELETE SET NULL ON UPDATE CASCADE
);
*/

// write, read to/from file descriptor
// mkdir, rmdir, creat, unlink, link, symlink
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

	function __construct($uid = NULL){
		$this->Table = 'fs';
		$this->PK = 'fs_id';
		$this->uid = $uid;
	}

	function sql_select(){
		return (
			new Select(
				"fs_id, fs_fsid, fs_uid, fs_depth, fs_type, fs_name, fs_ext, fs_fullname, fs_fullpath, fs_fullpath_hash, fs_size, fs_mime, fs_entered, fs_updated"
			))->From($this->Table);
	}

	function set_filters($sql, $DATA = null){
		$DATA = eoe($DATA);

		$filters = [
			'fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_ext', 'fs_fullname', 'fs_fullpath',
			'fs_contents', 'fs_mime', 'fs_entered', 'fs_updated'
		];
		$this->set_null_filters($sql, $DATA, $filters, "$this->Table.");

		# TODO: old pass get rid off
		if($DATA->isset('fs_fullpath_hash')){
			$sql->Where(["fs_fullpath_hash = SHA1(?)", $DATA->fs_fullpath_hash]);
		}

		if($DATA->get_dir_max){
			$sql
			->Where(["fs_fullpath LIKE ?", $DATA->get_dir_max."%"])
			->ResetOrderBy()->OrderBy("fs_depth DESC")
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

		$fields = [
			'fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_ext', 'fs_fullname', 'fs_fullpath', 'fs_fullpath_hash',
			'fs_contents', 'fs_size', 'fs_mime', 'fs_entered', 'fs_updated'
		];

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

		return $this->save($params);
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

		return $this->get_trans()->PrepareAndExecute($sql, [$path]) ? true : false;
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
				if(!($fs_fsid = $this->save($params))){
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
