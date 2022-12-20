<?php declare(strict_types = 1);

namespace dqdp;

abstract class API {
	static string $URL;
	static string $KEY;
	static string $SID;
	static string $RAW_RESPONSE_DATA;

	abstract static function set_host(string $host): void;

	static function decode_response(string $data): mixed {
		return json_decode(self::$RAW_RESPONSE_DATA = $data);
	}

	static function post(string $url, array $data = []): mixed {
		if(($ch = curl_init()) === false){
			return false;
		}

		if(static_prop_is_initialized(self::class, 'SID')){
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

	# TODO: remove
	static function set_sid(string $SID): void {
		self::$SID = $SID;
	}

	static function set_key(string $KEY): void {
		self::$SID = $KEY;
	}
}
