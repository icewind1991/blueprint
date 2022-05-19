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

namespace OCA\Blueprint\Exception;

class InvalidBlueprintException extends \Exception {
	public static function assertField(array $data, string $key, string $type, string $parent) {
		if (!isset($data[$key])) {
			throw new InvalidBlueprintException("$parent doesn't contain required field $key: " . json_encode($data));
		}
		self::assertOptionalField($data, $key, $type, $parent);
	}

	public static function assertOptionalField(array $data, string $key, string $type, string $parent) {
		if (isset($data[$key])) {
			$actualType = gettype($data[$key]);
			if (substr($type, -2) === '[]') {
				if (!is_array($data[$key])) {
					throw new InvalidBlueprintException("$key field in $parent is a $actualType instead of an array: " . json_encode($data));
				}
				$type = substr($type, 0, -2);
				foreach ($data[$key] as $item) {
					$actualType = gettype($item);
					if ($actualType !== $type) {
						throw new InvalidBlueprintException("array field $key in $parent contains a $actualType instead of only ${type}s: " . json_encode($data));
					}
				}

			} else {
				$actualType = gettype($data[$key]);
				if ($actualType !== $type) {
					throw new InvalidBlueprintException("$key field in $parent is a $actualType instead of a $type: " . json_encode($data));
				}
			}
		}
	}
}
