<?php

// fs_fullpath_hash varchar(40) NOT NULL,

/*IBase
CREATE GENERATOR FS;
fs_updated ON UPDATE CURRENT_TIMESTAMP
CREATE TABLE fs (
	fs_id bigint NOT NULL,
	fs_fsid bigint DEFAULT NULL,
	fs_uid integer DEFAULT 0,
	fs_depth integer NOT NULL,
	fs_type SMALLINT DEFAULT 0 NOT NULL,
	fs_name varchar(32) NOT NULL,
	fs_ext varchar(32) DEFAULT NULL,
	fs_fullname varchar(64) NOT NULL,
	fs_fullpath varchar(2048) NOT NULL,
	fs_contents blob,
	fs_size bigint DEFAULT NULL,
	fs_mime varchar(64) DEFAULT NULL,
	fs_entered TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	fs_updated TIMESTAMP DEFAULT NULL,
	PRIMARY KEY (fs_id),
	UNIQUE (fs_depth, fs_name),
	FOREIGN KEY (fs_fsid) REFERENCES fs (fs_id)
);
*/

/* MySQL
CREATE TABLE fs (
	fs_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	fs_fsid bigint(20) unsigned DEFAULT NULL,
	fs_uid int(10) unsigned DEFAULT 0,
	fs_depth int(10) unsigned NOT NULL,
	fs_type tinyint(3) unsigned NOT NULL DEFAULT '0',
	fs_name varchar(32) NOT NULL,
	fs_ext varchar(32) DEFAULT NULL,
	fs_fullname varchar(64) NOT NULL,
	fs_fullpath varchar(2048) NOT NULL,
	fs_contents longblob,
	fs_size bigint(20) unsigned DEFAULT NULL,
	fs_mime varchar(64) DEFAULT NULL,
	fs_entered timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
	fs_updated timestamp(6) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(6),
	PRIMARY KEY (fs_id),
	UNIQUE (fs_depth, fs_name),
	FOREIGN KEY (fs_fsid) REFERENCES fs (fs_id)
);
*/

namespace dqdp\FS;

use dqdp\SQL\Select;

class Entity extends \dqdp\Entity {
	// protected $hash_f = 'SHA1';
	protected $Gen;

	function __construct(){
		$this->Table = 'fs';
		$this->PK = 'fs_id';
		$this->Gen = 'FS';
	}

	// function set_trans(DBA $dba){
	// 	parent::set_trans($dba);
	// 	if($this->lex == 'fbird'){
	// 		$this->set_hash_function("HASH");
	// 	}
	// 	return $this;
	// }

	// function set_hash_function($f){
	// 	$this->hash_f = $f;
	// }

	// function get_hash_function(){
	// 	return $this->hash_f;
	// }

	function select(){
		return (
			new Select(
				"fs_id, fs_fsid, fs_uid, fs_depth, fs_type, fs_name, fs_ext, fs_fullname, fs_fullpath,".
				"fs_size, fs_mime, fs_entered, fs_updated"
			))->From($this->Table);
	}

	function set_filters($sql, $DATA = null){
		$DATA = eoe($DATA);

		$filters = [
			'fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_ext', 'fs_fullname', 'fs_fullpath',
			'fs_contents', 'fs_mime', 'fs_entered', 'fs_updated'
		];
		$this->set_null_filters($sql, $DATA, $filters);

		// if($DATA->exists('fs_fullpath_hash')){
		// 	$sql->Where(["fs_fullpath_hash = $this->hash_f(?)", $DATA->fs_fullpath_hash]);
		// }

		//sqlr($sql);

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
			'fs_contents', 'fs_size', 'fs_mime', 'fs_entered', 'fs_updated'
		];
	}

	// function save($DATA){
	// 	$DATA = eo($DATA);

	// 	// if(!$DATA->exists('fs_fullpath_hash')){
	// 	// 	$fs_fullpath = $DATA->fs_fullpath;
	// 	// 	$DATA->fs_fullpath_hash = function() use ($fs_fullpath) {
	// 	// 		return ["$this->hash_f(?)", $fs_fullpath];
	// 	// 	};
	// 	// }

	// 	return parent::save($DATA);
	// }
}
