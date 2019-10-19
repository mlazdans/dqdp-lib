<?php

namespace dqdp;

use PHPMailer\PHPMailer;
use PHPMailer\Exception;

class QueueMailer extends PHPMailer
{
	private $q;
	private $current;

	function __construct($exceptions = null){
		parent::__construct($exceptions);
		$this->Mailer = 'queue';
	}

	function queueSend($MIMEHeader, $MIMEBody){
		$serialize = ['From','Sender','to','cc','bcc','all_recipients','SMTPKeepAlive'];

		$ObjData = (object)[];
		foreach($serialize as $k){
			$ObjData->{$k} = $this->{$k};
		}

		$DATA = [
			'BODY'=>$this->Body,
			'ALT_BODY'=>$this->AltBody,
			'MIME_HEADERS'=>$MIMEHeader,
			'MIME_BODY'=>$MIMEBody,
			'MAILER_OBJ'=>serialize($ObjData),
			'SENDER'=>$this->From,
			'RECIPIENT'=>serialize($this->all_recipients),
		];

		$fields = array_keys($DATA);

		list($fieldSQL, $valuesSQL, $values) = build_sql($fields, (object)$DATA);
		$sql = "INSERT INTO MAIL_QUEUE (ID,CREATE_TIME,TIME_TO_SEND,$fieldSQL) VALUES (NEXT VALUE FOR GEN_MAIL_QUEUE_ID,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,$valuesSQL) RETURNING ID";

		if($q = ibase_query_array($sql, $values)){
			$r = ibase_fetch($q);
			return $r->ID;
		}
		return false;
	}

	function getQueue(){
		if(!isset($this->q)){
			$sql = "SELECT * FROM MAIL_QUEUE WHERE SENT_TIME IS NULL ORDER BY CREATE_TIME DESC";
			$this->q = ibase_query($sql);
		}

		if($r = ibase_fetch($this->q)){
			$this->current = $r;
			if($ObjData = unserialize($r->MAILER_OBJ)){
				foreach($ObjData as $k=>$v){
					$this->{$k} = $v;
				}
			}
			return true;
		} else {
			$this->current = null;
			return false;
		}
	}

	function current(){
		return $this->current;
	}

	function sendCurrent(){
		if(!($r = $this->current())){
			return false;
		}

		try {
			if($this->smtpSend($r->MIME_HEADERS,$r->MIME_BODY)){
				$sql = "UPDATE MAIL_QUEUE SET SENT_TIME = CURRENT_TIMESTAMP, ERROR_MSG = NULL WHERE ID = ?";
				ibase_query($sql, $r->ID);
				return true;
			}
		} catch (Exception $e) {
			$this->ErrorInfo = $e->getMessage();
			$sql = "UPDATE MAIL_QUEUE SET TRY_SENT = TRY_SENT + 1, ERROR_MSG = ? WHERE ID = ?";
			ibase_query($sql, $e->getMessage(), $r->ID);
			return false;
		}
	}
}