<?php declare(strict_types = 1);

# TODO: check argument types for __object_map() functions

use dqdp\InvalidTypeException;
use dqdp\LV;
use dqdp\QueueMailer;
use dqdp\StdObject;
use PHPMailer\PHPMailer\PHPMailer;

require_once("qblib.php");
require_once("objflib.php");

final class dqdp {
	static public $DATE_FORMAT = 'd.m.Y';
	static public $TIME_FORMAT = 'H:i:s';
	static public $LOCALE_INFO = null;
}

function netmasks(){
	return [
		"0.0.0.0", "128.0.0.0", "192.0.0.0", "224.0.0.0", "240.0.0.0", "248.0.0.0", "252.0.0.0",
		"254.0.0.0", "255.0.0.0", "255.128.0.0", "255.192.0.0", "255.224.0.0", "255.240.0.0", "255.248.0.0", "255.252.0.0",
		"255.254.0.0", "255.255.0.0", "255.255.128.0", "255.255.192.0", "255.255.224.0", "255.255.240.0", "255.255.248.0",
		"255.255.252.0", "255.255.254.0", "255.255.255.0", "255.255.255.128", "255.255.255.192", "255.255.255.224",
		"255.255.255.240", "255.255.255.248", "255.255.255.252", "255.255.255.254", "255.255.255.255"
	];
};

function menesi(){
	return [
		'01'=>'Janvāris',
		'02'=>'Februāris',
		'03'=>'Marts',
		'04'=>'Aprīlis',
		'05'=>'Maijs',
		'06'=>'Jūnijs',
		'07'=>'Jūlijs',
		'08'=>'Augusts',
		'09'=>'Septembris',
		'10'=>'Oktobris',
		'11'=>'Novembris',
		'12'=>'Decembris',
	];
}

function country_find_iso($country){
	$country = mb_strtoupper($country);
	foreach(countries() as $k=>$v){
		if(mb_strtoupper($v) == $country){
			return $k;
		}
	}

	return false;
}

function country_codes_eu($date = null){
	if($date){
		$date = strtotime($date);
	} else {
		$date = time();
	}

	$codes = [
		'AT','BE','BG','CY','CZ','DK','EE','FI','FR','DE','GR','HU','HR','IE',
		'IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'
	];

	if($date < strtotime('1.1.2020')){
		$codes[] = 'GB';
	}

	return $codes;
}

function country_codes_eu_sql($date = null){
	return "'".join("','", country_codes_eu($date))."'";
}

function countries(){
	return [
		"AF" => "Afghanistan",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"BQ" => "British Antarctic Territory",
		"IO" => "British Indian Ocean Territory",
		"VG" => "British Virgin Islands",
		"BN" => "Brunei",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"CT" => "Canton and Enderbury Islands",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos [Keeling] Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo - Brazzaville",
		"CD" => "Congo - Kinshasa",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"CI" => "Côte d’Ivoire",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"NQ" => "Dronning Maud Land",
		"DD" => "East Germany",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"FQ" => "French Southern and Antarctic Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-Bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and McDonald Islands",
		"HN" => "Honduras",
		"HK" => "Hong Kong SAR China",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JT" => "Johnston Island",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Laos",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macau SAR China",
		"MK" => "Macedonia",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"FX" => "Metropolitan France",
		"MX" => "Mexico",
		"FM" => "Micronesia",
		"MI" => "Midway Islands",
		"MD" => "Moldova",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar [Burma]",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"AN" => "Netherlands Antilles",
		"NT" => "Neutral Zone",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"KP" => "North Korea",
		"VD" => "North Vietnam",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PC" => "Pacific Islands Trust Territory",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PS" => "Palestinian Territories",
		"PA" => "Panama",
		"PZ" => "Panama Canal Zone",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"YD" => "People's Democratic Republic of Yemen",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn Islands",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RO" => "Romania",
		"RU" => "Russia",
		"RW" => "Rwanda",
		"RE" => "Réunion",
		"BL" => "Saint Barthélemy",
		"SH" => "Saint Helena",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"MF" => "Saint Martin",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and the Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"CS" => "Serbia and Montenegro",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia and the South Sandwich Islands",
		"KR" => "South Korea",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syria",
		"ST" => "São Tomé and Príncipe",
		"TW" => "Taiwan",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania",
		"TH" => "Thailand",
		"TL" => "Timor-Leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UM" => "U.S. Minor Outlying Islands",
		"PU" => "U.S. Miscellaneous Pacific Islands",
		"VI" => "U.S. Virgin Islands",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"SU" => "Union of Soviet Socialist Republics",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"US" => "United States",
		"ZZ" => "Unknown or Invalid Region",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VA" => "Vatican City",
		"VE" => "Venezuela",
		"VN" => "Vietnam",
		"WK" => "Wake Island",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe",
		"AX" => "Åland Islands",
	];
}

function req(string $key, $default = ''){
	return $_REQUEST[$key]??$default;
}

function get($key, $default = ''){
	return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function post($key, $default = ''){
	return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function postget($key, $default = ''){
	return isset($_POST[$key]) ? $_POST[$key] : get($key, $default);
}

function getpost($key, $default = ''){
	return isset($_GET[$key]) ? $_GET[$key] : post($key, $default);
}

function sess($key, $default = ''){
	return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function cookie($key, $default = ''){
	return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
}

function server($key, $default = ''){
	return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
}

function env($key, $default = ''){
	return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

function upload($key, $default = ''){
	return isset($_FILES[$key]) ? $_FILES[$key] : $default;
}

function redirect(string $url = null): void {
	header("Location: ".($url??php_self()));
}

function redirectp($url): void {
	header("Location: $url", true, 301);
}

function redirect_not_found(string $url = '/', string $msg = ''): void {
	header404($msg);
	redirect($url);
}

function redirect_referer(string $default = "/"): void {
	if(empty($_SERVER['HTTP_REFERER'])){
		redirect($default);
	} else {
		redirect($_SERVER["HTTP_REFERER"]);
	}
}


function floatpoint(mixed $val): float {
	$val = preg_replace('/[^0-9,\.\-]/', '', (string)$val);
	return (float)str_replace(',', '.', (string)$val);
}

function to_float(mixed $data): mixed {
	return __object_map($data, function(mixed $item): float {
		return floatpoint($item);
	});
}

function to_int(mixed $data): mixed {
	return __object_map($data, function(mixed $item): int {
		return (int)$item;
	});
}

function money_conv(mixed $data): float {
	return floatpoint($data);
}

function money_round(mixed $data): string {
	return number_format(money_conv($data), 2, '.', '');
}

function to_money(mixed $data): mixed {
	return __object_map($data, function(mixed $item): float {
		return money_conv($item);
	});
}

function to_range($val, $range, $default = ''){
	$range_a = preg_split('//', $range);
	if(!$val || !in_array($val, $range_a)){
		$val = $default;
	}

	return $val;
}

function upload_save($id, $save_path){
	$f = upload($id);
	return $f && $f['tmp_name'] && move_uploaded_file($f['tmp_name'], $save_path);
}

function upload_errormsg($id){
	$f = upload($id);
	if(empty($f['error'])){
		return "";
	}

	switch($f['error']){
		case UPLOAD_ERR_INI_SIZE:
			$errormsg = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$errormsg = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			break;
		case UPLOAD_ERR_PARTIAL:
			$errormsg = 'The uploaded file was only partially uploaded.';
			break;
		case UPLOAD_ERR_NO_FILE:
			$errormsg = 'No file was uploaded.';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$errormsg = 'Missing a temporary folder.';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$errormsg = 'Failed to write file to disk.';
			break;
		default:
			$errormsg = 'Unknow error.';
			break;
	}

	return $errormsg;
}

function numpad($data, int $size = 2): string {
	return str_pad($data, $size, "0", STR_PAD_LEFT);
}

function md5uniqid(): string {
	return md5(uniqid((string)rand(), true));
}

function browse_tree($path, callable $function){
	foreach(scandir($path) as $entry){
		($r = $function($path, $entry, $function)) && $ret[$entry] = $r;
	}
	return $ret??[];
}

function browse_flat($path, callable $function){
	$ret = [];
	foreach(scandir($path) as $entry){
		($r = $function($path, $entry, $function)) && $ret = array_merge($ret, is_array($r) ? $r : [$r]);
	}
	return $ret??[];
}

function compacto($data): mixed {
	return __object_filter($data, function($item){
		return (bool)$item;
	});
}

function utf2win(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return mb_convert_encoding((string)$item, 'ISO-8859-13', 'UTF-8');
	});
}

function win2utf(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return mb_convert_encoding($item, 'UTF-8', 'ISO-8859-13');
	});
}

