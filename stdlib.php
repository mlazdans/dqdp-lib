<?php

use dqdp\LV;
use dqdp\QueueMailer;
use dqdp\EmptyObject;
use PHPMailer\PHPMailer;

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

function country_codes_eu(){
	return [
		'AT','BE','BG','CY','CZ','DK','EE','FI','FR','DE','GR','HU','HR','IE',
		'IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE','GB'
	];
}

function country_codes_eu_sql(){
	return "'".join("','", country_codes_eu())."'";
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

function redirect_not_found($url = '/', $msg = ''){
	header404($msg);
	redirect($url);
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
	return $f && !!move_uploaded_file($f['tmp_name'], $save_path);
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

function browse($path, callable $function){
	$entries = array_filter(scandir($path), function($entry){
		return $entry != '.' && $entry != '..';
	});

	array_map(function($entry) use ($path, $function) {
		$function($path, $entry);
	}, $entries);
}

function compacto($data){
	return __object_filter($data, function($item){
		return !empty($item);
	});
}

# TODO: pielikt visiem __object_*() key parametru tāpat kā __object_walk_ref()
function __object_walk($data, $func){
	if(is_array($data)){
		foreach($data as $v){
			__object_walk($v, $func);
		}
	} elseif(is_object($data)) {
		__object_walk(get_object_vars($data), $func);
	} else {
		$func($data);
	}
}

function __object_walk_ref(&$data, $func, &$i = null){
	if(is_array($data)){
		foreach($data as $k=>&$v){
			$oldK = $k;
			__object_walk_ref($v, $func, $k);
			if($oldK !== $k){
				$data[$k] = $data[$oldK];
				unset($data[$oldK]);
			}
		}
	} elseif(is_object($data)) {
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

function __object_filter($data, $func){
	$ndata = null;
	if(is_array($data)){
		$ndata = [];
		foreach($data as $k=>$v){
			if(__object_filter($v, $func)){
				$ndata[$k] = $v;
			}
		}
	} elseif(is_object($data)) {
		return __object_filter(get_object_vars($data), $func);
	} else {
		if($func($data)){
			$ndata = $data;
		}
	}
	return $ndata;
}

function __object_map($data, $func){
	if(is_array($data)){
		$ndata = [];
		foreach($data as $k=>$v){
			$ndata[$k] = __object_map($v, $func);
		}
	} elseif(is_object($data)){
		$ndata = (object)__object_map(get_object_vars($data), $func);
	} else {
		$ndata = $func($data);
	}
	return $ndata;
}

function __object_reduce($data, $func, $initial = null){
	$carry = $initial;
	if(is_array($data)){
		foreach($data as $v){
			$carry = __object_reduce($v, $func, $carry);
		}
	} elseif(is_object($data)) {
		$carry = __object_reduce(get_object_vars($data), $func, $carry);
	} else {
		$carry = $func($carry, $data);
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
		if(is_array($item) || is_object($item))
			return $carry && is_empty($item);
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

function specialchars($data){
	return __object_map($data, function($item){
		return htmlspecialchars($item, ENT_COMPAT | ENT_HTML401, '', false);
	});
}

function date_in_periods($date, Array $periods){
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

function date_startend($DATE){
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
	} elseif($DATE->YESTERDAY) {
		$start_date = $end_date = strtotime('yesterday');
		$end_date = time();
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
	parse_str($format, $FORMAT);

	foreach($allowed as $k=>$v){
		unset($QS[$k]);
	}

	foreach($FORMAT as $k=>$v){
		if($k{0} == '-'){
			$k2 = substr($k, 1);
			if(!$v || $v == $QS[$k2]){
				unset($QS[$k2]);
			}
		} else {
			$QS[$k] = $v;
		}
	}

	$ret = [];
	foreach($QS as $k=>$v){
		$ret[] = "$k=$v";
	}

	return join($delim, $ret);
}

function format_debug($v){
	$vars = __object_map($v, function($item){
		if(is_scalar($item) && mb_detect_encoding($item)){
			return mb_substr($item, 0, 500);
		} elseif(is_null($item)) {
			return "NULL";
		} elseif(is_resource($item)) {
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
		if(is_object($v) && (get_class($v) == 'dqdp\SQL\Select')){
			$vars = format_debug($v->vars());
			print_r((string)$v);
			print ("\n\n[Bind vars]\n");
			print_r($vars);
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

function __output_wrapper($data, $func){
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
	list($net, $cidr) = split('/', $pNetwork);

	$ipLong = net2long($pIp);
	$netLong = net2long($net);
	$mask = net2long(cidr2net($cidr));

	return ($ipLong & $mask) == ($netLong & $mask);
}

function array_sort_len($a){
	array_multisort(array_map('strlen', $a), SORT_NUMERIC, SORT_DESC, $a);
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

function strip_path($service) {
	return preg_replace('/[\/\.\\\]/', '', $service);
}

function urlize($name){
	$name = preg_replace("/[%]/", " ", $name);
	$name = html_entity_decode($name, ENT_QUOTES);
	$name = mb_strtolower($name);
	$name = strip_tags($name);
	$name = preg_replace("/[\:\/\?\#\[\]\@\"'\(\)\.,&;\+=\\\]/", " ", $name);
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

	$mail->Encoding = QueueMailer::ENCODING_QUOTED_PRINTABLE;
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

function csv_col_count($file){
	if(($f = fopen($file, "r")) === false){
		return false;
	}

	$col_count = 0;
	if(($line = fgetcsv($f, 2000, ';')) !== false){
		$col_count = count($line);
	}
	fclose($f);

	return $col_count;
}

function __csv_load($file, $map, $ret_type = 'array'){
	if(($f = fopen($file, "r")) === false){
		return false;
	}

	$ret = [];
	while (($line = fgetcsv($f, 2000, ';')) !== false){
		$rl = [];
		foreach($map as $k=>$v){
			$rl[$v] = ltrim(trim($line[$k]??null), "'");
		}
		$ret[] = ($ret_type == 'object' ? (object)$rl : $rl);
	}

	fclose($f);

	return $ret;
}

function csv_load($file, $map){
	return __csv_load($file, $map, 'array');
}

function csv_load_object($file, $map){
	return __csv_load($file, $map, 'object');
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

function checkeda($a, $k){
	return checked($a[$k]??false);
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

# $value = stdval or comma separated multiples
function sql_create_int_filter($field, $values){
	$v = array_map(function($i){
		return (int)$i;
	}, $values); // explode(",", $values)

	return ["$field IN (".join(",", array_fill(0, count($v), "?")).")", $v];
}

function sql_create_filter($filter, $join = "AND"){
	if(!is_array($filter)){
		$filter = [$filter];
	}
	return $filter ? sprintf("(%s)", join(" $join ", $filter)) : '';
}

function sql_add_filter(&$filter, &$values, $newf){
	if(!isset($newf[0])){
		return;
	}

	$filter[] = $newf[0];

	if(!isset($newf[1])){
		return;
	}

	if(is_array($newf[1])){
		$values = array_merge($values, $newf[1]);
	} else {
		$values[] = $newf[1];
	}
}

function where($filter, $join = "AND"){
	return ($f = sql_create_filter($filter, $join)) ? " WHERE $f" : '';
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

function __merge($o1, $o2, $fields){
	if(is_null($o1) && is_null($o2)){
		return null;
	}

	if(is_object($o2)){
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

	if(is_object($o1)){
		foreach($a2 as $k=>$v)$o1->{$k} = merge($o1->{$k}??null, $v);
	} elseif(is_array($o1)){
		foreach($a2 as $k=>$v)$o1[$k] = merge($o1, $v);
	} else {
		return $o2;
	}

	return $o1;
}

function merge($o1, $o2){
	return __merge($o1, $o2, null);
}

function merge_only($fields, $o1, $o2 = null){
	if(is_null($o2)){
		$o2 = is_array($o1) ? [] : (is_object($o1) ? new stdClass : $o2);
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

function build_sql($fields, $DATA = null){
	foreach($fields as $k){
		$values[] = $DATA->{$k} ?? null;
	}

	return [join(",", $fields), join(",", array_fill(0, count($fields), "?")), $values??[]];
}

function build_sql_set($fields, $DATA){
	$values = $nfields = [];
	foreach($fields as $k){
		if(isset($DATA->{$k})){
			$values[] = $DATA->{$k};
			$nfields[] = $k;
		}
	}

	return [join(",", $nfields), join(",", array_fill(0, count($nfields), "?")), $values];
}

function eo($data = null){
	return new EmptyObject($data);
}

function eoe($data = null){
	if($data instanceof dqdp\EmptyObject){
		return $data;
	} else {
		return eo($data);
	}
}

function escape_shell(Array $args, $glue = ' '){
	foreach($args as $k => $part){
		if(is_string($k)){
			$params[] = escapeshellarg($k).$glue.escapeshellarg($part);
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

function wkhtmltopdf($HTML){
	$args = ["-", "-"];
	$wkhtmltopdf = getenv('WKHTMLTOPDF')??(is_windows() ? "wkhtmltopdf.exe" : "wkhtmltopdf");

	return proc_exec($HTML, $wkhtmltopdf, $args);
}

function proc_exec($input, $cmd, $args = [], $descriptorspec = []){
	if(empty($descriptorspec[0]))$descriptorspec[0] = ["pipe", "r"];
	if(empty($descriptorspec[1]))$descriptorspec[1] = ["pipe", "w"];
	if(empty($descriptorspec[2]))$descriptorspec[2] = ['file', getenv('TMPDIR')."./proc_exec-stderr.log", 'a'];

	$process_cmd = '"'.$cmd.'"';
	if($args){
		$process_cmd .= ' '.join(" ", escape_shell($args));
	}

	$process = proc_open($process_cmd, $descriptorspec, $pipes);
	if(is_resource($process)){
		if(isset($pipes[1]))stream_set_blocking($pipes[1], 0);
		if(isset($pipes[2]))stream_set_blocking($pipes[2], 0);
		fwrite($pipes[0], $input);
		fclose($pipes[0]);

		$output = null;
		if(isset($pipes[1])){
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}

		return [proc_close($process), $output];
	}
	return false;
}

function debug2file($msg){
	file_put_contents(getenv('TMPDIR').'./debug.log', sprintf("[%s] %s\n", date('c'), $msg), FILE_APPEND);
}
