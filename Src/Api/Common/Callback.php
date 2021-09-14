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

namespace W7\Sdk\OpenCloud\Api\Common;

use W7\Sdk\OpenCloud\Cache\CacheInterface;
use W7\Sdk\OpenCloud\Exception\ApiErrorException;
use W7\Sdk\OpenCloud\Util\Common;

class Callback
{
	private $siteInfoHandler;
	private $shippingFileHandler;

	private $postInputStream = '';

	/**
	 * callback地址暴露给云服务时，服务回推数据时的GET参数名
	 * @var string
	 */
	private $actionParamName = 'HTTP_API_ACTION';

	/**
	 * 接收数据时临时存放的目录，目前只支持文件缓存，期待以后增加redis
	 * @var CacheInterface
	 */
	private $cache;

	private $siteToken;

	public function __construct()
	{
		$this->registerPostInputStream(file_get_contents('php://input'));
	}

	/**
	 * 接口推送站点信息时
	 * @param callable $handler
	 */
	public function registerSiteInfoHandler(callable $handler)
	{
		$this->siteInfoHandler = $handler;
	}

	/**
	 * 注册推送文件处理器
	 * @param callable $handler
	 */
	public function registershippingFileHandler(callable $handler)
	{
		$this->shippingFileHandler = $handler;
	}

	/**
	 * 注册站点TOKEN，用于校验数据
	 * @param string $token
	 */
	public function registerSiteToken($token)
	{
		$this->siteToken = $token;
	}

	/**
	 * 注册 php://input 数据
	 * 一般不需要调用，构造函数内会自动获取
	 * @param string $postInput
	 * @return bool
	 */
	public function registerPostInputStream($postInput)
	{
		if (!empty($this->postInputStream)) {
			return true;
		}
		if (empty($postInput)) {
			return true;
		}

		$this->postInputStream = $postInput;
	}

	/**
	 * 注册推送数据临时存放的目录
	 * @param CacheInterface $cache
	 */
	public function registerCache(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	public function dispatch()
	{
		$action      = $_SERVER[$this->actionParamName];
		$allowAction = [
			'auth',
			'build',
			'init',
			'schema',
			'download',
			'module.query',
			'module.bought',
			'module.info',
			'module.build',
			'theme.query',
			'theme.info',
			'theme.build',
			'application.build',
			'test',
			'touch'
		];
		if (!in_array($action, $allowAction)) {
			throw new \RuntimeException('操作不允许');
		}

		$methodName = 'do' . str_replace('.', '', $action);
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		}

		return $this->doShippingData($action);
	}

	private function doAuth()
	{
		$siteInfo = json_decode(base64_decode($this->postInputStream), true);

		$currentHost = htmlspecialchars((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
		if ($siteInfo['url'] != 'http://' . $currentHost && $siteInfo['url'] != 'https://' . $currentHost) {
			//throw new \RuntimeException('站点信息与当前域名不符');
		}

		if (is_callable($this->siteInfoHandler)) {
			call_user_func_array($this->siteInfoHandler, [
				$siteInfo
			]);
		}

		return 'success';
	}

	public function doTouch()
	{
		echo 'success';
		exit;
	}

	private function doDownload()
	{
		$result = Common::unserialize($this->postInputStream);
		$gz     = function_exists('gzcompress') && function_exists('gzuncompress');
		$file   = base64_decode($result['file']);
		if ($gz) {
			$file = gzuncompress($file);
		}

		$transtoken = $this->cache->load('trans.token', false);
		$transtoken = Common::authcode($transtoken, 'DECODE');
		$string     = (md5($file) . $result['path'] . $transtoken);

		if (md5($string) === $result['sign']) {
			if (is_callable($this->shippingFileHandler)) {
				call_user_func_array($this->shippingFileHandler, [
					[
						'path' => $result['path'],
						'file' => $file,
					]
				]);
			}
		}

		return 'success';
	}

	/**
	 * 接收shipping推送来的数据
	 */
	private function doShippingData($action)
	{
		if (empty($this->siteToken)) {
			throw new ApiErrorException('请使用registerSiteToken方法注册站点token');
		}

		if (empty($this->cache)) {
			throw new ApiErrorException('请使用registerCache方法注册缓存处理器');
		}

		$data = $this->postDecode($this->postInputStream);

		$secret        = Common::random(32);
		$ret           = [];
		$ret['data']   = $data;
		$ret['secret'] = $secret;

		$this->cache->save($action, serialize($ret));

		return $secret;
	}

	protected function postDecode($post)
	{
		if (empty($post)) {
			return false;
		}
		$data   = base64_decode($post);
		$ret    = Common::unserialize($data);
		$string = ($ret['data'] . $this->siteToken);
		if (md5($string) === $ret['sign']) {
			return $ret['data'];
		}
		return false;
	}
}