function translit(mixed $data): mixed {
	return __object_map($data, function($item): string {
		return iconv("utf-8","ascii//TRANSLIT", $item);
	});
}

function is_empty(mixed $data): bool {
	return __object_reduce($data, function(bool $carry, mixed $item): bool {
		return $carry && empty($item);
	}, true);
}

function non_empty(mixed $data): bool {
	return !is_empty($data);
}

function ent(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return htmlentities($item);
	});
}

function entdecode(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return html_entity_decode($item);
	});
}

# https://www.gyrocode.com/articles/php-urlencode-vs-rawurlencode/
# scheme:[//[user[:password]@]host[:port]][/path][?query][#fragment]
# If you are encoding *path* segment, use rawurlencode().
# If you are encoding *query* component, use urlencode().
function urlenc(mixed $data): mixed {
	return __object_map($data, function($item): string {
		return urlencode($item);
	});
}
function urldec(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return urldecode($item);
	});
}
function rawurlenc(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return rawurlencode($item);
	});
}
function rawurldec(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return rawurldecode($item);
	});
}
##

function specialchars(mixed $data){
	return __object_map($data, function(mixed $item): string {
		return htmlspecialchars(string: (string)$item, double_encode: false);
	});
}

function date_in_periods($date, array $periods): bool {
	if(empty($periods)){
		return false;
	}

	$ts = empty($date) ? time() : strtotime($date);

	if(($ts === false) || ($ts > time())){
		return null;
	}

	foreach($periods as list($s, $e)){
		$e = is_null($e) ? time() : strtotime($e);
		$s = strtotime($s);

		if(($ts >= $s) && ($ts <= $e)){
			return true;
		}
	}

	return false;
}

function date_monthstamp(){
	return date('Ym');
}

function date_datestamp(){
	return date('Ymd');
}

function date_timestamp(){
	return date('YmdHi');
}

function get_date_format(){
	return dqdp::$DATE_FORMAT;
}

function set_date_format($f){
	return dqdp::$DATE_FORMAT = $f;
}

function timef($ts = null){
	return date(get_time_format(), $ts ?? time());
}

function get_time_format(){
	return dqdp::$TIME_FORMAT;
}

function set_time_format($f){
	return dqdp::$TIME_FORMAT = $f;
}

function datef(int $ts = null): string {
	return date(get_date_format(), $ts ?? time());
}

function date_today(): string {
	return datef(time());
}

function date_yesterday(): string {
	return datef(strtotime('yesterday'));
}

function date_daycount(int $m = null, int $y = null): int {
	return (int)($m ? (date('t', mktime(0,0,0, $m, 1, ($y ? $y : (int)date('Y'))))) : date('t'));
}

function date_month_start(): string {
	return datef(strtotime("first day of this month"));
}

function date_month_end(): string {
	return datef(strtotime("last day of this month"));
}

function date_lastmonth_start(): string {
	return datef(strtotime("first day of previous month"));
}

function date_lastmonth_end(): string {
	return datef(strtotime("last day of previous month"));
}

function is_valid_date($date): bool {
	return strtotime($date) !== false;
}

function ustrftime(string $format, int $timestamp = 0): string {
	return win2utf(strftime($format, $timestamp));
}

# quarter month
function date_qt_month(int $C, int $m = 1): int {
	return ($C - 1) * 3 + $m;
}

// function date_startend($D): array {
// 	$DATE = eoe($D);
// 	$format = get_date_format();
// 	$start_date = $end_date = false;

// 	$ceturksnis = false;
// 	for($i = 1; $i < 5; $i++){
// 		if($DATE->{"C$i"}){
// 			$ceturksnis = $i;
// 		}
// 	}

// 	if($ceturksnis){
// 		// $start_date = mktime(0,0,0, ($ceturksnis - 1) * 3 + 1, 1, date('Y'));
// 		// $days_in_end_month = date_daycount(($ceturksnis - 1) * 3 + 3);
// 		// $end_date = mktime(0,0,0, ($ceturksnis - 1) * 3 + 3, $days_in_end_month, date('Y'));
// 		$start_date = mktime(0,0,0, date_qt_month($ceturksnis, 1), 1, (int)date('Y'));
// 		$days_in_end_month = date_daycount(date_qt_month($ceturksnis, 3));
// 		$end_date = mktime(0,0,0, date_qt_month($ceturksnis, 3), $days_in_end_month, (int)date('Y'));
// 	} elseif($DATE->PREV_YEAR) {
// 		$start_date = strtotime('first day of January last year');
// 		$end_date = strtotime('last day of December last year');
// 	} elseif($DATE->THIS_YEAR){
// 		$start_date = strtotime('first day of January');
// 		$end_date = time();
// 	} elseif($DATE->TODAY) {
// 		$start_date = $end_date = strtotime('today');
// 	} elseif($DATE->YESTERDAY) {
// 		$start_date = $end_date = strtotime('yesterday');
// 	} elseif($DATE->THIS_WEEK) {
// 		$start_date = strtotime("last Monday");
// 		$end_date = time();
// 	} elseif($DATE->THIS_MONTH) {
// 		$start_date = strtotime("first day of");
// 		$end_date = time();
// 	} elseif($DATE->PREV_MONTH) {
// 		$start_date = strtotime("first day of previous month");
// 		$end_date = strtotime("last day of previous month");
// 	} elseif($DATE->PREV_30DAYS){
// 		$start_date = strtotime("-30 days");
// 		$end_date = time();
// 	} elseif($DATE->MONTH){
// 		if(empty($DATE->YEAR))$DATE->YEAR = date('Y');
// 		$dc = date_daycount((int)$DATE->MONTH, (int)$DATE->YEAR);
// 		$start_date = strtotime("$DATE->YEAR-$DATE->MONTH-01");
// 		$end_date = strtotime("$DATE->YEAR-$DATE->MONTH-$dc");
// 	} elseif($DATE->YEAR){
// 		$start_date = strtotime("first day of January $DATE->YEAR");
// 		$end_date = strtotime("last day of December $DATE->YEAR");
// 	} else {
// 		if($DATE->START)$start_date = strtotime($DATE->START);
// 		if($DATE->END)$end_date = strtotime($DATE->END);
// 	}

