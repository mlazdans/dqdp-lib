<?php declare(strict_types = 1);

namespace dqdp;

use dqdp\DBA\AbstractDBA;
use dqdp\DBA\TransactionInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class QueueMailer extends PHPMailer implements TransactionInterface
{
	var $TR;
	private $q;
	private $current;

	function __construct($exceptions = null){
		parent::__construct($exceptions);
		$this->Mailer = 'queue';
	}

	function set_trans(AbstractDBA $dba){
		$this->TR = $dba;

		return $this;
	}

	function get_trans(): AbstractDBA {
		return $this->TR;
	}

	function queueSend($MIMEHeader, $MIMEBody){
		$serialize = ['From','Sender','to','cc','bcc','all_recipients','SMTPKeepAlive','ContentType'];

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
			'CREATE_TIME'=>static function(){
				return 'CURRENT_TIMESTAMP';
			},
			'TIME_TO_SEND'=>static function(){
				return 'CURRENT_TIMESTAMP';
			}
		];

		$Ent = (new QueueMailer\Entity)->set_trans($this->get_trans());

		return $Ent->save($DATA);
	}

	function getQueue(){
		if(!isset($this->q)){
			$sql = "SELECT * FROM MAIL_QUEUE WHERE SENT_TIME IS NULL ORDER BY CREATE_TIME DESC";
			$this->q = $this->get_trans()->query($sql);
		}

		if($r = $this->get_trans()->fetch_object($this->q)){
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
				$this->get_trans()->query($sql, $r->ID);

				return true;
			}
		} catch (Exception $e) {
			$this->ErrorInfo = $e->getMessage();
			$sql = "UPDATE MAIL_QUEUE SET TRY_SENT = TRY_SENT + 1, ERROR_MSG = ? WHERE ID = ?";
			$this->get_trans()->query($sql, $e->getMessage(), $r->ID);

			return false;
		}
	}
}
