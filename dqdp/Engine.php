<?php declare(strict_types = 1);

namespace dqdp;

class Engine
{
	static public $CONFIG = [];
	static public $START_TIME;
	static public $REQ;
	static public $GET;
	static public $POST;
	static public $IP;
	static public $DEV;
	static public $DOMAIN;
	static public $LOCALE;
	static public $MSG = [
		'DEBUG'=>[],
		'WARN'=>[],
		'ERR'=>[],
		'INFO'=>[],
	];
	static public $SYS_ROOT;
	static public $TMP_ROOT;
	static public $PUBLIC_ROOT;
	static public $MODULES_ROOT;
	static public $MODULES;
	static public $ROUTES;
	static public ?EngineTemplate $TEMPLATE = null;
	static public $MOD_REWRITE;

	static function get_config($k = null){
		return self::$CONFIG[$k]??null;
	}

	static function add_config(){
		//func_get_arg();
		$args = func_get_args();
		if(count($args) == 1){
			if(is_array($args[0])){
				self::$CONFIG = array_merge(self::$CONFIG, $args[0]);
				return true;
			}
		} elseif(count($args) == 2){
			self::$CONFIG[$args[0]] = $args[1];
			return true;
		}
		return false;
		//self::$CONFIG = array_merge(self::$CONFIG, $nc);
	}

	static function init(){
		ob_start();
		if(empty(self::$SYS_ROOT) || !file_exists(self::$SYS_ROOT)){
			trigger_error('self::$SYS_ROOT not set', E_USER_ERROR);
		}
		self::$START_TIME = microtime(true);
		ini_set('display_errors', '0'); // 1, ja iebūvētais
		set_error_handler('dqdp\Engine::error_handler', error_reporting());
		set_exception_handler('dqdp\Engine::exception_handler');
		register_shutdown_function('dqdp\Engine::shutdown');
		self::$REQ = eo();
		self::$GET = eo();
		self::$POST = eo();

		self::$TMP_ROOT = self::$SYS_ROOT.DIRECTORY_SEPARATOR.'tmp';
		self::$MODULES_ROOT = self::$SYS_ROOT.DIRECTORY_SEPARATOR.'modules';
		self::$PUBLIC_ROOT = self::$SYS_ROOT.DIRECTORY_SEPARATOR."public";

		if(is_climode()){
			self::$MOD_REWRITE = false;
			self::$IP = 'localhost';
			# Parse parameters passed as --param=value
			$arg = $_SERVER['argv']??[];
			if(count($arg) > 1){
				for($i = 1; $i<count($arg); $i++){
					if(strpos($arg[$i], '--') === 0){
						$parts = explode("=", $arg[$i]);
						$param = substr(array_shift($parts), 2); // remove "--"
						self::$REQ->{$param} = join("=", $parts); // restore 'value' in case value contains "="
					}
				}
			}
		} else {
			self::$MOD_REWRITE = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
			self::$IP = getenv('REMOTE_ADDR');
			self::$GET->merge(entdecode($_GET));
			self::$POST->merge(entdecode($_POST));
			self::$REQ->merge(entdecode($_GET));
			self::$REQ->merge(entdecode($_POST));
		}

		# Module loader
		spl_autoload_register(function ($class) {
			if(strpos($class, "App\\modules\\") === 0){
				$parts = array_slice(explode("\\", $class), 2);
				$Class = $parts[0];
				$module = strtolower($Class);

				$path = join_paths([self::$MODULES_ROOT, $module, "$Class.php"]);
				if(file_exists($path)){
					require_once($path);
				}
			}
		});
	}

	static function __url($params, $delim){
		if(self::get_config('use_mod_rewrite') && self::$MOD_REWRITE){
			$MID = $params['MID']??"/";
			unset($params['MID']);
			$Q = __query('', $params, $delim);
			return "/$MID".($Q ? "?$Q" : "");
		} else {
			return "/index.php?".__query('', $params, $delim);
		}
	}

	static function url($params = []){
		return self::__url($params, '&amp;');
	}

	static function urll($params = []){
		return self::__url($params, '&');
	}

	static function a($name, Array $url, Array $url_params = []){
		if(empty($url_params['href'])){
			$url_params['href'] = self::url($url);
		}
		foreach($url_params as $k=>$v){
			$u_params[] = sprintf('%s="%s"', $k, $v);
		}

		return sprintf('<a %s>%s</a>', join(" ", $u_params??""), ent($name));
	}

	static function module_filter_chars($MID){
		$module_chars = '/[^a-z_\/0-9]/';
		return preg_replace($module_chars, "", $MID);
	}