// 	if($start_date)$start_date = date($format, $start_date);
// 	if($end_date)$end_date = date($format, $end_date);

// 	return [$start_date, $end_date];
// }

function php_self(){
	return $_SERVER['REQUEST_URI'] ?? '';
}

function queryl($format = '', $allowed = []){
	return __query($_SERVER['QUERY_STRING'] ?? '', $format, '&', $allowed);
}

function query($format = '', $allowed = []){
	return __query($_SERVER['QUERY_STRING'] ?? '', $format, '&amp;', $allowed);
}

function __query($query_string = '', $format = '', $delim = '&amp;', $allowed = []){
	parse_str($query_string, $QS);
	if(is_array($format)){
		$FORMAT = $format;
	} else {
		parse_str($format, $FORMAT);
	}

	foreach($allowed as $k=>$v){
		unset($QS[$k]);
	}

	foreach($FORMAT as $k=>$v){
		if($k[0] == '-'){
			$k2 = substr($k, 1);
			if(!$v || $v == $QS[$k2]){
				unset($QS[$k2]);
			}
		} else {
			$QS[$k] = $v;
		}
	}

	// $ret = [];
	// foreach($QS as $k=>$v){
	// 	$ret[] = "$k=$v";
	// }
	// $q1 = join($delim, $ret);
	$q2 = http_build_query($QS, "", $delim);
	// print "\n$q1\n$q2\n";
	// die;

	return $q2;
}

function format_debug(mixed $v): mixed {
	return __object_map($v, function($item) {
		if((is_string($item) || $item instanceof Stringable) && mb_detect_encoding($item)){
			return mb_substr($item, 0, 1024).(mb_strlen($item) > 1024 ? '...' : '');
		} elseif(is_scalar($item)){
			return $item;
		} elseif(is_null($item)) {
			return "NULL";
		} elseif(is_resource($item)) {
			return "$item";
		} elseif(is_array($item)) {
			return "[ARRAY]";
		} elseif(is_object($item) && method_exists($item , '__toString')) {
			return "$item";
		} else {
			return "[BLOB]";
		}
	});
}

# NOTE: dep on https://highlightjs.org/
function sqlr(){
	if(!is_climode())print "<pre><code class=\"sql\">";

	print ($t = debug_backtrace()) ? __back_trace_fmt($t[0])."\n\n" : '';
	__output_wrapper(function($v){
		if($v instanceof dqdp\SQL\Statement){
			print (string)$v;
			if(method_exists($v, 'getVars')){
				print ("\n\n--[Bind vars]\n");
				if($vars = $v->getVars()){
					foreach($vars as $k=>$var){
						printf("--[%s] = %s\n", $k, format_debug($var));
					}
				} else {
					print "-- none --";
				}
				// printf("\n--Finished in: %.3f sec", $v->end_ts - $v->start_ts);
			}
		} else {
			print_r(format_debug($v));
		}
	}, ...func_get_args());

	print is_climode() ? "\n-------------------------------------------------\n" : "</code></pre>";

	print "\n";
}

function dumpr(){
	__pre_wrapper('var_dump', ...func_get_args());
}

function printr(){
	__pre_wrapper('print_r', ...func_get_args());
}

function __pre_wrapper(callable $func, ...$args){
	if(!is_climode())print '<pre style="background: lightgrey; color: black">';

	print ($t = debug_backtrace()) ? __back_trace_fmt($t[1])."\n------------------------------------------------------------------------------\n" : '';
	__output_wrapper($func, ...$args);

	print is_climode() ? "\n------------------------------------------------------------------------------\n" : "</pre>";
	print "\n";
}

function __output_wrapper(callable $func, ...$args){
	$c = count($args);
	if(is_climode()){
		for($i = 0; $i < $c; $i++){
			if($i > 0)print "\n";
			$func($args[$i]);
		}
	} else {
		for($i = 0; $i < $c; $i++){
			if($i > 0)print "\n";
			ob_start();
			$func($args[$i]);
			print htmlspecialchars(string: ob_get_clean(), double_encode: false);
		}
	}
}

function __back_trace_fmt($t){
	return sprintf("called '%s' in '%s' on line %d", $t['function'], $t['file'], $t['line']);
}

function printrr(){
	ob_start();
	call_user_func_array('printr', func_get_args());
	return ob_get_clean();
}

function dumprr(){
	ob_start();
	call_user_func_array('dumpr', func_get_args());
	return ob_get_clean();
}

function br2nl($text){
	return preg_replace('/<br\\\\s*?\\/??>/i', "\\n", $text);
}

function js_bool($js_bool){
	if($js_bool === 'true')
		$ret = true;
	elseif($js_bool === 'on')
		$ret = true;
	else
		$ret = false;

	return $ret;
}

function mb_streqi($s1, $s2){
	return mb_strtoupper($s1) === mb_strtoupper($s2);
}

function net2cidr($mask){
	return array_search($mask, netmasks());
}

function cidr2net($bits){
	return netmasks()[$bits] ?? false;
}

function net2long($ip){
	return ip2long($ip);
}

function long2net($net){
	return long2ip($net);
}

# $ip      = 192.168.1.2
# $network = 192.168.1.0/24
function ipInNet($pIp, $pNetwork){
	list($net, $cidr) = explode('/', $pNetwork);

	$ipLong = net2long($pIp);
	$netLong = net2long($net);
	$mask = net2long(cidr2net($cidr));

	return ($ipLong & $mask) == ($netLong & $mask);
}

function array_sort_len($a){
	array_multisort(array_map('strlen', $a), SORT_NUMERIC, SORT_DESC, $a);
	return $a;
}

# TODO: SORT_DESC, SORT_NUMERIC parametros
function array_sort_byk($a, $k){
	$ka = array_map(function($i) use ($k){
		return $i[$k];
	}, $a);
	array_multisort($ka, SORT_DESC, SORT_NUMERIC, $a);
	return $a;
}

function array_insert_after(array $a, $v1, $v2){
	if(($pos = array_search($v1, $a)) === false){
		return $a;
	}
	return array_merge(
		array_slice($a, 0, $pos + 1),
		[$v2],
		array_slice($a, $pos + 1));
}
function array_insert_afterr(array &$a, $v1, $v2){
	$a = array_insert_after($a, $v1, $v2);
}

function array_delete(array $a, $v1){
	if(($pos = array_search($v1, $a)) !== false){
		unset($a[$pos]);
	}
	return $a;
}
function array_deleter(array &$a, $v1){
	$a = array_delete($a, $v1);
}


function locale_get_info($refresh = false){
	if($refresh || is_null(dqdp::$LOCALE_INFO)){
		dqdp::$LOCALE_INFO = localeconv();
	}

	return dqdp::$LOCALE_INFO;
}

function locale_number_format($t, $places = 2){
	$LOCALE_INFO = locale_get_info();
	$point = $LOCALE_INFO['decimal_point']??'.';
	$sep = $LOCALE_INFO['thousands_sep']??'';

	return number_format($t, $places, $point, $sep);
}

