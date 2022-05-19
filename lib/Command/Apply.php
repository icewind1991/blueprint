<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2021 Robin Appelman <robin@icewind.nl>
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

namespace OCA\Blueprint\Command;

use OC\Core\Command\Base;
use OCA\Blueprint\Blueprint\Blueprint;
use OCA\Blueprint\Blueprint\Loader;
use OCP\IConfig;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Apply extends Base {
	private IConfig $config;
	private Loader $loader;

	public function __construct(IConfig $config, Loader $loader) {
		parent::__construct();
		$this->config = $config;
		$this->loader = $loader;
	}

	protected function configure() {
		$this
			->setName('blueprint:apply')
			->setDescription('Apply a blueprint to this instance')
			->addArgument('blueprint', InputArgument::REQUIRED, 'Blueprint to apply');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$this->config->getSystemValueBool('blueprint', false)) {
			$output->writeln("Blueprint mode not enabled for instance");
			return 1;
		}

		$file = $input->getArgument('blueprint');

		if (!file_exists($file)) {
			$output->writeln("Blueprint not found");
			return 1;
		}

		$blueprint = Blueprint::fromFile($file);
		$this->loader->apply($blueprint);

		return 0;
	}
}
