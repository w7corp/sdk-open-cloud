<?php

/**
 * WeEngine Sdk Core System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Sdk\OpenCloud\Request\Middleware\Service;

use GuzzleHttp\Command\Command;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use W7\Sdk\OpenCloud\Request\ServiceRequest;

class ServiceExtendMiddleware extends ServiceMiddlewareAbstract
{
	/**
	 * @var ServiceRequest
	 */
	protected $service;

	public function __construct(GuzzleClient $client, ServiceRequest $service)
	{
		parent::__construct($client);

		$this->service = $service;
	}

	public function __invoke(callable $handler)
	{
		return function (Command $command) use ($handler) {
			$extend = $this->serviceClient->getDescription()->getOperation($command->getName())->toArray()['extends'] ?? '';
			if ($extend && method_exists($this->service, $extend)) {
				return $handler($this->service->$extend($command));
			}
			return $handler($command);
		};
	}
}