function locale_money_format($t, $places = 2){
	$LOCALE_INFO = locale_get_info();
	$point = $LOCALE_INFO['mon_decimal_point']??'.';
	$sep = $LOCALE_INFO['mon_thousands_sep']??'';

	return number_format($t, $places, $point, $sep);
}

function strip_path(mixed $data): mixed {
	return __object_map($data, function(mixed $item): string {
		return preg_replace('/[\/\.\\\]/', '', $item);
	});
}

function urlize($name){
	$name = preg_replace("/[%]/", " ", $name);
	$name = html_entity_decode($name, ENT_QUOTES);
	$name = mb_strtolower($name);
	$name = strip_tags($name);
	$name = preg_replace("/[`\:\/\?\#\[\]\@\"'\(\)\.,&;\+=\\\]/", " ", $name);
	$name = trim($name);
	$name = preg_replace("/\s+/", "-", $name);
	$name = preg_replace("/-+/", "-", $name);

	return $name;
}

function url_pattern() {
	$url_patt = $path_patt = '';
	return "/(http(s?):\/\/|ftp:\/\/|mailto:|callto:)([^\/\s\t\n\r\!\'\<>\(\)]".$url_patt."*)([^\s\t\n\r\'\<>]".$path_patt."*)/is";
}

function get_inner_html($node){
	$innerHTML= '';
	$children = $node->childNodes;
	foreach ($children as $child) {
		$innerHTML .= $child->ownerDocument->saveXML( $child );
	}

	return $innerHTML;
}

function ip_rev($ip){
	return implode('.', array_reverse(explode('.', $ip)));
}

function ip_blacklisted($ip){
	$dnsbl = ['bl.blocklist.de', 'xbl.spamhaus.org', 'cbl.abuseat.org', 'all.s5h.net'];

	$iprev = ip_rev($ip);
	foreach($dnsbl as $bl) {
		# return code 1 - not found; 0 - listed
		# $c = "host -W 1 $iprev.$bl";

		$c = "dig +short $iprev.$bl";
		if(exec($c, $o, $rv)){
			return true;
		}
	}

	return false;
}

# TODO: savest kārtībā params
# TODO: iespēja izmantot multiple providers
function emailex($params){
	$to                = $params->to;
	$from              = $params->from;
	$subj              = $params->subject;
	$msg               = $params->message;
	$msgTxt            = $params->messageTxt??'';
	$attachments       = (isset($params->attachments) ? $params->attachments : array());
	$MAIL_PARAMS       = (isset($params->MAIL_PARAMS) ? $params->MAIL_PARAMS : false);
	$use_queue         = (isset($params->use_queue) ? $params->use_queue : false);
	$delete_after_send = (isset($params->delete_after_send) ? $params->delete_after_send : true);
	$id_user           = (isset($params->id_user) ? $params->id_user : null);

	if($use_queue){
		$mail = new QueueMailer(true);
		$mail->set_trans($params->TR);
	} else {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
	}

	if(isset($params->headers) && is_array($params->headers)){
		foreach($params->headers as $k=>$v){
			$mail->addCustomHeader($k, $v);
		}
	}

	if(!empty($params->Sender))$mail->Sender = $params->Sender;

	$mail->Encoding = PHPMailer::ENCODING_QUOTED_PRINTABLE;
	$mail->CharSet = 'utf-8';
	$mail->XMailer = " ";
	//$mail->SMTPDebug = 2;
	//$mail->isSMTP();
	$mail->Host = $MAIL_PARAMS['host'];
	$mail->Port = $MAIL_PARAMS['port'];
	if(!empty($MAIL_PARAMS['auth'])){
		$mail->Username = $MAIL_PARAMS['username'];
		$mail->Password = $MAIL_PARAMS['password'];
		$mail->SMTPAuth = $MAIL_PARAMS['auth'];
	} else {
		$mail->SMTPAuth = false;
	}
	$mail->SMTPSecure = 'tls';

	if(isset($params->smtpdebug)){
		$mail->SMTPDebug = $params->smtpdebug;
	}

	$mail->setFrom($from);
	$mail->addAddress($to);
	$mail->isHTML($params->isHTML??false);
	$mail->Subject = $subj;

	if(empty($params->isHTML)){
		$mail->Body = $msg;
	} else {
		$mail->msgHTML($msg, $params->basedir??'');
		if($msgTxt){
			$mail->AltBody = $msgTxt;
		}
	}

	foreach($attachments as $item){
		if($item->isfile){
			$mail->addAttachment($item->data, $item->name);
		} else {
			$mail->addStringAttachment($item->data, $item->name);
		}
	}

	return $mail->send();
}

function csv_get_header(string $file, string $delim = ';'): ?array {
	if(($f = fopen($file, "r")) === false){
		return false;
	}

	$ret = null;
	if(($line = fgetcsv($f, 2000, $delim)) !== false){
		$ret = $line;
	}
	fclose($f);

	return $ret;
}

function csv_col_count($file, $delim = ';'): ?int {
	if($line = csv_get_header($file, $delim)){
		return count($line);
	} else {
		return null;
	}
	// if(($f = fopen($file, "r")) === false){
	// 	return false;
	// }

	// $col_count = 0;
	// if(($line = fgetcsv($f, 2000, $delim)) !== false){
	// 	$col_count = count($line);
	// }
	// fclose($f);

	// return $col_count;
}

function __csv_load(string $file, array $map, string $ret_type = 'array', string $delim = ';'): array {
	if(($f = fopen($file, "r")) === false){
		return false;
	}

	$ret = [];
	while (($line = fgetcsv($f, 2000, $delim)) !== false){
		$rl = [];
		foreach($map as $k=>$v){
			$rl[$v] = ltrim(trim($line[$k]??null), "'");
		}
		$ret[] = ($ret_type == 'object' ? (object)$rl : $rl);
	}

	fclose($f);

	return $ret;
}

function csv_load($file, $map, $delim = ';'){
	return __csv_load($file, $map, 'array', $delim);
}

function csv_load_object($file, $map, $delim = ';'){
	return __csv_load($file, $map, 'object', $delim);
}

function csv_find_key($map, $field): ?string {
	foreach($map as $k=>$v){
		if($v == $field){
			return $k;
		}
	}

	return null;
}

function csv_find_value($map, $line, $field): ?string {
	if(($k = csv_find_key($map, $field)) !== null){
		return $line[$k];
	}

	return null;
}

function get_server_schema(){
	if(isset($_SERVER['HTTP_ORIGIN'])){
		if(($pos = strpos($_SERVER['HTTP_ORIGIN'], "://")) !== false){
			return substr($_SERVER['HTTP_ORIGIN'], 0, $pos);
		}
	}

	return "http";
}

function get_server_protocol(){
	return $_ENV['SERVER_PROTOCOL']??($_SERVER['SERVER_PROTOCOL']??'');
}

function __header(int $code, string $msg_header, string $msg_display = null): void {
	$SERVER_PROTOCOL = get_server_protocol();

	header("$SERVER_PROTOCOL $code $msg_header", true, $code);
	if($msg_display){
		print "<h1>$msg_display</h1>";
	}
}

function header403($msg = "Forbidden"){
	__header(403, "Forbidden", $msg);
}

function header404($msg = "Not Found"){
	__header(404, "Not Found", $msg);
}

