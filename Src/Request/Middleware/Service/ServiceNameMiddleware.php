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

class ServiceNameMiddleware extends ServiceMiddlewareAbstract
{
	const SERVICE_NAME_KEY = 'X-W7-OPEN-SERVICE-NAME';

	public function __invoke(callable $handler)
	{
		return function (Command $command) use ($handler) {
			$commandHttp = $command['@http'] ?? [];
			$commandHttp['headers'] = $commandHttp['headers'] ?? [];
			$commandHttp['headers'] = array_merge($commandHttp['headers'], [static::SERVICE_NAME_KEY => $command->getName()]);
			$command['@http'] = $commandHttp;

			return $handler($command);
		};
	}
}
