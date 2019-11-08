<?php

namespace dqdp;

class Engine
{
	static public $START_TIME;
	static public $DB;
	static public $REQ;
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

	static function init(){
		Engine::$START_TIME = microtime(true);
		set_error_handler('dqdp\Engine::error_handler');
		set_exception_handler('dqdp\Engine::exception_handler');
		register_shutdown_function('dqdp\Engine::shutdown');
		Engine::$REQ = eo();
		if(is_climode()){
			Engine::$IP = 'localhost';
			# Parse parameters passed as --param=value
			$argv = $GLOBALS['argv'] ?? [];
			if(count($argv) > 1){
				for($i = 1; $i<count($argv); $i++){
					if(strpos($argv[$i], '--') === 0){
						$parts = explode("=", $argv[$i]);
						$param = substr(array_shift($parts), 2); // remove "--"
						Engine::$REQ->{$param} = join("=", $parts); // restore 'value' in case value contains "="
					}
				}
			}
		} else {
			Engine::$IP = getenv('REMOTE_ADDR');
			Engine::$REQ->merge(entdecode($_GET));
			Engine::$REQ->merge(entdecode($_POST));
		}
	}

	static function db_connect($params){
		return Engine::$DB = ibase_connect_config($params);
	}

	static function get_module($MID){
		return Engine::$MODULES[$MID]??false;
	}

	static function module_filter_chars($MID){
		$module_chars = '/[^a-z_\/0-9]/';
		return preg_replace($module_chars, "", $MID);
	}

	static function module_path($MID){
		return realpath(Engine::$SYS_ROOT).DIRECTORY_SEPARATOR."./modules/$MID.php";
	}

	static function module_exists($MID){
		return isset(Engine::$MODULES[$MID]);
	}

	static function __msg($key, $msg = null){
		if($msg === null){
			return Engine::${$key.'_MSG'};
		} else {
			return Engine::${$key.'_MSG'}[] = $msg;
		}
	}

	static function debug_msg($msg = null){
		return Engine::__msg("DEBUG", $msg);
	}

	static function warn_msg($msg = null){
		return Engine::__msg("WARN", $msg);
	}

	static function err_msg($msg = null){
		return Engine::__msg("ERR", $msg);
	}

	static function info_msg($msg = null){
		return Engine::__msg("INFO", $msg);
	}

	static function __error_handler($errno, $errtype, $errstr, $errfile, $errline, $trace = null){
		$outp[] = Engine::error_handler_msgformat($errtype, $errstr, $errfile, $errline);

		if($trace) {
			foreach($trace as $t){
				$outp[] = Engine::error_handler_traceformat($t);
			}
		}

		$msg = ini_get('error_prepend_string').join(ini_get('html_errors') ? "<br>" : "\n", $outp).ini_get('error_append_string');

		if(php_err_is_fatal($errno)){
			Engine::$ERR_MSG[] = $msg;
		} else {
			Engine::$DEBUG_MSG[] = $msg;
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

		Engine::__error_handler($errno, $errtype, $errstr, $errfile, $errline, $trace);

		return false;
	}

	static function exception_handler($e) {
		Engine::__error_handler(0, sprintf("Uncaught Exception(%s)", get_class($e)), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
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
				Engine::error_handler($err['type'], $err['message'], $err['file'], $err['line']);
			}
		}
	}
}