function header410($msg = "Gone"){
	__header(410, "Gone", $msg);
}

function header503($msg = "Server error"){
	__header(503, "Server error", $msg);
}

function proc_date(string $date){
	$D = ['šodien', 'vakar', 'aizvakar'];
	$M = ['janvārī', 'februārī', 'martā', 'aprīlī', 'maijā', 'jūnijā', 'jūlijā', 'augustā', 'septembrī', 'oktobrī', 'novembrī', 'decembrī'];

	$date_now = date("Y:m:j:H:i");
	list($y0, $m0, $d0, $h0, $min0) = to_int(explode(":", date("Y:m:j:H:i", strtotime($date))));
	list($y1, $m1, $d1, $h1, $min1) = to_int(explode(":", $date_now));
	// mktime ( [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]])
	$dlong0 = mktime($h0, $min0, 0, $m0, $d0, $y0);
	$dlong1 = mktime($h1, $min1, 0, $m1, $d1, $y1);
	$diff = date('z', $dlong1) - date('z', $dlong0);
	$retdate = '';

	if( ($diff < 3) && ($y1 == $y0) )
	//if( ($diff < 3) /*&& ($y1 == $y0)*/ )
	{
		$retdate .= $D[$diff];
	} else {
		if($y1 != $y0)
			$retdate .= "$y0. gada ";
		$retdate.= "$d0. ".$M[$m0 - 1];
	}

	//if((integer)$h0 || (integer)$min0)
		$retdate .= ", plkst. $h0:$min0";

	return $retdate;
}

function print_time(float $start_time, float $end_time = null): string {
	if(is_null($end_time))$end_time = microtime(true);

	$seconds = $end_time - $start_time;

	$print_time = [];
	if($d = floor($seconds / 86400)){
		$print_time[] = $d."d";
		$seconds -= $d * 86400;
	}

	if($h = floor($seconds / 3600)){
		$print_time[] = $h."h";
		$seconds -= $h * 3600;
	}

	if($m = floor($seconds / 60)){
		$print_time[] = $m."min";
		$seconds -= $m * 60;
	}

	$print_time[] = sprintf("%.3f sec", $seconds);

	return join(" ", $print_time);
}

function print_nanotime(array $start_time, array $end_time = null, int $precision = 4): string {
	if(is_null($end_time))$end_time = hrtime(false);

	list($s1, $ns1) = $start_time;
	list($s2, $ns2) = $end_time;

	$seconds = $s2 - $s1;

	$print_time = [];
	if($seconds > 86400){
		$d = floor($seconds / 86400);
		$print_time[] = $d."d";
		$seconds -= $d * 86400;
	}

	if($seconds > 3600){
		$h = floor($seconds / 3600);
		$print_time[] = $h."h";
		$seconds -= $h * 3600;
	}

	if($seconds > 60){
		$m = floor($seconds / 60);
		$print_time[] = $m."min";
		$seconds -= $m * 60;
	}

	$seconds += ($ns2 - $ns1) / 1e9;

	$print_time[] = sprintf("%.".$precision."f sec", $seconds);

	return join(" ", $print_time);
}

function print_memory($mem, $precision = 2): string {
	if(($GB = number_format($mem / 1024 / 1024 / 1024, $precision, '.', '')) > 1){
		$ret = "$GB GB";
	} elseif(($MB = number_format($mem / 1024 / 1024, $precision, '.', '')) > 1){
		$ret = "$MB MB";
	} elseif(($KB = number_format($mem / 1024, $precision, '.', '')) > 1){
		$ret = "$KB KB";
	} else {
		$ret = (string)$mem;
	}

	return $ret;
}

function selected($v, $value): string {
	return sprintf(' value="%s"%s', $v, $v == $value ? ' selected' : '');
}

function optioned($v, $value): string {
	return sprintf(' value="%s"%s', $v, checked($v == $value));
}

function checked(mixed $v): string {
	return ($v ? ' checked' : '');
}

function checkeda(array $a, $k): string {
	return checked($a[$k]??null);
}

function checkedina(array $a, mixed $v): string {
	return checked(in_array($v, $a));
}

# Hacking POST checkboxes
function boolcheckbox($NAME, $checked): string {
	$ret[] = sprintf('<input type=hidden value=0 name=%s>', $NAME);
	$ret[] = sprintf('<input type=checkbox value=1 name=%s%s>', $NAME, checked($checked));
	return join("\n", $ret);
}

function datediff(string $d1, string $d2, $calc = 3600 * 24): int|false {
	$date1 = strtotime($d1);
	$date2 = strtotime($d2);
	// $date1 = $d1 ? strtotime($d1) : time();
	// $date2 = $d2 ? strtotime($d2) : time();

	if($date1 === false || $date2 === false || $calc == 0){
		return false;
	}

	return (int)round(($date1 - $date2) / $calc);
}

function vardiem($int, $CURR_ID = 'EUR'){
	return LV::vardiem($int, $CURR_ID);
}

function xml2array($xml, $d = 0) {
	//print str_repeat(" ", $d * 2).sprintf("%s(%s)\n", $xml->getName(), $xml->count());
	if($xml->count()){
		$result = [];
		foreach($xml->children() as $k=>$c) {
			$r = xml2array($c, $d + 1);
			/*
			if(is_array($r)){
				$result[$k][] = $r;
			} else {
				$result[$k] = $r;
			}
			*/
			if(isset($result[$k])){
				if(isset($result[$k][0]))
					$result[$k][] = $r;
				else
					$result[$k] = array_merge([$result[$k]], [$r]);
			} else {
				$result[$k] = $r;
			}
		}
	} else {
		$result = "$xml";
	}
	return $result;
}

function kdsort(&$a){
	ksort($a);
	foreach(array_keys($a) as $k){
		if(is_array($a[$k]))
			kdsort($a[$k]);
	}
}

function __merge(mixed $o1, mixed $o2, array $fields = null): mixed {
	if(is_null($o1) && is_null($o2)){
		return null;
	}

	if(is_array($o2) || $o2 instanceof ArrayAccess){
		$a2 = $o2;
	} elseif($o2 instanceof stdClass || $o2 instanceof Traversable){
		$a2 = get_object_vars($o2);
	} else {
		return $o2;
	}

	// if($o2 instanceof stdClass || $o2 instanceof Traversable){
	// 	$a2 = get_object_vars($o2);
	// } elseif(is_array($o2)){
	// 	$a2 = $o2;
	// } else {
	// 	return $o2;
	// 	// if(is_array($o1)){
	// 	// 	return (array)$o2;
	// 	// } else {
	// 	// 	return $o2;
	// 	// }
	// }

	if($fields){
		foreach($a2 as $k=>$v){
			if(!in_array($k, $fields)){
				unset($a2[$k]);
			}
		}
	}

	if(is_array($o1)){
		foreach($a2 as $k=>$v)$o1[$k] = merge($o1, $v);
	} elseif($o1 instanceof ArrayAccess){
		foreach($a2 as $k=>$v)$o1[$k] = merge($o1[$k]??null, $v);
	} elseif($o1 instanceof stdClass || $o1 instanceof Traversable){
		foreach($a2 as $k=>$v)$o1->{$k} = merge($o1->{$k}??null, $v);
	} else {
		return $o2;
	}

	// if($o1 instanceof stdClass || $o1 instanceof Traversable){
	// 	foreach($a2 as $k=>$v)$o1->{$k} = merge($o1->{$k}??null, $v);
	// } elseif(is_array($o1)){
	// 	foreach($a2 as $k=>$v)$o1[$k] = merge($o1, $v);
	// } else {
	// 	return $o2;
	// }

	return $o1;
}

