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

use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IGroupManager;
use OCP\IUserManager;

class Loader {
	private IUserManager $userManager;
	private IGroupManager $groupManager;
	private IRootFolder $rootFolder;

	public function __construct(IUserManager $userManager, IGroupManager $groupManager, IRootFolder $rootFolder) {
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->rootFolder = $rootFolder;
	}

	public function apply(Blueprint $blueprint) {
		foreach ($blueprint->users as $user) {
			$this->applyUser($user);
		}
	}

	private function applyUser(BlueprintUser $blueprintUser) {
		if (!$this->userManager->userExists($blueprintUser->id)) {
			$this->userManager->createUser($blueprintUser->id, $blueprintUser->id);
		}
		$user = $this->userManager->get($blueprintUser->id);

		foreach ($blueprintUser->groups as $group) {
			if (!$this->groupManager->groupExists($group)) {
				$this->groupManager->createGroup($group);
			}
			$group = $this->groupManager->get($group);
			if (!$group->inGroup($user)) {
				$group->addUser($user);
			}
		}

		$userFolder = $this->rootFolder->getUserFolder($user->getUID());
		foreach ($blueprintUser->files as $file) {
			$this->createFile($userFolder, $file);
		}
	}

	private function createFile(Folder $folder, string $file) {
		if ($folder->nodeExists($file)) {
			return;
		}

		$parts = explode('/', $file);
		$name = array_pop($parts);

		foreach ($parts as $part) {
			try {
				$node = $folder->get($part);
				if ($node instanceof Folder) {
					$folder = $node;
				} else {
					throw new \Exception("Tried creating a file inside another file");
				}
			} catch (NotFoundException $e) {
				$folder = $folder->newFolder($part);
			}
		}

		$folder->newFile($name, $this->getDummyContent($name));
	}

	/**
	 * @param string $name
	 * @return string|resource
	 */
	private function getDummyContent(string $name) {
		return "dummy content for $name";
	}
}
