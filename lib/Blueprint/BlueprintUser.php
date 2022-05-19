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

class BlueprintUser {
	public string $id;
	/** @var string[] */
	public array $groups;
	/** @var string[] */
	public array $files;

	public function __construct(string $id, array $groups, array $files) {
		$this->id = $id;
		$this->groups = $groups;
		$this->files = $files;
	}

	public static function fromArray(array $user): BlueprintUser {
		InvalidBlueprintException::assertField($user, 'id', 'string', 'user');
		InvalidBlueprintException::assertOptionalField($user, 'groups', 'string[]', 'user');
		InvalidBlueprintException::assertOptionalField($user, 'files', 'string[]', 'user');

		return new BlueprintUser($user['id'], $user['groups'] ?: [], $user['files'] ?: []);
	}
}
