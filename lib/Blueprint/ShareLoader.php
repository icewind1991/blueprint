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
use OCP\Files\NotFoundException;
use OCP\Share\IManager;
use OCP\Share\IShare;

class ShareLoader {
	private IManager $shareManager;
	/** @var IShare[] */
	private array $existingShares;
	private IRootFolder $rootFolder;
	private FileLoader $fileLoader;

	public function __construct(IManager $shareManager, IRootFolder $rootFolder, FileLoader $fileLoader) {
		$this->shareManager = $shareManager;
		$this->rootFolder = $rootFolder;
		$this->fileLoader = $fileLoader;

		$shares = $this->shareManager->getAllShares();
		if (is_array($shares)) {
			$this->existingShares = $shares;
		} else {
			$this->existingShares = iterator_to_array($shares);
		}
	}

	public function applyShare(BlueprintShare $blueprintShare) {
		if ($this->shareExists($blueprintShare)) {
			return;
		}
		$userFolder = $this->rootFolder->getUserFolder($blueprintShare->from);
		try {
			$source = $userFolder->get($blueprintShare->file);
		} catch (NotFoundException $e) {
			throw new NotFoundException("Source for share not found: " . $blueprintShare->file, 0);
		}

		if (strpos($blueprintShare->target, '/')) {
			$targetFolder = $this->rootFolder->getUserFolder($blueprintShare->to);
			$this->fileLoader->createParents($targetFolder, $blueprintShare->target);
		}

		$share = $this->shareManager->newShare();

		$share->setSharedBy($blueprintShare->from);
		$share->setSharedWith($blueprintShare->to);
		$share->setNode($source);
		$share->setShareType(IShare::TYPE_USER);
		$share->setPermissions($blueprintShare->permissions);

		$share = $this->shareManager->createShare($share);
		$share->setTarget($blueprintShare->target);
		$this->shareManager->moveShare($share, $blueprintShare->to);
	}

	private function shareExists(BlueprintShare $blueprintShare) {
		foreach ($this->existingShares as $existingShare) {
			if ($existingShare->getSharedBy() !== $blueprintShare->from) {
				continue;
			}
			if ($existingShare->getSharedWith() !== $blueprintShare->to) {
				continue;
			}
			if ($existingShare->getNode()->getPath() !== '/'. $blueprintShare->from . '/files' . $blueprintShare->file) {
				continue;
			}

			return true;
		}

		return false;
	}
}
