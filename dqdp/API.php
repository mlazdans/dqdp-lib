<?php

namespace dqdp;

abstract class API {
	static $URL;
	static $KEY;
	static $SID;

	abstract static function set_host($host);

	static function decode_response($data){
		return json_decode($data);
	}

	static function post($url, Array $data = []){
		if(($ch = curl_init()) === false){
			return false;
		}

		if(self::$SID && !isset($data['SID'])){
			$data['SID'] = self::$SID;
		}

		curl_setopt($ch, CURLOPT_URL, self::$URL.$url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$HTML = curl_exec($ch);
		curl_close($ch);

		return self::decode_response($HTML);
	}

	static function set_sid($SID){
		self::$SID = $SID;
	}

	static function set_key($KEY){
		self::$SID = $KEY;
	}
}
