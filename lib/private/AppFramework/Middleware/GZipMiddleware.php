<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\AppFramework\Middleware;

use OCP\AppFramework\Http\ICallbackResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\IRequest;

class GZipMiddleware extends Middleware {

	/** @var bool */
	private $useGZip;

	/** @var IRequest */
	private $request;

	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	public function beforeController($controller, $methodName) {
		$header = $this->request->getHeader('Accept-Encoding');

		if (strpos($header, 'gzip') !== false) {
			$this->useGZip = true;
		}
	}

	public function afterException($controller, $methodName, \Exception $exception) {
		$this->useGZip = false;
	}


	public function afterController($controller, $methodName, Response $response) {
		if ($response instanceof ICallbackResponse) {
			$this->useGZip = false;
		}

		if ($this->useGZip) {
			$response->addHeader('Content-Encoding', 'gzip');
		}

		return $response;
	}

	public function beforeOutput($controller, $methodName, $output) {
		if (!$this->useGZip) {
			return $output;
		}

		return gzencode($output);
	}


}