function merge(mixed $o1, mixed $o2){
	return __merge($o1, $o2);
}

function merge_only(array $fields, mixed $o1, mixed $o2 = null){
	if(is_null($o2)){
		$o2 = $o1;
		$o1 = is_array($o2) ? [] : (is_object($o2) ? new stdClass : $o1);
	}

	return __merge($o1, $o2, $fields);
}

function is_climode(){
	return php_sapi_name() === 'cli';
}

function println(){
	call_user_func_array('printf', func_get_args());
	print is_climode() ? "\n" : "<br>\n";
}

function __vpbc(){
	$args = func_get_args();
	$f = (string)array_shift($args);
	$v1 = (string)array_shift($args);
	foreach($args as $v){
		$v1 = $f($v1, (string)$v);
	}

	return $v1;
}
function vpaddr(&$v1){
	return $v1 = call_user_func_array('__vpbc', array_merge(['bcadd'], func_get_args()));
}
function vpsubr(&$v1){
	return $v1 = call_user_func_array('__vpbc', array_merge(['bcsub'], func_get_args()));
}
function vpmulr(&$v1){
	return $v1 = call_user_func_array('__vpbc', array_merge(['bcmul'], func_get_args()));
}
function vpdivr(&$v1){
	return $v1 = call_user_func_array('__vpbc', array_merge(['bcdiv'], func_get_args()));
}
function vpadd(){
	return call_user_func_array('__vpbc', array_merge(['bcadd'], func_get_args()));
}
function vpsub(){
	return call_user_func_array('__vpbc', array_merge(['bcsub'], func_get_args()));
}
function vpmul(){
	return call_user_func_array('__vpbc', array_merge(['bcmul'], func_get_args()));
}
function vpdiv(){
	return call_user_func_array('__vpbc', array_merge(['bcdiv'], func_get_args()));
}

function between(mixed $v, mixed $s, mixed $e): bool {
	return ($v >= $s) && ($v <= $e);
}

function within(mixed $v, mixed $s, mixed $e): bool {
	return ($v > $s) && ($v < $e);
}

function eo($data = null): StdObject {
	return new StdObject($data);
}

function eoe($data = null): StdObject {
	if($data instanceof dqdp\StdObject){
		return $data;
	} else {
		return eo($data);
	}
}

# TODO: apvienot sqlr,eo_debug,printr
function eo_debug(StdObject $o, $keys = null){
	if(is_null($keys)){
		$keys = array_keys(get_object_vars($o));
	}

	$ret = [];
	foreach($keys as $k){
		if(!$o->exists($k)){
			continue;
		}
		$msg = "$k=";
		if($o->{$k} instanceof dqdp\StdObject){
			$msg .= 'eo{'.eo_debug($o->{$k}).'}';
		} elseif(is_object($o->{$k})){
			$msg .= "{".join(",", get_object_vars($o->{$k}))."}";
		} elseif(is_array($o->{$k})){
			$msg .= "[".join(",", array_flatten($o->{$k}))."]";
		} elseif(is_bool($o->{$k})){
			$msg .= (int)$o->{$k};
		} else {
			$msg .= $o->{$k};
		}
		$ret[] = $msg;
	}

	return join(",", $ret);
}

# TODO: kāpēc return $args?
// function escape_shell(Array $args){
// 	foreach($args as $k=>$part){
// 		if(is_string($k)){
// 			$params[] = escapeshellarg($k).'='.escapeshellarg($part);
// 		} else {
// 			$params[] = escapeshellarg($part);
// 		}
// 	}
// 	return $args;
// }

function is_windows(): bool {
	return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function get_include_paths(): array {
	return array_map(function($i){
		return realpath($i);
	}, explode(PATH_SEPARATOR, ini_get('include_path')));
}

function trim_includes_path(string $path): ?string {
	if($path = realpath($path)){
		foreach(get_include_paths() as $root){
			$root = $root.DIRECTORY_SEPARATOR;
			if(strpos($path, $root) === 0){
				return str_replace($root, '', $path);
			}
		}
	} else {
		return null;
	}
}

function trimmer($data){
	return __object_map($data, function(string $item){
		return trim($item);
	});
}

function is_fatal_error($errno): bool {
	return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
}

function is_php_fatal_error($errno): bool {
	return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]);
}

function prepend_path(string $path, string $cmd): string {
	$cmd = is_windows() ? "$cmd.exe" : $cmd;
	if($path = realpath($path)){
		return $path.DIRECTORY_SEPARATOR.$cmd;
	} else {
		return $cmd;
	}
}

function wkhtmltopdf($HTML){
	$args = ["-", "-"];
	$wkhtmltopdf = prepend_path(constant('WKHTMLTOPDF_BIN'), "wkhtmltopdf");

	return proc_exec('"'.$wkhtmltopdf.'"', $args, $HTML);
}

// function proc_prepare_args($cmd, $args = []){
// 	if($args){
// 		$cmd .= ' '.join(" ", escape_shell($args));
// 	}
// 	return $cmd;
// }

# TODO: create return type
function proc_exec(string $cmd, array $args = [], string $input = '', array $descriptorspec = []){
	if(empty($descriptorspec[0]))$descriptorspec[0] = ["pipe", "r"];
	if(empty($descriptorspec[1]))$descriptorspec[1] = ["pipe", "w"];
	if(empty($descriptorspec[2]))$descriptorspec[2] = ["pipe", "w"];

	// Wrapperis
	// https://github.com/cubiclesoft/createprocess-windows
	// $use_wrapper = false;
	// if($use_wrapper){
	// 	$cp_args = ['/w=5000', '/term', '"'.$cmd.'"'];
	// 	$process_cmd = proc_prepare_args('C:\bin\createprocess.exe', array_merge($cp_args, $args));
	// } else {
	// $cmd .= sprintf("1> %s 2> %s", escapeshellarg($temp_stdout), escapeshellarg($temp_stderr));

	// $process_cmd = proc_prepare_args($cmd, $args);
	$process_cmd = $cmd.' '.join(" ", $args);

	# Fix pipe-blocks
	$temp_stdout = tempnam(sys_get_temp_dir(), substr(md5((string)rand()), 8));
	$temp_stderr = tempnam(sys_get_temp_dir(), substr(md5((string)rand()), 8));
	// $temp_stdout = 'C:\Program Files\test';
	// $process_cmd .= ' '.sprintf("1> %s", ($temp_stdout));
	// $process_cmd .= ' '.sprintf("2> %s", ($temp_stderr));
	$descriptorspec[1] = ['file', ($temp_stdout), 'w'];
	$descriptorspec[2] = ['file', ($temp_stderr), 'w'];

	// $process_cmd .= sprintf(" 1> %s 2> %s", escapeshellarg($temp_stdout), escapeshellarg($temp_stderr));

	$process = proc_open($process_cmd, $descriptorspec, $pipes);

	if(!is_resource($process)){
		return false;
	}

	if($input && isset($pipes[0]) && is_resource($pipes[0])){
		fwrite($pipes[0], $input);
		fclose($pipes[0]);
	}

	$errcode = proc_close($process);

	if(file_exists($temp_stdout)){
		$stdout = file_get_contents($temp_stdout);
		if(!$errcode)
			unlink($temp_stdout);
	} else {
		$stdout = null;
	}

	if(file_exists($temp_stderr)){
		$stderr = file_get_contents($temp_stderr);
		if(!$errcode)
			unlink($temp_stderr);
	} else {
		$stderr = null;
	}

	return [$errcode, $stdout, $stderr];
}

