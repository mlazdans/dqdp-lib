<?php

declare(strict_types = 1);

namespace dqdp\QueueMailer;

use dqdp\DBA\Table;

class QueueMailerTable extends Table {
	function getName(): string {
		return 'MAIL_QUEUE';
	}

	function getPK(){
		return 'ID';
	}

	function getGen(): ?string {
		return 'GEN_MAIL_QUEUE_ID';
	}

	function getFields(): array {
		return [
			'CREATE_TIME', 'TIME_TO_SEND', 'SENT_TIME', 'ID_USER', 'IP', 'SENDER', 'RECIPIENT', 'BODY',
			'ALT_BODY', 'MIME_HEADERS', 'MIME_BODY', 'MAILER_OBJ', 'ERROR_MSG', 'TRY_SENT', 'DELETE_AFTER_SEND'
		];
	}
}
