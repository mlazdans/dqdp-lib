<?php

declare(strict_types = 1);

namespace dqdp\FS;

use dqdp\DBA\Table;

class FSTable extends Table {
	function getName(): string {
		return 'fs';
	}

	function getPK(){
		return 'fs_id';
	}

	function getGen(): ?string {
		return null;
		// return 'FS';
	}

	function getFields(): array {
		return [
			'fs_fsid', 'fs_uid', 'fs_depth', 'fs_type', 'fs_name', 'fs_ext', 'fs_fullname', 'fs_fullpath',
			'fs_contents', 'fs_size', 'fs_mime', 'fs_entered', 'fs_updated'
		];
	}
}