function debug2file($msg){
	$msg = str_replace(array("\r", "\n"), ' ', $msg);
	return file_put_contents(constant('TMPDIR').DIRECTORY_SEPARATOR.'.'.DIRECTORY_SEPARATOR.'debug.log', sprintf("[%s] %s\n", date('c'), $msg), FILE_APPEND);
}

function get_ex_rate($CURR_ID, $date = null): ?float {
	$ts = $date ? strtotime($date) : time();

	$url = "http://www.bank.lv/vk/ecb.xml";
	if($ts)$url .= "?date=".date('Ymd', $ts);

	if($xml = simplexml_load_file(rawurlencode($url))){
		foreach($xml->Currencies->Currency as $Item){
			if((string)$Item->ID == $CURR_ID){
				return (float)$Item->Rate;
			}
		}
	}

	return null;
}

function array_enfold($v) : array {
	return is_array($v) ? $v : [$v];
}

function wrap_elements($o, $s1, $s2 = null){
	return __object_map($o, function($v) use ($s1, $s2){
		return $s1.$v.($s2??$s1);
	});
}

# Select from key value pairs array
function html_select_prepare($data, $k_field, $l_field = null){
	foreach($data as $v){
		yield $v->{$k_field} => $v->{$l_field??$k_field};
	}
}

function __options_select_kv(iterable $data, $selected = null): string {
	foreach($data as $k=>$v){
		$ret[] = "<option".selected($k, $selected).">$v</option>";
	}

	return join("", $ret??[]);
}

function options_select(iterable $data, string $vk, string $lk, $selected = null): string {
	foreach($data as $i){
		$ret[] = "<option".selected($i[$vk], $selected).">$i[$lk]</option>";
	}

	return join("", $ret??[]);
}

function array_search_k(array|object $arr, $k, $v): mixed {
	foreach($arr as $i=>$item){
		if(is_scalar($item)){
			if($item === $v){
				return $i;
			}
		} else {
			if(get_prop($item, $k) === $v){
				return $i;
			}
		}
	}

	return null;
}

function parse_search_q(string $q, int $minWordLen = 0): array {
	$words = preg_split('/\s/', $q);

	foreach($words as $word){
		if(
			($word = trim($word)) &&
			($index = mb_strtolower($word)) &&
			empty($buf[$index]) &&
			(mb_strlen($word) >= $minWordLen)
		){
			$buf[$index] = true;
			$ret[] = $word;
		}
	}

	return $ret??[];
}

// function parse_search_q($q, $minWordLen = 0){
// 	$q = preg_replace('/[%,\'\.]/', ' ', $q);
// 	$words = explode(' ', $q);

// 	foreach($words as $k=>$word){
// 		if(($word = trim($word)) && (mb_strlen($word) >= $minWordLen)){
// 			$words[$k] = mb_strtoupper($word);
// 		} else {
// 			unset($words[$k]);
// 		}
// 	}
// 	return array_unique($words);
// }

function split_words(string $q){
	return preg_split('/\s+/', trim($q));
}

function is_valid_host(string $host){
	$testip = gethostbyname($host);
	$test1 = ip2long($testip);
	$test2 = long2ip($test1);

	return ($testip == $test2);
}

function is_valid_email(string $email){
	$parts = explode('@', $email);

	if(count($parts) != 2){
		return false;
	}

	list($username, $domain) = $parts;

	if(strlen($username) > 64){
		return false;
	}

	return ($username && $domain && (is_valid_host($domain) || checkdnsrr($domain, 'MX')));
}

function accepts_gzip(){
	return substr_count($_SERVER['HTTP_ACCEPT_ENCODING']??'', 'gzip');
}

function get_mime(string $buf){
	if(!extension_loaded('fileinfo')){
		return false;
	}

	$fi = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_buffer($fi, $buf);
	finfo_close($fi);

	return $mime;
}

// function to_string($data = null){
// 	return __object_reduce($data, function($carry, $item, $key){
// 		//return "$c$key=$item";
// 		// if(is_array($item) || is_object($item)){
// 		// 	return "aaa\t".to_string($item);
// 		// }
// 		return $carry.($carry?"\n":"")."$key=$item";
// 	}, '');
// }

// function mkdir_full($dir){
// 	printr(pathinfo($dir));
// 	return;
// 	$da = explode('/', $dir);
// 	$path = '';
// 	foreach($da as $item){
// 		$path .= "/$item";
// 		if(!file_exists($path)){
// 			mkdir($path);
// 		}
// 	}
// }

// function is_function($params){
// 	if(is_scalar($params)){
// 		return is_callable($params);
// 	} elseif(is_object($params)){
// 	}

// 	if(is_callable([$DATA, $k]) || $DATA->{$k} instanceof Closure){

// 	}
// }

# TODO: write tests
function hl(string $data, string $kw): string {
	//strip_script($data, $keys, $scripts);
	$colors = array('white', 'white', 'black', 'white');
	$bg = array('red', 'blue', 'yellow', 'magenta');
	$cc = count($colors);
	$bc = count($bg);

	$kw = trim(preg_replace("/[\*\(\)\-\+\/\:]/", " ", $kw));

	$words = explode(' ', $kw);
	// duplikaati nafig
	$words = array_unique($words);

	//$tokens = array();
	foreach($words as $index=>$word)
	{
		$word = preg_replace('/[<>\/]/', '', $word);
		//$word = substitute(preg_quote($word));
		$word = substitute(preg_quote($word));

		if(empty($word))
			continue;

		$color = $colors[$index % $cc];
		$bgcolor = $bg[$index % $bc];
		$data = ">$data<";
		//$patt = "/(>[^<]*)(".substitute(preg_quote($word)).")([^>]*)<?/imsUu";
		//$patt = "/(>[^<]*)(".substitute($word).")([^>]*)<?/imsUu";
		$patt = "/(>[^<]*)(".$word.")([^>]*)<?/imsUu";

		//$data = preg_replace($patt, "$1<span style=\"background-color: $bgcolor; color: $color; font-weight: bold;\">$2</span>$3", $data);
		$data = preg_replace($patt, "$1<mark style=\"background-color: $bgcolor; color: $color; font-weight: bold;\">$2</mark>$3", $data);
		$data = mb_substr($data, 1, mb_strlen($data)-2);
	}

	//unstrip_script($data, $keys, $scripts);
	return $data;
} // hl

