<?php

namespace OCA\Blueprint\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public function __construct(array $urlParams = []) {
		parent::__construct('blueprint', $urlParams);
	}
}