	static function module_exists($ROUTES){
		if(is_scalar($ROUTES)){
			$ROUTES = explode("/", $ROUTES);
		}

		if(!is_array($ROUTES)){
			return false;
		}

		do {
			$path = self::$MODULES_ROOT.DIRECTORY_SEPARATOR.join_paths($ROUTES);
			$path1 = $path.DIRECTORY_SEPARATOR."index.php";
			if(file_exists($path1)){
				return $path1;
			}
			$path2 = $path.".php";
			if(file_exists($path2)){
				return $path2;
			}
			array_pop($ROUTES);
		} while($ROUTES);

		return false;
	}

	static function module_path($ROUTES, $max_d = INF){
		$path = [self::$MODULES_ROOT];

		if(is_scalar($ROUTES)){
			$ROUTES = explode("/", $ROUTES);
		}

		if($max_d === INF) {
			$ep = $ROUTES;
		} else {
			$ep = array_slice($ROUTES, 0, $max_d);
		}
		$path = array_merge($path, $ep);

		return join_paths($path).".php";
		// $path = self::$MODULE_PATH;

		// if(is_scalar($ROUTES)){
		// 	$ROUTES = explode("/", $ROUTES);
		// }

		// if($ep = array_slice($ROUTES, 0, $max_d)){
		// 	$path = array_merge($path, $ep);
		// }

		// return join('/', $path).".php";
	}

	/*
	static function module_path($ROUTES, $max_d){
		$path = self::$MODULE_PATH;

		if(is_scalar($ROUTES)){
			$ROUTES = explode("/", $ROUTES);
		}

		if($ep = array_slice($ROUTES, 0, $max_d)){
			$path = array_merge($path, $ep);
		}

		return join('/', $path).".php";
	}

	static function module_exists($ROUTES){
		if(is_scalar($ROUTES)){
			$ROUTES = explode("/", $ROUTES);
		} if(!is_array($ROUTES)){
			return false;
		}

		$ROUTES = array_reverse($ROUTES);

		$ret = false;
		$MODULES = self::$MODULES;
		do {
			$r = array_pop($ROUTES);
			if(isset($MODULES[$r])){
				$ret = true;
			} else {
				$ret = false;
				break;
			}
			$MODULES = $MODULES[$r]['sub_modules']??[];
		} while($ROUTES);

		return $ret;
	}
	*/

	static function __msg(string $key, $msg = null){
		if(is_climode() && $msg){
			$io = in_array($key, ['ERR', 'DEBUG']) ? STDERR : STDOUT;
			fprintf($io, "[%s] %s\n", $key, translit($msg));
		}

		if($msg === null){
			return self::$MSG[$key];
		} else {
			if(is_array($msg)){
				self::$MSG[$key] = array_merge(self::$MSG[$key], $msg);
				return $msg;
			} else {
				return self::$MSG[$key][] = $msg;
			}
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
				if($line = self::error_handler_traceformat($t)){
					$outp[] = $line;
				}
			}
		}

		$msg = ini_get('error_prepend_string').join(ini_get('html_errors') ? "<br>" : "\n", $outp).ini_get('error_append_string');

		# !$errno - exception
		if(is_fatal_error($errno) || !$errno){
			self::err_msg($msg);
		} else {
			self::debug_msg($msg);
		}
	}

	static function error_handler(int $errno, string $errstr, string $errfile, int $errline){
		$errtype = $errno;
		if(is_fatal_error($errno)){
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
		error_log("Uncaught Exception: ".$e);
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
		if(!empty($trace['class'])){
			if($trace['class'] == 'dqdp\PHPTemplate'){
				//return false;
			}
			$args = trim_includes_path($trace['file']);
		} elseif(!empty($trace['args']) && in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])){
			$from = trim_includes_path($trace['file']);
			if($from == 'dqdp\PHPTemplate.php'){
				return false;
			}
			$args = trim_includes_path($trace['args'][0]);
		} else {
			//printr($trace);
			// $args = trim_includes_path($trace['file']);
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
			if(is_php_fatal_error($err['type'])){
				if(!is_climode())header503(null);
				self::error_handler($err['type'], $err['message'], $err['file'], $err['line']);
			}
		}

		$MODULE_DATA = '';
		while(ob_get_level()){
			$MODULE_DATA .= ob_get_clean();
		}

		try {
			if(self::$TEMPLATE){
				self::$TEMPLATE->out($MODULE_DATA);
			} else {
				print $MODULE_DATA;
			}
		} catch(\Error $ex){
			self::exception_handler($ex);
			println("Fatal error:");
			foreach(self::$MSG as $k=>$m){
				println("$k:");
				foreach($m as $msg){
					println($msg);
				}
			}
			print $MODULE_DATA;
		}
	}
}
