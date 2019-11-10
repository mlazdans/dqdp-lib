<?php

namespace dqdp;

class Engine
{
	static public $START_TIME;
	static public $DB;
	static public $DB_PARAMS;
	static public $REQ;
	static public $GET;
	static public $POST;
	static public $IP;
	static public $DEV;
	static public $DOMAIN;
	static public $LOCALE;
	static public $DEBUG_MSG = [];
	static public $WARN_MSG = [];
	static public $ERR_MSG = [];
	static public $INFO_MSG = [];
	static public $SYS_ROOT;
	static public $TMP_ROOT;
	static public $PUBLIC_ROOT;
	static public $MODULES;
	static public $TEMPLATE_FILE;
	static public $TEMPLATE;

	# TODO: dažādi eventi: before init, after init, etc
	static function addEvent(){
	}

	static function init(){
		self::$START_TIME = microtime(true);
		set_error_handler('dqdp\Engine::error_handler');
		set_exception_handler('dqdp\Engine::exception_handler');
		register_shutdown_function('dqdp\Engine::shutdown');
		self::$REQ = eo();
		self::$GET = eo();
		self::$POST = eo();
		if(is_climode()){
			self::$IP = 'localhost';
			# Parse parameters passed as --param=value
			$argv = $GLOBALS['argv'] ?? [];
			if(count($argv) > 1){
				for($i = 1; $i<count($argv); $i++){
					if(strpos($argv[$i], '--') === 0){
						$parts = explode("=", $argv[$i]);
						$param = substr(array_shift($parts), 2); // remove "--"
						self::$REQ->{$param} = join("=", $parts); // restore 'value' in case value contains "="
					}
				}
			}
		} else {
			self::$IP = getenv('REMOTE_ADDR');
			self::$GET->merge(entdecode($_GET));
			self::$POST->merge(entdecode($_POST));
			self::$REQ->merge(entdecode($_GET));
			self::$REQ->merge(entdecode($_POST));
		}
	}

	static function db_connect($params){
		return self::$DB = ibase_connect_config($params);
	}

	static function get_module($MID){
		return self::$MODULES[$MID]??false;
	}

	static function module_filter_chars($MID){
		$module_chars = '/[^a-z_\/0-9]/';
		return preg_replace($module_chars, "", $MID);
	}

	static function module_path($MID){
		return realpath(self::$SYS_ROOT).DIRECTORY_SEPARATOR."./modules/$MID.php";
	}

	static function module_exists($MID){
		return isset(self::$MODULES[$MID]);
	}

	static function get_msgs(){
		return [
			'ERR'=>self::$ERR_MSG,
			'INFO'=>self::$INFO_MSG,
			'WARN'=>self::$WARN_MSG,
			'DEBUG'=>self::$DEBUG_MSG,
		];
	}

	static function __msg($key, $msg = null){
		if(is_climode() && $msg){
			fprintf(STDERR, "[%s] %s\n", $key, translit($msg));
		}
		if($msg === null){
			return self::${$key.'_MSG'};
		} else {
			return self::${$key.'_MSG'}[] = $msg;
		}
	}

	static function debug_msg($msg = null){
		return self::__msg("DEBUG", $msg);
	}

	static function warn_msg($msg = null){
		return self::__msg("WARN", $msg);
	}

	static function err_msg($msg = null){
		return self::__msg("ERR", $msg);
	}

	static function info_msg($msg = null){
		return self::__msg("INFO", $msg);
	}

	static function __error_handler($errno, $errtype, $errstr, $errfile, $errline, $trace = null){
		$outp[] = self::error_handler_msgformat($errtype, $errstr, $errfile, $errline);

		if($trace) {
			foreach($trace as $t){
				$outp[] = self::error_handler_traceformat($t);
			}
		}

		$msg = ini_get('error_prepend_string').join(ini_get('html_errors') ? "<br>" : "\n", $outp).ini_get('error_append_string');

		# !$errno - exception
		if(php_err_is_fatal($errno) || !$errno){
			self::err_msg($msg);
		} else {
			self::debug_msg($msg);
		}
	}

	static function error_handler(int $errno, string $errstr, string $errfile, int $errline){
		$errtype = $errno;
		if(php_err_is_fatal($errno)){
			$errtype = 'Fatal error';
		} elseif(in_array($errno, [E_WARNING, E_USER_WARNING])){
			$errtype = 'Warning';
		} elseif(in_array($errno, [E_NOTICE, E_USER_NOTICE])){
			$errtype = 'Notice';
		} elseif(in_array($errno, [E_DEPRECATED, E_USER_DEPRECATED])){
			$errtype = 'Deprecated';
		} elseif($errno == E_RECOVERABLE_ERROR){
			$errtype = 'Fatal recoverable error';
		}

		$trace = debug_backtrace();
		# Noņem handler f-iju no trace
		if(count($trace) < 2){
			$trace = false;
		} else {
			$trace = array_slice($trace, 1);
		}

		self::__error_handler($errno, $errtype, $errstr, $errfile, $errline, $trace);

		return false;
	}

	static function exception_handler($e) {
		self::__error_handler(0, sprintf("Uncaught Exception(%s)", get_class($e)), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
	}

	static function error_handler_msgformat($errtype, $errstr, $errfile, $errline){
		$msg = '%s: %s in %s on line %s';
		if(ini_get('html_errors')){
			$msg = '<b>%s:</b> %s in <b>%s</b> on line <b>%s</b>';
		}
		return sprintf($msg, $errtype, $errstr, trim_includes_path($errfile), $errline);
	}

	static function error_handler_traceformat($trace){
		if(empty($trace['file'])){
			$trace['file'] = __FILE__;
		}
		if(empty($trace['line'])){
			$trace['line'] = 'unknown';
		}
		if(empty($trace['class']) && !empty($trace['args']) && in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])){
			$args = trim_includes_path($trace['args'][0]);
		} else {
			$args = '...';
		}

		# TODO: formāts konfigurējams
		$msg = "\t%s(%s) in %s on line %s";
		if(ini_get('html_errors')){
			$msg = "\t%s(%s) in <b>%s</b> on line <b>%s</b>";
		}

		return sprintf($msg, $trace['function'], $args, trim_includes_path($trace['file']), $trace['line']);
	}

	static function shutdown(){
		if($err = error_get_last()){
			if(php_err_is_fatal($err['type'])){
				if(!is_climode())header503(null);
				self::error_handler($err['type'], $err['message'], $err['file'], $err['line']);
			}
		}

		if(ob_get_level()){
			$MODULE_DATA = ob_get_clean();
		}

		if(self::$TEMPLATE_FILE){
			self::$TEMPLATE->set('MODULE_DATA', $MODULE_DATA??'');
			self::$TEMPLATE->include(self::$TEMPLATE_FILE);
		} else {
			print $MODULE_DATA??'';
		}
	}
}
