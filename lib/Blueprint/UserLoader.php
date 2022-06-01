<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Robin Appelman <robin@icewind.nl>
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

namespace OCA\Blueprint\Blueprint;

use OCP\Files\IRootFolder;
use OCP\IGroupManager;
use OCP\IUserManager;

class UserLoader {
	private IUserManager $userManager;
	private IGroupManager $groupManager;
	private IRootFolder $rootFolder;
	private FileLoader $fileLoader;

	public function __construct(
		IUserManager $userManager,
		IGroupManager $groupManager,
		IRootFolder $rootFolder,
		FileLoader $fileLoader
	) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->rootFolder = $rootFolder;
		$this->fileLoader = $fileLoader;
	}

	public function applyUser(BlueprintUser $blueprintUser) {
		if (!$this->userManager->userExists($blueprintUser->id)) {
			$this->userManager->createUser($blueprintUser->id, $blueprintUser->id);
		}
		$user = $this->userManager->get($blueprintUser->id);
		if (!$user) {
			throw new \Exception("Failed to create user");
		}

		foreach ($blueprintUser->groups as $groupId) {
			$group = $this->groupManager->get($groupId);
			if (!$group) {
				$group = $this->groupManager->createGroup($groupId);

				if (!$group) {
					throw new \Exception("Failed to create group");
				}
			}
			if (!$group->inGroup($user)) {
				$group->addUser($user);
			}
		}

		$userFolder = $this->rootFolder->getUserFolder($user->getUID());
		foreach ($blueprintUser->files as $file) {
			$this->fileLoader->createFile($userFolder, $file);
		}
	}
}
