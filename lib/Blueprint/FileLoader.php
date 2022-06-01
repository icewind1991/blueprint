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

use OCA\Blueprint\Exception\InvalidBlueprintException;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;

class FileLoader {
	/**
	 * Recursively create all parent folders for $path
	 *
	 * @param Folder $folder
	 * @param string $path
	 * @return Folder
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function createParents(Folder $folder, string $path): Folder {
		$parts = explode('/', $path);
		array_pop($parts);

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

		return $folder;
	}

	public function createFile(Folder $folder, string $file) {
		foreach ($this->expandRange($file) as $item) {
			$this->createSingleFile($folder, $item);
		}
	}

	private function createSingleFile(Folder $folder, string $file) {
		if ($folder->nodeExists($file)) {
			return;
		}

		$folder = $this->createParents($folder, $file);
		$name = basename($file);

		if (strpos($name, '.') === false) {
			$folder->newFolder($name);
		} else {
			$folder->newFile($name, $this->getDummyContent($name));
		}
	}

	/**
	 * @param string $name
	 * @return string|resource
	 */
	private function getDummyContent(string $name) {
		$ext = substr($name, -3);
		if ($ext === 'png') {
			return fopen(__DIR__ . '/../../files/nc.png', 'r');
		} elseif ($ext === 'jpg') {
			return fopen(__DIR__ . '/../../files/nc.jpg', 'r');
		}
		return "dummy content for $name";
	}

	/**
	 * @param string $range
	 * @return string[]
	 * @throws InvalidBlueprintException
	 */
	public function expandRange(string $range): array {
		preg_match("/\[(\d+)..(\d+)\]/", $range, $matches, PREG_OFFSET_CAPTURE);
		if (!$matches) {
			return [$range];
		}

		$before = substr($range, 0, $matches[0][1]);
		$after = substr($range, $matches[0][1] + strlen($matches[0][0]));
		$from = (int)$matches[1][0];
		$to = (int)$matches[2][0];

		if ($to <= $from) {
			throw new InvalidBlueprintException("Invalid range in blueprint");
		}

		$files = [];
		for ($i = $from; $i < $to; $i++) {
			$applied = $before . $i . $after;
			$files = array_merge($files, $this->expandRange($applied));
		}
		return $files;
	}
}
