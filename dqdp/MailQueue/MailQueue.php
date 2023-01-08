<?php declare(strict_types = 1);

namespace dqdp\MailQueue;

use dqdp\DBA\interfaces\DBAInterface;
use dqdp\DBA\interfaces\TransactionInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailQueue extends PHPMailer implements TransactionInterface
{
	var $TR;
	private $q;
	private $current;

	function __construct($exceptions = null){
		parent::__construct($exceptions);
		$this->Mailer = 'queue';
	}

	function set_trans(DBAInterface $dba){
		$this->TR = $dba;

		return $this;
	}

	function get_trans(): DBAInterface {
		return $this->TR;
	}

	function queueSend($MIMEHeader, $MIMEBody){
		$serialize = ['From','Sender','to','cc','bcc','all_recipients','SMTPKeepAlive','ContentType'];

		$ObjData = (object)[];
		foreach($serialize as $k){
			$ObjData->{$k} = $this->{$k};
		}

		$dummy = new MailQueueDummy(
			Body: $this->Body,
			AltBody: $this->AltBody,
			MimeHeaders: $MIMEHeader,
			MimeBody: $MIMEBody,
			MailerObj: serialize($ObjData),
			Sender: $this->From,
			Recipient: serialize($this->all_recipients),
		);

		$Ent = (new Entity)->set_trans($this->get_trans());

		return $Ent->save(MailQueueType::initFrom($dummy));
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
				$sql = "UPDATE MAIL_QUEUE SET SENT_TIME = CURRENT_TIMESTAMP WHERE ID = ?";
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
