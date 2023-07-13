<?php

namespace W7\Sdk\OpenCloud\Util;

class Downloader {
	static function downloadChunk($url, $offset, $size, $headers = array(), $timeout = 10) {
		$urlInfo = self::parseUrl($url);
		if (!$urlInfo['host']) {
			throw new \Exception('Url is Invalid');
		}

		// default header
		$def_headers = array(
			'Accept'          => '*/*',
			'User-Agent'      => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)',
			'Accept-Encoding' => 'gzip, deflate',
			'Host'            => $urlInfo['host'],
			'Connection'      => 'Close',
			'Accept-Language' => 'zh-cn',
		);
		// merge heade
		$headers = array_merge($def_headers, $headers);

		return self::downloadContent($urlInfo['host'], $urlInfo['port'], $urlInfo['request'], $offset, $size, 10240, $headers, $timeout);
	}

	/**
	 * parse url
	 *
	 * @param $url
	 * @return bool|mixed
	 */
	protected static function parseUrl($url) {
		$urlInfo = parse_url($url);
		if (!$urlInfo['host']) {
			return false;
		}

		$urlInfo['port']    = $urlInfo['port'] ?: 80;
		$urlInfo['query'] = $urlInfo['query'] ?: '';
		$urlInfo['request'] = $urlInfo['path'] . ($urlInfo['query'] ? ('?' . $urlInfo['query']) : '');
		return $urlInfo;
	}

	/**
	 * download content by chunk
	 *
	 * @param $host
	 * @param $port
	 * @param $path
	 * @param $offset
	 * @param $size
	 * @param $speed
	 * @param $headers
	 * @param $timeout
	 */
	protected static function downloadContent($host, $port, $path, $offset, $size, $speed, &$headers, $timeout) {
		$request = self::buildHeader('GET', $path, $headers, $offset, $size);
		$fsocket = @fsockopen($host, $port, $errno, $errstr, $timeout);
		stream_set_blocking($fsocket, TRUE);
		stream_set_timeout($fsocket, $timeout);
		fwrite($fsocket, $request);
		$status = stream_get_meta_data($fsocket);
		if ($status['timed_out']) {
			throw new \Exception('Socket Connect Timeout');
		}
		$isHeaderEnd = 0;
		$downloadTotalSize    = 0;
		$content = '';
		while (!feof($fsocket)) {
			if (!$isHeaderEnd) {
				$line = @fgets($fsocket);
				if (in_array($line, array("\n", "\r\n"))) {
					$isHeaderEnd = 1;
				}
				continue;
			}
			$resp        = fread($fsocket, $speed);
			$lownloadLength = strlen($resp);
			$downloadTotalSize += $lownloadLength;
			if ($resp === false && $size < $downloadTotalSize) {
				fclose($fsocket);
				throw new \Exception('Socket I/O Error Or File Was Changed');
			}
			$content .= $resp;
			// check file end
			if ($size == $downloadTotalSize) {
				break;
			}
		}
		fclose($fsocket);

		return $content;

	}

	/**
	 * build header for socket
	 *
	 * @param     $action
	 * @param     $path
	 * @param     $headers
	 * @param int $offset
	 * @return string
	 */
	protected static function buildHeader($action, $path, &$headers, $offset = -1, $size = -1) {
		$out = $action . " {$path} HTTP/1.0\r\n";
		foreach ($headers as $hkey => $hval) {
			$out .= $hkey . ': ' . $hval . "\r\n";
		}
		if ($offset > -1) {
			$end = $offset + $size;
			$out .= "Accept-Ranges: bytes\r\n";
			$out .= "Range: bytes={$offset}-{$end}\r\n";
		}
		$out .= "\r\n";

		return $out;
	}
}
