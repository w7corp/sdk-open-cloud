<?php

namespace W7\Sdk\OpenCloud\Service;

use PhpZip\Constants\ZipCompressionMethod;
use PhpZip\Model\EndOfCentralDirectory;
use PhpZip\Model\ZipEntry;
use W7\Sdk\OpenCloud\Request\ServiceRequest;
use W7\Sdk\OpenCloud\Util\Downloader;
use W7\Sdk\OpenCloud\Util\InstanceTraiter;
use W7\Sdk\OpenCloud\Util\ZipReader;

class Attach extends ServiceRequest {
	use InstanceTraiter;

	public function getApiMap() {
		// TODO: Implement getApiMap() method.
	}

	protected function parseECDInfoByPath($path, $count, $offset, $size, $zip64) {
		$endCD = new EndOfCentralDirectory(
			$count,
			0,
			$size,
			$zip64
		);

		$fileMap = [];
		$fileHandler = fopen($path, 'rb');
		$ecdPos = (new ZipReader($fileHandler))->readCentralDirectory($endCD);

		/**
		 * @var ZipEntry $before
		 */
		$before = '';
		foreach ($ecdPos as $fileName => $entry) {
			$fileMap[$fileName] = [
				'compress' => [
					'method' => $entry->getCompressionMethod(),
					'level' => $entry->getCompressionLevel(),
				],
				'size' => $entry->getCompressedSize(),
				'origin_size' => $entry->getUncompressedSize(),
				'offset' => $entry->getLocalHeaderOffset(),
			];

			if (!empty($before)) {
				$fileMap[$before->getName()]['offset'] = $entry->getLocalHeaderOffset() - $fileMap[$before->getName()]['size'];
				// 第三标志位为1时，说明有描述信息，需要再减去固定的16位
				if ($before->getGeneralPurposeBitFlags() == 8) {
					$fileMap[$before->getName()]['offset'] -= 16;
				}
			}
			$before = $entry;
		}

		$fileMap[$before->getName()]['offset'] = $offset - $fileMap[$before->getName()]['size'];
		// 第三标志位为1时，说明有描述信息，需要再减去固定的16位
		if ($before->getGeneralPurposeBitFlags() == 8) {
			$fileMap[$before->getName()]['offset'] -= 16;
		}
		if (\is_resource($fileHandler)) {
			// 在 Reader 类中，可能已经半闭资源
			fclose($fileHandler);
		}

		return $fileMap;
	}

	protected function parseECDInfoByContent($content, $count, $offset, $size, $zip64) {
		$tmpFile = sys_get_temp_dir() . '/we7-sdk-attach-' . microtime(true) . '.data';
		file_put_contents($tmpFile, $content);

		try {
			return $this->parseECDInfoByPath($tmpFile, $count, $offset, $size, $zip64);
		} finally {
			@unlink($tmpFile);
		}
	}

	protected function unZipContent($content, $compressionMethod) {
		switch ($compressionMethod) {
			case ZipCompressionMethod::STORED:
				return $content;
			case ZipCompressionMethod::DEFLATED:
				return gzinflate($content);
			case ZipCompressionMethod::BZIP2:
				return bzdecompress($content);
			default:
				throw new \RuntimeException(
					sprintf(
						'compression method %d is not supported',
						$compressionMethod
					)
				);
		}
	}

	public function getFileContentFromRemoteZip($zipUrl, $fileName, $zipDirContentOffset, $zipDirContentSize, $zipFilesCount, $zip64 = 0) {
		$cacheKey = 'sdk:open_cloud:attach:dir:cache:' . md5($zipUrl);
		$dirInfo = $this->cache ? $this->cache->load($cacheKey) : [];

		if (!$dirInfo) {
			$dirContent = Downloader::downloadChunk($zipUrl, $zipDirContentOffset, $zipDirContentSize);
			if (!$dirContent) {
				throw new \Exception('remote zip file dir pos not exists');
			}

			$dirInfo = $this->parseECDInfoByContent(
				$dirContent,
				$zipFilesCount,
				$zipDirContentOffset,
				$zipDirContentSize,
				$zip64
			);
			$dirInfo && $this->cache && $this->cache->save($cacheKey, $dirInfo);
		}

		if (empty($dirInfo[$fileName])) {
			throw new \Exception("file " . $fileName . ' not exists');
		}


		return $this->unZipContent(Downloader::downloadChunk($zipUrl, $dirInfo[$fileName]['offset'], $dirInfo[$fileName]['size']), $dirInfo[$fileName]['compress']['method']);
	}

	public function downloadFileFromRemoteZip($zipUrl, $fileName, $savePath, $zipDirContentOffset, $zipDirContentSize, $zipFilesCount, $zip64 = 0) {
		$content = $this->getFileContentFromRemoteZip($zipUrl, $fileName, $zipDirContentOffset, $zipDirContentSize, $zipFilesCount, $zip64);

		file_put_contents($savePath, $content);
	}
}