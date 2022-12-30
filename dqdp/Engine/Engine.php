<?php declare(strict_types = 1);

namespace dqdp\Engine;

use ArgumentCountError;
use dqdp\InvalidTypeException;

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
	static public array $MSG;
	static public $SYS_ROOT;
	static public $TMP_ROOT;
	static public $PUBLIC_ROOT;
	static public $MODULES_ROOT;
	static public $MODULES;
	static public ?Template $TEMPLATE = null;

	static public bool $MOD_REWRITE_ENABLED = false;
	static string $REQUEST_METHOD;
	static string $MODULE;

	# TODO: rename after old $REQ remove
	static public Request  $R;

	static function get_config(string $k = null): mixed {
		return self::$CONFIG[$k]??null;
	}

	static function initMsgs(): void {
		self::$MSG = [
			'DEBUG'=>[],
			'WARN'=>[],
			'ERR'=>[],
			'INFO'=>[],
		];
	}

	static function consumeMsgs(): array {
		$m = self::$MSG;
		self::initMsgs();
		return $m;
	}

	static function add_config(...$args): void {
		if(count($args) == 1){
			if(is_array($args[0])){
				self::$CONFIG = array_merge(self::$CONFIG, $args[0]);
			} else {
				throw new InvalidTypeException($args[0]);
			}
		} elseif(count($args) == 2){
			self::$CONFIG[$args[0]] = $args[1];
		} else {
			throw new ArgumentCountError();
		}
	}

	static function init(){
		// ob_start();
		self::$START_TIME = microtime(true);
		self::initMsgs();
		ini_set('display_errors', '0'); // 1, ja iebūvētais
		set_error_handler([Engine::class, 'error_handler'], error_reporting());
		set_exception_handler([Engine::class, 'exception_handler']);
		register_shutdown_function([Engine::class, 'shutdown']);
		self::$REQ = eo();
		self::$GET = eo();
		self::$POST = eo();

		if(is_climode()){
			# TODO: add as command line argument
			self::$REQUEST_METHOD = "";
			self::$MOD_REWRITE_ENABLED = false;
			self::$R = new CliRequest;

			# Legacy
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
			self::$REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];
			self::$MOD_REWRITE_ENABLED = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());

			self::$R = new HttpRequest;
			self::$R->GET = Args::initFrom($_GET);
			self::$R->POST = Args::initFrom($_POST);
			self::$R->IP = getenv('REMOTE_ADDR');

			# Legacy
			self::$IP = getenv('REMOTE_ADDR');
			self::$GET->merge(entdecode($_GET));
			self::$POST->merge(entdecode($_POST));
			self::$REQ->merge(entdecode($_GET));
			self::$REQ->merge(entdecode($_POST));
		}

		# Module loader
		// spl_autoload_register(function ($class) {
		// 	if(strpos($class, "App\\modules\\") === 0){
		// 		$parts = array_slice(explode("\\", $class), 2);
		// 		$Class = $parts[0];
		// 		$module = strtolower($Class);

		// 		$path = join_paths([self::$MODULES_ROOT, $module, "$Class.php"]);
		// 		if(file_exists($path)){
		// 			require_once($path);
		// 		}
		// 	}
		// });
	}

	static function __url($params, $delim){
		if(self::get_config('use_mod_rewrite') && self::$MOD_REWRITE_ENABLED){
			$MID = $params['MID']??"/";
			unset($params['MID']);
			$Q = __query('', $params, $delim);
			return "/$MID".($Q ? "?$Q" : "");
		} else {
			return "/main.php?".__query('', $params, $delim);
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

	static function __msg(string $key, string|array $msg = null){
		if(is_climode() && $msg){
			// $io = in_array($key, ['ERR', 'DEBUG']) ? STDERR : STDOUT;
			// fprintf($io, "[%s] %s\n", $key, translit($msg));
			// printr("ddddd", $msg);
			# NOTE: fprintf() does some bufferings!
			printf("[%s] %s\n", $key, translit($msg));
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

	static function debug_msg(string|array $msg = null){
		return self::__msg("DEBUG", $msg);
	}

	static function warn_msg(string|array $msg = null){
		return self::__msg("WARN", $msg);
	}

	static function err_msg(string|array $msg = null){
		return self::__msg("ERR", $msg);
	}

	static function info_msg(string|array $msg = null){
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
			$args = trim_includes_path($trace['file']);
		} elseif(!empty($trace['args']) && in_array($trace['function'], ['include', 'require', 'include_once', 'require_once'])){
			$from = trim_includes_path($trace['file']);
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

	static function run(){
		$MID = $_GET["MID"]??$_POST["MID"]??"";
		$Prefix = match(self::$REQUEST_METHOD){
			"GET"=>"get",
			"POST"=>"post",
			default=>"",
		};

		$ROUTES = array_reverse(explode("/", $MID));

		$Module = mb_strtolower(array_pop($ROUTES));
		if(!($Module)){
			$Module = "main";
		}
		$Module[0] = name2prop($Module[0]);

		self::$MODULE = $Module;

		$ModuleClass = self::$MODULES_ROOT."\\$Module";

		$Method = "";
		while($ROUTES){
			$Method .= name2prop(array_pop($ROUTES));
		}

		if(!$Method){
			$Method = "index";
		}

		$PrefixMethod = "$Prefix$Method";

		$method_is_callable = function(string $className, string|int $k): bool {
		try {
				$method = (new ReflectionClass($className))->getMethod($k);
				return $method->isPublic() && !$method->isStatic();
			} catch(ReflectionException $e){
				return false;
			}
		};

		$templateTried = false;
		$MODULE_DATA = "";
		try {
			if($method_is_callable($ModuleClass, $PrefixMethod)){
				ob_start();
				(new ($ModuleClass))->$PrefixMethod();
				$MODULE_DATA = ob_get_clean();
			} elseif($method_is_callable($ModuleClass, $Method)){
				ob_start();
				(new ($ModuleClass))->$Method();
				$MODULE_DATA = ob_get_clean();
			} else {
				printr("$ModuleClass\\$Method not found");
				// header404("$ModuleClass->$Method not found");
				return;
			}

			if(self::$TEMPLATE){
				self::$TEMPLATE->out($MODULE_DATA);
			} else {
				print $MODULE_DATA;
			}
			self::dump_msg();
		} catch(\Error $ex){
			self::exception_handler($ex);
			self::dump_msg();
		}
	}

	private static function ob_get_clean_all(): string {
		$buf = '';
		while(ob_get_level()){
			$buf .= ob_get_clean();
		}

		return $buf;
	}

	static function dump_msg(): void {
		foreach(self::consumeMsgs() as $k=>$m){
			if(count($m)){
				foreach($m as $msg){
					println($msg);
				}
			}
		}
	}

	static function shutdown(){
		$MODULE_DATA = self::ob_get_clean_all();
		self::dump_msg();
		print $MODULE_DATA;
	}
}
