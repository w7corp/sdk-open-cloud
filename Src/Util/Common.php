<?php

/**
 * WeEngine Cloud SDK System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\Cloud\Util;

class Common {
	/**
	 * 获取序列化字符的反序列化结果
	 * @param string $value
	 * @return mixed
	 */
	public static function unserialize($value) {
		if (empty($value)) {
			return array();
		}
		if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
			$result = unserialize($value, array('allowed_classes' => false));
		} else {
			if (preg_match('/[oc]:[^:]*\d+:/i', $value)) {
				return array();
			}
			$result = unserialize($value);
		}
		return $result;
	}

	public static function is_error($data) {
		if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] == 0)) {
			return false;
		} else {
			return true;
		}
	}

	public static function error($errno, $message = '') {
		return array(
			'errno' => $errno,
			'message' => $message,
		);
	}

	public static function writeAble($dir) {
		$writeable = 0;
		if (!is_dir($dir)) {
			@mkdir($dir, 0777);
		}
		if (is_dir($dir)) {
			if ($fp = fopen("$dir/test.txt", 'w')) {
				fclose($fp);
				unlink("$dir/test.txt");
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		}
		return $writeable;
	}

	public static function random($length, $numeric = false) {
		$seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
		if ($numeric) {
			$hash = '';
		} else {
			$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
			$length--;
		}
		$max = strlen($seed) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $seed[mt_rand(0, $max)];
		}
		return $hash;
	}

	public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key != '' ? $key : W7_CLOUD_SDK_AUTHKEY);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

		$cryptkey = $keya . md5($keya . $keyc);
		$key_length = strlen($cryptkey);

		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);

		$rndkey = array();
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}

		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}

		if ($operation == 'DECODE') {
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc . str_replace('=', '', base64_encode($result));
		}
	}

	public static function aesEncode($message, $encodingaeskey = '', $appid = '') {
		$key = base64_decode($encodingaeskey . '=');
		$text = self::random(16) . pack('N', strlen($message)) . $message . $appid;

		$iv = substr($key, 0, 16);

		$block_size = 32;
		$text_length = strlen($text);
		//计算需要填充的位数
		$amount_to_pad = $block_size - ($text_length % $block_size);
		if ($amount_to_pad == 0) {
			$amount_to_pad = $block_size;
		}
		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = '';
		for ($index = 0; $index < $amount_to_pad; $index++) {
			$tmp .= $pad_chr;
		}
		$text = $text . $tmp;
		$encrypted = openssl_encrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
		//加密后的消息
		$encrypt_msg = base64_encode($encrypted);
		return $encrypt_msg;
	}
}
