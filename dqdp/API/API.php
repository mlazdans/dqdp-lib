<?php declare(strict_types = 1);

namespace dqdp\API;

abstract class API {
	static string $URL;
	static SerializationMethod $serializationMethod = SerializationMethod::JSON;
	static string $RAW_RESPONSE_DATA = "";

	abstract static function set_host(string $host): void;

	static function decode_response(string $data): mixed {
		if(static::$serializationMethod == SerializationMethod::JSON){
			return json_decode(static::$RAW_RESPONSE_DATA = $data);
		} elseif(static::$serializationMethod == SerializationMethod::PHP){
			return unserialize(static::$RAW_RESPONSE_DATA = $data);
		}
	}

	static function post(string $url, array $data = []): mixed {
		if(($ch = curl_init()) === false){
			return false;
		}

		curl_setopt($ch, CURLOPT_URL, static::$URL.$url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$HTML = curl_exec($ch);
		curl_close($ch);

		return static::decode_response($HTML);
	}
}
