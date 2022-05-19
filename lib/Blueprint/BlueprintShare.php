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
use OCP\Constants;

class BlueprintShare {
	public string $from;
	public string $to;
	public string $file;
	public string $target;
	public int $permissions;

	public function __construct(string $from, string $to, string $file, string $target, int $permissions) {
		$this->from = $from;
		$this->to = $to;
		$this->file = '/' . ltrim($file, '/');
		$this->target = '/' . ltrim($target, '/');
		$this->permissions = $permissions;
	}

	public static function fromArray(array $data): BlueprintShare {
		InvalidBlueprintException::assertField($data, 'from', 'string', 'share');
		InvalidBlueprintException::assertField($data, 'to', 'string', 'share');
		InvalidBlueprintException::assertField($data, 'file', 'string', 'share');
		InvalidBlueprintException::assertOptionalField($data, 'target', 'string', 'share');
		InvalidBlueprintException::assertOptionalField($data, 'permissions', 'integer', 'share');

		$isFile = strpos($data['file'], '.') > 0;
		if (isset($data['permissions'])) {
			$permissions = $data['permissions'];
		} else {
			$permissions = Constants::PERMISSION_ALL;
		}
		if ($isFile) {
			$permissions = $permissions & (Constants::PERMISSION_ALL - Constants::PERMISSION_CREATE);
		}
		$permissions = $permissions & (Constants::PERMISSION_ALL - Constants::PERMISSION_DELETE);

		return new BlueprintShare(
			$data['from'],
			$data['to'],
			$data['file'],
			$data['target'] ?? basename($data['file']),
			$permissions
		);
	}
}
