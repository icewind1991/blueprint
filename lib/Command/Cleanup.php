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
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cleanup extends Base {
	private IConfig $config;
	private IUserManager $userManager;
	private IGroupManager $groupManager;
	private IRootFolder $rootFolder;

	public function __construct(IConfig $config, IUserManager $userManager, IRootFolder $rootFolder, IGroupManager $groupManager) {
		parent::__construct();
		$this->config = $config;
		$this->userManager = $userManager;
		$this->rootFolder = $rootFolder;
		$this->groupManager = $groupManager;
	}

	protected function configure() {
		$this
			->setName('blueprint:clean')
			->setDescription('Remove all users except for the admin and remove all user files');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (!$this->config->getSystemValueBool('blueprint', false)) {
			$output->writeln("Blueprint mode not enabled for instance");
			return 1;
		}

		/** @var IUser[] $usersToDelete */
		$usersToDelete = [];

		$this->userManager->callForAllUsers(function(IUser $user) use (&$usersToDelete) {
			if ($this->groupManager->isAdmin($user->getUID())) {
				$userFolder = $this->rootFolder->getUserFolder($user->getUID());
				foreach ($userFolder->getDirectoryListing() as $child) {
					$child->delete();
				}

				$userFolder->newFolder('files');
			} else {
				$usersToDelete[] = $user;
			}
		});

		foreach ($usersToDelete as $user) {
			$user->delete();
		}

		return 0;
	}
}
