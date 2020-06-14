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

namespace dqdp\FS;

use dqdp\SQL\Select;

class Entity extends \dqdp\Entity {
	function __construct(){
		$this->Table = 'fs';
		$this->PK = 'fs_id';
	}

	function select(){
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
		$this->set_null_filters($sql, $DATA, $filters);

		if($DATA->exists('fs_fullpath_hash')){
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

		return parent::set_filters($sql, $DATA);
	}

	function fields(): array {
		return [
			'fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_ext', 'fs_fullname', 'fs_fullpath',
			'fs_fullpath_hash', 'fs_contents', 'fs_size', 'fs_mime', 'fs_entered', 'fs_updated'
		];
	}

	function save($DATA){
		$DATA = eo($DATA);

		if(!$DATA->exists('fs_fullpath_hash')){
			$fs_fullpath = $DATA->fs_fullpath;
			$DATA->fs_fullpath_hash = function() use ($fs_fullpath) {
				return ["SHA1(?)", $fs_fullpath];
			};
		}

		return parent::save($DATA);
	}
}