# TODO: pārbaudīt vai tas nesakrīt ar translit
function substitute_change(string $str){
	$patt = [
		"'Ā'", "'Č'", "'Ē'", "'Ģ'", "'Ī'", "'Ķ'", "'Ļ'", "'Ņ'", "'Ō'", "'Ŗ'", "'Š'", "'Ū'", "'Ž'",
		"'ā'", "'č'", "'ē'", "'ģ'", "'ī'", "'ķ'", "'ļ'", "'ņ'", "'ō'", "'ŗ'", "'š'", "'ū'", "'ž'",
	];
	$repl = [
		"A", "C", "E", "G", "I", "K", "L", "N", "O", "R", "S", "U", "Z",
		"a", "c", "e", "g", "i", "k", "l", "n", "o", "r", "s", "u", "z",
	];

	return preg_replace($patt, $repl, $str);
}

function substitute(string $str){
	/*
	$patt = array(
		"/([ĀČĒĢĪĶĻŅŌŖŠŪŽ])/iue"
	);
	$repl = array(
		"'[$1|'.substitute_change('$1').']'"
	);
	return preg_replace($patt, $repl, $str);
	*/
	$patt = ["/([ĀČĒĢĪĶĻŅŌŖŠŪŽ])/iu"];
	return preg_replace_callback(
		$patt,
		function($m){
			return "[".$m[1]."|".substitute_change($m[1])."]";
		},
		$str);
}

function join_paths(array $a): string {
	return join(DIRECTORY_SEPARATOR, $a);
}

function str_limiter($str, $limit, $append){
	if(strlen($str)>$limit){
		return substr($str, 0, $limit).$append;
	}

	return $str;
}

# TODO: remove throws?
function prop_exists(array|object|null $o, string|int $k): bool {
	if(is_null($o)){
		return false;
	} elseif(is_array($o)){
		return key_exists($k, $o);
	} elseif($o instanceof ArrayAccess){
		return $o->offsetExists($k);
	// } elseif($o instanceof Generator){
	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	} elseif(is_object($o)){
		return is_int($k) ? property_exists($o, (string)$k) : property_exists($o, $k);
	} else {
		throw new InvalidTypeException($o);
	}
}

function prop_initialized(array|object|null $o, string|int $k): bool {
	if(is_null($o)){
		return false;
	} elseif(is_array($o)){
		return key_exists($k, $o);
	} elseif(is_object($o)){
		return (new ReflectionObject($o))->getProperty($k)->isInitialized($o);
	} else {
		throw new InvalidTypeException($o);
	}
}

# TODO: vajag vai nevajag ar referenci??
function &get_prop_ref(array|object|null $o, string|int $k): mixed {
	if(is_null($o)){
		return null;
	} elseif(is_array($o) || $o instanceof ArrayAccess){
		return $o[$k];
	// } elseif($o instanceof Generator){
	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	} elseif(is_object($o)){
		return $o->{$k};
	} else {
		throw new InvalidTypeException($o);
	}
}
function get_prop(array|object|null $o, string|int $k): mixed {
	if(is_null($o)){
		return null;
	} elseif(is_array($o) || $o instanceof ArrayAccess){
		return $o[$k];
	// } elseif($o instanceof Generator){
	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	} elseif(is_object($o)){
		return $o->{$k};
	} else {
		throw new InvalidTypeException($o);
	}
}

function unset_prop(array|object &$o, string|int $k): void {
	if(is_array($o) || $o instanceof ArrayAccess){
		unset($o[$k]);
	// } elseif($o instanceof Generator){
	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	} elseif(is_object($o)){
		unset($o->{$k});
	} else {
		throw new InvalidTypeException($o);
	}
}

function set_prop(array|object &$o, string|int $k, mixed $v): void {
	if(is_array($o) || $o instanceof ArrayAccess){
		$o[$k] = $v;
	// } elseif($o instanceof Generator){
	// 	throw new InvalidArgumentException("Generators does not support ArrayAccess");
	} elseif(is_object($o)){
		$o->{$k} = $v;
	} else {
		throw new InvalidTypeException($o);
	}
}

function str_ends($haystack, $needle) {
	return 0 === substr_compare($haystack, $needle, -strlen($needle));
}

function ktolower(array|object $data): mixed {
	__object_walk($data, function(&$item, &$k, &$parent){
		$new_k = strtolower($k);
		if(strcmp($new_k, $k) !== 0){
			unset_prop($parent, $k);
			set_prop($parent, $new_k, $item);
		}
	});

	return $data;
}

function array_flatten(array $a): array {
	return flatten($a);
}

function flatten(array|object $o): array {
	__object_walk($o, function($i) use (&$ret){
		$ret[] = $i;
	});

	return $ret??[];
}

function getbyk(array|object $o, string|int $k): mixed {
	return flatten(__object_filter($o, function($item, $inner_k) use ($k){
		return $inner_k === $k;
	}));
}

// NxN
function array_rotate_right($map, $times){
	if(!($times = $times % 4)){
		return $map;
	}

	// 1x (r,c)->(c,n-1-r)
	// 2x (r,c)->(n-1-r,n-1-c)
	// 3x (r,c)->(n-1-c,r)
	$f = [
		1=>function(&$ret, $n, $v, $r, $c){
			$ret[$c][$n-1-$r] = $v;
		},
		2=>function(&$ret, $n, $v, $r, $c){
			$ret[$n-1-$r][$n-1-$c] = $v;
		},
		3=>function(&$ret, $n, $v, $r, $c){
			$ret[$n-1-$c][$r] = $v;
		}
	];

	$n = count($map);

	$ret = [];
	for($r = 0; $r < $n; $r++){
		for($c = 0; $c < $n; $c++){
			$f[$times]($ret, $n, $map[$r][$c], $r, $c);
		}
	}

	return $ret;
}

function array_rotate_left($map, $times){
	if(!($times = $times % 4)){
		return $map;
	}

	return array_rotate_right($map, 4 - $times);
}

function dig($domain, $type){
	// fprintf(STDERR, "Cheking $domain:\t");
	list($errcode, $stdout, $stderr) = proc_exec("dig", ["+short", $type, $domain]);
	$stdout = str_replace("\r\n", "\t", trim($stdout));
	$stdout = str_replace("\n", "\t", $stdout);
	$stdout = explode("\t", $stdout);

	// sort($stdout, SORT_STRING);

	// $stdout = join("\t", $stdout);

	return $stdout[0];
}

function parse_query_string(string $qs): array {
	$ret = [];
	$pairs = explode('&', $qs);
	foreach($pairs as $kv){
		$parts = explode('=', $kv);
		$k = isset($parts[0]) ? urldecode($parts[0]) : false;
		$v = isset($parts[1]) ? urldecode($parts[1]) : false;

		# Arrays
		if(substr($k, -2) == '[]'){
			$ka = substr($k, 0, -2);
			if(empty($ret[$ka])){
				$ret[$ka] = [];
			}
			$ret[$ka][] = $v;
		} else {
			$ret[$k] = $v;
		}
	}

	return $ret;
}

function rearrange_files_array(array $file_post): array {
	$file_ary = [];
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);

	for ($i=0; $i<$file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}

	return $file_ary;
}

function is_dqdp_statement($args): bool {
	return (count($args) == 1) && $args[0] instanceof \dqdp\SQL\Statement;
}

function get_class_public_vars(string $className){
	return get_class_vars($className);
}

function get_object_public_vars(object $o){
	return get_object_vars($o);
}

function static_prop_initialized(string $className, string|int $k): bool {
	return (new ReflectionClass($className))->getProperty($k)->isInitialized();
}

function get_multitype(mixed $o): string {
	return is_object($o) ? get_class($o) : gettype($o);
}
