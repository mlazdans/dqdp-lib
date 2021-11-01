<?php

use dqdp\LV;
use dqdp\QueueMailer;
use dqdp\StdObject;
use PHPMailer\PHPMailer\PHPMailer;

require_once("qblib.php");

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

function redirect($url = ''){
	$url = $url ? $url : php_self();
	header("Location: $url");
}

function redirectp($url){
	header("Location: $url",true, 301);
}

function redirect_not_found($url = '/', $msg = ''){
	header404($msg);
	redirect($url);
}

function redirect_referer($default = "/"){
	if(empty($_SERVER['HTTP_REFERER'])){
		redirect($default);
	} else {
		redirect($_SERVER["HTTP_REFERER"]);
	}
}


function floatpoint($val){
	$val = preg_replace('/[^0-9,\.\-]/', '', $val);
	return str_replace(',', '.', $val);
}

function to_float($data){
	return __object_map($data, function($item){
		return floatval(floatpoint($item));
	});
}

function to_int($data){
	return __object_map($data, function($item){
		return intval($item);
	});
}

function money_conv($data){
	return floatpoint($data);
}

function money_round($data){
	return number_format(money_conv($data), 2, '.', '');
}

function to_money($data){
	return __object_map($data, function($item){
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

function numpad($data, $size = 2){
	return str_pad($data, $size, "0", STR_PAD_LEFT);
}

function md5uniqid(){
	return md5(uniqid(rand(), true));
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

function compacto($data) {
	return __object_filter($data, function($item){
		return (bool)$item;
	});
}

function __object_walk($data, callable $func, $i = null){
	if(is_array($data)){
		foreach($data as $k=>$v){
			__object_walk($v, $func, $k);
		}
	} elseif($data instanceof stdClass || $data instanceof Traversable) {
		foreach(get_object_vars($data) as $k=>$v){
			__object_walk($v, $func, $k);
		}
	} else {
		$func($data, $i);
	}
}

function __object_walk_ref(&$data, callable $func, &$i = null){
	if(is_array($data)){
		foreach($data as $k=>&$v){
			$oldK = $k;
			__object_walk_ref($v, $func, $k);
			if($oldK !== $k){
				$data[$k] = $data[$oldK];
				unset($data[$oldK]);
			}
		}
	} elseif($data instanceof stdClass || $data instanceof Traversable) {
		foreach(get_object_vars($data) as $k=>$v){
			$oldK = $k;
			__object_walk_ref($data->{$k}, $func, $k);
			if($oldK !== $k){
				$data->{$k} = $data->{$oldK};
				unset($data->{$oldK});
			}
		}
	} else {
		$func($data, $i);
	}
}

/**
 * Nebūtu slikti izdomāt veidu, kā ērtāk apstrādāt obj un array pašā $func
 * Pagaidām $func dabū tikai ne-(obj|arr)
 * Tas palīdzētu tādām f-ijām, kas čeko [] vai empty object
 */
function __object_filter($data, callable $func, $i = null){
	if(is_array($data)){
		foreach($data as $k=>$v){
			$v2 = __object_filter($v, $func, $k);
			if(is_null($v2)){
				unset($data[$k]);
			} else {
				$data[$k] = $v2;
			}
		}
		return $data;
	} elseif($data instanceof stdClass || $data instanceof Traversable) {
		$d = clone $data;
		foreach(get_object_vars($d) as $k=>$v){
			$v2 = __object_filter($v, $func, $k);
			if(is_null($v2)){
				unset($d->{$k});
			} else {
				$d->{$k} = $v2;
			}
		}
		return $d;
	} else {
		if($v = $func($data, $i)){
			return $data;
		}
	}
}
/*
function __object_filterk($data, $func, $i = null){
	if(is_array($data)){
		foreach($data as $k=>$v){
			if($v2 = __object_filter($v, $func, $k)){
				$data[$k] = $v2;
			} else {
				unset($data[$k]);
			}
		}
		return $data;
	} elseif(is_object($data)) {
		$d = clone $data;
		foreach(get_object_vars($d) as $k=>$v){
			if($v2 = __object_filter($v, $func, $k)){
				$d->{$k} = $v2;
			} else {
				unset($d->{$k});
			}
		}
		return $d;
	} else {
		return $func($data, $i);
	}
}
*/

function __object_map($data, callable $func, $i = null){
	if(is_array($data)){
		foreach($data as $k=>$v){
			$data[$k] = __object_map($v, $func, $k);
		}
		return $data;
	} elseif($data instanceof stdClass || $data instanceof Traversable){
		$d = clone $data;
		foreach(get_object_vars($data) as $k=>$v){
			$d->{$k} = __object_map($v, $func, $k);
		}
		return $d;
	} else {
		return $func($data, $i);
	}
}

// TODO: bool carry should return immediately
function __object_reduce($data, callable $func, $carry = null, $i = null){
	if(is_array($data)){
		foreach($data as $k=>$v){
			$carry = __object_reduce($v, $func, $carry, $k);
		}
	} elseif($data instanceof stdClass || $data instanceof Traversable) {
		$carry = __object_reduce(get_object_vars($data), $func, $carry, $i);
	} else {
		$carry = $func($carry, $data, $i);
	}

	return $carry;
}

function utf2win($data){
	return __object_map($data, function($item){
		return mb_convert_encoding($item, 'ISO-8859-13', 'UTF-8');
	});
}

function win2utf($data){
	return __object_map($data, function($item){
		return mb_convert_encoding($item, 'UTF-8', 'ISO-8859-13');
	});
}

function translit($data){
	return __object_map($data, function($item){
		return iconv("utf-8","ascii//TRANSLIT", $item);
	});
}

function is_empty($data = null){
	return __object_reduce($data, function($carry, $item){
		return $carry && empty($item);
	}, true);
}

function non_empty($data = null){
	return !is_empty($data);
}

function ent($data){
	return __object_map($data, function($item){
		return htmlentities($item);
	});
}

function entdecode($data){
	return __object_map($data, function($item){
		return html_entity_decode($item);
	});
}

# https://www.gyrocode.com/articles/php-urlencode-vs-rawurlencode/
# scheme:[//[user[:password]@]host[:port]][/path][?query][#fragment]
# If you are encoding *path* segment, use rawurlencode().
# If you are encoding *query* component, use urlencode().
function urlenc($data){
	return __object_map($data, function($item){
		return urlencode($item);
	});
}
function urldec($data){
	return __object_map($data, function($item){
		return urldecode($item);
	});
}
function rawurlenc($data){
	return __object_map($data, function($item){
		return rawurlencode($item);
	});
}
function rawurldec($data){
	return __object_map($data, function($item){
		return rawurldecode($item);
	});
}
##

function specialchars($data){
	return __object_map($data, function($item){
		return htmlspecialchars($item, ENT_COMPAT | ENT_HTML401, '', false);
	});
}

function date_in_periods($date, array $periods){
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

function datef($ts = null){
	return date(get_date_format(), $ts ?? time());
}

function date_today(){
	return datef(time());
}

function date_yesterday(){
	return datef(strtotime('yesterday'));
}

function date_daycount($m = false, $y = false){
	return $m ? (date('t', mktime(0,0,0, $m, 1, ($y ? $y : date('Y'))))) : date('t');
}

function date_month_start(){
	return datef(strtotime("first day of this month"));
}

function date_month_end(){
	return datef(strtotime("last day of this month"));
}

function date_lastmonth_start(){
	return datef(strtotime("first day of previous month"));
}

function date_lastmonth_end(){
	return datef(strtotime("last day of previous month"));
}

function is_valid_date($date){
	return strtotime($date) !== false;
}

function ustrftime($format, $timestamp = 0){
	return win2utf(strftime($format, $timestamp));
}

# quarter month
function date_qt_month($C, $m = 1){
	return ($C - 1) * 3 + $m;
}

function date_startend($D){
	$DATE = eoe($D);
	$format = get_date_format();
	$start_date = $end_date = false;

	$ceturksnis = false;
	for($i = 1; $i < 5; $i++){
		if($DATE->{"C$i"}){
			$ceturksnis = $i;
		}
	}

	if($ceturksnis){
		// $start_date = mktime(0,0,0, ($ceturksnis - 1) * 3 + 1, 1, date('Y'));
		// $days_in_end_month = date_daycount(($ceturksnis - 1) * 3 + 3);
		// $end_date = mktime(0,0,0, ($ceturksnis - 1) * 3 + 3, $days_in_end_month, date('Y'));
		$start_date = mktime(0,0,0, date_qt_month($ceturksnis, 1), 1, date('Y'));
		$days_in_end_month = date_daycount(date_qt_month($ceturksnis, 3));
		$end_date = mktime(0,0,0, date_qt_month($ceturksnis, 3), $days_in_end_month, date('Y'));
	} elseif($DATE->PREV_YEAR) {
		$start_date = strtotime('first day of January last year');
		$end_date = strtotime('last day of December last year');
	} elseif($DATE->THIS_YEAR){
		$start_date = strtotime('first day of January');
		$end_date = time();
	} elseif($DATE->TODAY) {
		$start_date = $end_date = strtotime('today');
	} elseif($DATE->YESTERDAY) {
		$start_date = $end_date = strtotime('yesterday');
	} elseif($DATE->THIS_WEEK) {
		$start_date = strtotime("last Monday");
		$end_date = time();
	} elseif($DATE->THIS_MONTH) {
		$start_date = strtotime("first day of");
		$end_date = time();
	} elseif($DATE->PREV_MONTH) {
		$start_date = strtotime("first day of previous month");
		$end_date = strtotime("last day of previous month");
	} elseif($DATE->PREV_30DAYS){
		$start_date = strtotime("-30 days");
		$end_date = time();
	} elseif($DATE->MONTH){
		if(empty($DATE->YEAR))$DATE->YEAR = date('Y');
		$dc = date_daycount($DATE->MONTH, $DATE->YEAR);
		$start_date = strtotime("$DATE->YEAR-$DATE->MONTH-01");
		$end_date = strtotime("$DATE->YEAR-$DATE->MONTH-$dc");
	} elseif($DATE->YEAR){
		$start_date = strtotime("first day of January $DATE->YEAR");
		$end_date = strtotime("last day of December $DATE->YEAR");
	} else {
		if($DATE->START)$start_date = strtotime($DATE->START);
		if($DATE->END)$end_date = strtotime($DATE->END);
	}

	if($start_date)$start_date = date($format, $start_date);
	if($end_date)$end_date = date($format, $end_date);

	return [$start_date, $end_date];
}

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

# TODO: tas nestrādā, kā plānots
function format_debug($v, $depth = 0){
	$vars = __object_map($v, function($item) use ($depth){
		if(is_scalar($item) && mb_detect_encoding($item)){
			return mb_substr($item, 0, 1024).(mb_strlen($item) > 1024 ? '...' : '');
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

	return $vars;
}

# NOTE: dep on https://highlightjs.org/
function sqlr(){
	__output_wrapper(func_get_args(), function($v){
		if(!is_climode())print '<code class="sql">';
		if($v instanceof dqdp\SQL\Statement){
			print_r((string)$v);
			if(method_exists($v, 'vars')){
				print ("\n\n[Bind vars]\n");
				print_r(format_debug($v->{'vars'}()));
			}
		} else {
			print_r(format_debug($v));
		}
		if(!is_climode())print '</code>';
	});
}

function dumpr(){
	__output_wrapper(func_get_args(), "var_dump");
}

function printr(){
	__output_wrapper(func_get_args(), "print_r");
}

function __output_wrapper($data, callable $func){
	foreach($data as $v){
		if(is_climode()){
			$func($v);
			print "\n";
		} else {
			print "<pre>";
			$func($v);
			print "</pre>";
		}
	}
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

function mt(){
	return microtime(true);
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

function strip_path($data) {
	return __object_map($data, function($item){
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
		# return 1 - not found; 0 - listed
		# $c = "host -W 1 -t any $iprev.$bl";
		$c = "host -W 1 $iprev.$bl";
		$ret = exec($c, $o, $rv);
		if(!$rv){
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

	try {
		$mail->send();
		return true;
	} catch (Exception $e) {
		return $e;
	}
}

function csv_get_header($file, $delim = ';'){
	if(($f = fopen($file, "r")) === false){
		return false;
	}

	$ret = false;
	if(($line = fgetcsv($f, 2000, $delim)) !== false){
		$ret = $line;
	}
	fclose($f);

	return $ret;
}

function csv_col_count($file, $delim = ';'){
	if($line = csv_get_header($file, $delim)){
		return count($line);
	} else {
		return false;
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

function __csv_load($file, $map, $ret_type = 'array', $delim = ';'){
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

function csv_find_key($map, $field){
	foreach($map as $k=>$v){
		if($v == $field){
			return $k;
		}
	}

	return false;
}

function csv_find_value($map, $line, $field){
	if(($k = csv_find_key($map, $field)) !== false){
		return $line[$k];
	}

	return false;
}

function __header($code, $msg_header, $msg_display = null){
	$SERVER_PROTOCOL = $_ENV['SERVER_PROTOCOL']??($_SERVER['SERVER_PROTOCOL']??'');

	header("$SERVER_PROTOCOL $code $msg_header", true, $code);
	if(!is_null($msg_display)){
		print "<h1>$msg_display!</h1>";
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

function proc_date($date){
	$D = ['šodien', 'vakar', 'aizvakar'];
	$M = ['janvārī', 'februārī', 'martā', 'aprīlī', 'maijā', 'jūnijā', 'jūlijā', 'augustā', 'septembrī', 'oktobrī', 'novembrī', 'decembrī'];

	$date_now = date("Y:m:j:H:i");
	list($y0, $m0, $d0, $h0, $min0) = explode(":", date("Y:m:j:H:i", strtotime($date)));
	list($y1, $m1, $d1, $h1, $min1) = explode(":", $date_now);
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

function print_time($start_time, $end_time = false)
{
	if(!$end_time)$end_time = mt();

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

	$print_time[] = sprintf("%.2f sec", $seconds);

	return join(" ", $print_time);
}

function print_memory($mem){
	return number_format($mem / 1024 / 1024, 2, '.', '').'MB';
}

function selected($v, $value){
	return sprintf(' value="%s"%s', $v, $v == $value ? ' selected' : '');
}

function optioned($v, $value){
	return sprintf(' value="%s"%s', $v, checked($v == $value));
}

function checked($v){
	return ($v ? ' checked' : '');
}

function checkeda(Array $a, $k){
	return checked($a[$k]??null);
}

function checkedina(Array $a, $k){
	return checked(in_array($k, $a));
}

# Hacking POST checkboxes
function boolcheckbox($NAME, $checked){
	$ret[] = sprintf('<input type=hidden value=0 name=%s>', $NAME);
	$ret[] = sprintf('<input type=checkbox value=1 name=%s%s>', $NAME, checked($checked));
	return join("\n", $ret);
}

function datediff($d1, $d2, $calc = 3600 * 24){
	$date1 = strtotime($d1);
	$date2 = strtotime($d2);

	return round(($date1 - $date2) / $calc);
}

function vardiem($int, $CURR_ID){
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

function __merge($o1, $o2, array $fields = null){
	if(is_null($o1) && is_null($o2)){
		return null;
	}

	if($o2 instanceof stdClass || $o2 instanceof Traversable){
		$a2 = get_object_vars($o2);
	} elseif(is_array($o2)){
		$a2 = $o2;
	} else {
		return $o2;
	}

	if($fields){
		foreach($a2 as $k=>$v){
			if(!in_array($k, $fields)){
				unset($a2[$k]);
			}
		}
	}

	if($o1 instanceof stdClass || $o1 instanceof Traversable){
		foreach($a2 as $k=>$v)$o1->{$k} = merge($o1->{$k}??null, $v);
	} elseif(is_array($o1)){
		foreach($a2 as $k=>$v)$o1[$k] = merge($o1, $v);
	} else {
		return $o2;
	}

	return $o1;
}

function merge($o1, $o2){
	return __merge($o1, $o2);
}

function merge_only(array $fields, $o1, $o2 = null){
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

function __vp(){
	$args = func_get_args();
	$f = array_shift($args);
	$v1 = array_shift($args);
	foreach($args as $v){
		$v1 = $f($v1, $v);
	}

	return $v1;
}
function vpaddr(&$v1){
	return $v1 = call_user_func_array('__vp', array_merge(['bcadd'], func_get_args()));
}
function vpsubr(&$v1){
	return $v1 = call_user_func_array('__vp', array_merge(['bcsub'], func_get_args()));
}
function vpmulr(&$v1){
	return $v1 = call_user_func_array('__vp', array_merge(['bcmul'], func_get_args()));
}
function vpdivr(&$v1){
	return $v1 = call_user_func_array('__vp', array_merge(['bcdiv'], func_get_args()));
}
function vpadd(){
	return call_user_func_array('__vp', array_merge(['bcadd'], func_get_args()));
}
function vpsub(){
	return call_user_func_array('__vp', array_merge(['bcsub'], func_get_args()));
}
function vpmul(){
	return call_user_func_array('__vp', array_merge(['bcmul'], func_get_args()));
}
function vpdiv(){
	return call_user_func_array('__vp', array_merge(['bcdiv'], func_get_args()));
}

function between($v, $s, $e){
	return ($v >= $s) && ($v <= $e);
}

function within($v, $s, $e){
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

function escape_shell(Array $args){
	foreach($args as $k=>$part){
		if(is_string($k)){
			$params[] = escapeshellarg($k).'='.escapeshellarg($part);
		} else {
			$params[] = escapeshellarg($part);
		}
	}
	return $args;
}

function is_windows(){
	return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function get_include_paths(){
	return array_map(function($i){
		return realpath($i);
	}, explode(PATH_SEPARATOR, ini_get('include_path')));
}

function trim_includes_path($path){
	$path = realpath($path);
	foreach(get_include_paths() as $root){
		$root = $root.DIRECTORY_SEPARATOR;
		if(strpos($path, $root) === 0){
			return str_replace($root, '', $path);
		}
	}
}

function trimmer($data){
	return __object_map($data, function($item){
		return trim($item);
	});
}

function is_fatal_error($errno){
	return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
}

function is_php_fatal_error($errno){
	return in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]);
}

function prepend_path($path, $cmd){
	$cmd = is_windows() ? "$cmd.exe" : $cmd;
	if($path = realpath($path)){
		return $path.DIRECTORY_SEPARATOR.$cmd;
	} else {
		return $cmd;
	}
}

function wkhtmltopdf($HTML){
	$args = ["-", "-"];

	$wkhtmltopdf = prepend_path(getenv('WKHTMLTOPDF_BIN', true), "wkhtmltopdf");

	return proc_exec('"'.$wkhtmltopdf.'"', $args, $HTML);
}

function proc_prepare_args($cmd, $args = []){
	if($args){
		$cmd .= ' '.join(" ", escape_shell($args));
	}
	return $cmd;
}

function proc_exec($cmd, $args = [], $input = '', $descriptorspec = []){
	if(empty($descriptorspec[0]))$descriptorspec[0] = ["pipe", "r"];
	if(empty($descriptorspec[1]))$descriptorspec[1] = ["pipe", "w"];
	if(empty($descriptorspec[2]))$descriptorspec[2] = ["pipe", "w"];

	// Wrapperis
	// https://github.com/cubiclesoft/createprocess-windows
	$use_wrapper = false;
	if($use_wrapper){
		$cp_args = ['/w=5000', '/term', '"'.$cmd.'"'];
		$process_cmd = proc_prepare_args('C:\bin\createprocess.exe', array_merge($cp_args, $args));
	} else {
		$process_cmd = proc_prepare_args($cmd, $args);
	}

	$process = proc_open($process_cmd, $descriptorspec, $pipes);
	if(!is_resource($process)){
		return false;
	}

	if(isset($pipes[0]) && is_resource($pipes[0])){
		if($input){
			fwrite($pipes[0], $input);
		}
		fclose($pipes[0]);
	}

	$stdout = $stderr = null;
	if(isset($pipes[1]) && is_resource($pipes[1])){
		$stdout = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	}
	if(isset($pipes[2]) && is_resource($pipes[2])){
		$stderr = stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	}

	$errcode = proc_close($process);

	return [$errcode, $stdout, $stderr];
}

function debug2file($msg){
	$msg = str_replace(array("\r", "\n"), ' ', $msg);
	file_put_contents(getenv('TMPDIR').'./debug.log', sprintf("[%s] %s\n", date('c'), $msg), FILE_APPEND);
}

function get_ex_rate($CURR_ID, $date = false){
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

	return false;
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

function __options_select_kv($data, $selected){
	foreach($data as $k=>$v){
		$ret[] = "<option".selected($k, $selected).">$v</option>";
	}
	return join("", $ret??[]);
}

function options_select($data, $vk, $lk, $selected = null){
	foreach($data as $i){
		$ret[] = "<option".selected($i[$vk], $selected).">$i[$lk]</option>";
	}
	return join("", $ret??[]);
}

function array_search_k(array $arr, $k, $v){
	foreach($arr as $i=>$item){
		if(is_object($item)){
			$cmpv = $item->{$k}??null;
		} elseif(is_array($item)){
			$cmpv = $item[$k]??null;
		} else {
			$cmpv = $item;
		}
		if($cmpv === $v){
			return $i;
		}
	}
}

# TODO: refactor
function parse_search_q($q, $minWordLen = 0){
	$q = preg_replace('/[%,\'\.]/', ' ', $q);
	$words = explode(' ', $q);

	foreach($words as $k=>$word){
		if(($word = trim($word)) && (mb_strlen($word) >= $minWordLen)){
			$words[$k] = mb_strtoupper($word);
		} else {
			unset($words[$k]);
		}
	}
	return array_unique($words);
}

function split_words($q){
	$words = preg_split('/\s+/', trim($q));
	return $words;
}

function is_valid_host($host){
	$testip = gethostbyname($host);
	$test1 = ip2long($testip);
	$test2 = long2ip($test1);

	return ($testip == $test2);
}

function is_valid_email($email){
	if(!$email){
		return false;
	}

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

function get_mime($buf){
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

function hl(&$data, $kw)
{
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
} // hl

# TODO: pārbaudīt vai tas nesakrīt ar translit
function substitute_change($str){
	$patt = array(
		"'Ā'", "'Č'", "'Ē'", "'Ģ'", "'Ī'", "'Ķ'", "'Ļ'", "'Ņ'", "'Ō'", "'Ŗ'", "'Š'", "'Ū'", "'Ž'",
		"'ā'", "'č'", "'ē'", "'ģ'", "'ī'", "'ķ'", "'ļ'", "'ņ'", "'ō'", "'ŗ'", "'š'", "'ū'", "'ž'",
	);
	$repl = array(
		"A", "C", "E", "G", "I", "K", "L", "N", "O", "R", "S", "U", "Z",
		"a", "c", "e", "g", "i", "k", "l", "n", "o", "r", "s", "u", "z",
	);

	return preg_replace($patt, $repl, $str);
}

function substitute($str){
	/*
	$patt = array(
		"/([ĀČĒĢĪĶĻŅŌŖŠŪŽ])/iue"
	);
	$repl = array(
		"'[$1|'.substitute_change('$1').']'"
	);
	return preg_replace($patt, $repl, $str);
	*/
	$patt = array(
		"/([ĀČĒĢĪĶĻŅŌŖŠŪŽ])/iu"
	);
	return preg_replace_callback(
		$patt,
		function($m){
			//if(false && $i_am_admin)
				return "[".$m[1]."|".substitute_change($m[1])."]";
			//else
			//	return "'[".$m[1]."|'".substitute_change($m[1])."']'";
		},
		$str);
}

function join_paths($a){
	return join(DIRECTORY_SEPARATOR, $a);
}

function str_limiter($str, $limit, $append){
	if(strlen($str)>$limit){
		return substr($str, 0, $limit).$append;
	}

	return $str;
}

function get_prop($o, $k){
	if(is_object($o)){
		if(property_exists($o, $k)){
			return $o->{$k};
		}
	} elseif(is_array($o)){
		if(key_exists($k, $o)){
			return $o[$k];
		}
	}
}

function set_prop(&$o, $k, $v){
	if(is_object($o)){
		return $o->{$k} = $v;
	} elseif(is_array($o)){
		return $o[$k] = $v;
	}
}

function str_ends($haystack, $needle) {
	return 0 === substr_compare($haystack, $needle, -strlen($needle));
}

function ktolower($data){
	__object_walk_ref($data, function(&$item, &$k){
		$k = strtolower($k);
	});

	return $data;
}

function array_flatten(array $a) : array {
	return flatten($a);
}

function flatten($o) : array {
	__object_walk($o, function($i) use (&$ret){
		$ret[] = $i;
	});

	return $ret??[];
}

function getbyk($o, $k){
	return flatten(__object_filter($o, function($item, $i) use ($k){
		return $i === $k;
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
