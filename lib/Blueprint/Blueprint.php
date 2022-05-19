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

use Yosymfony\Toml\Toml;

class Blueprint {
	/** @var BlueprintUser[] */
	public array $users;
	/** @var BlueprintShare[] */
	public array $shares;

	public function __construct(array $users, array $shares) {
		$this->users = $users;
		$this->shares = $shares;
	}

	public static function fromArray(array $data): Blueprint {
		$users = array_map([BlueprintUser::class, 'fromArray'], $data['user'] ?: []);
		$shares = array_map([BlueprintShare::class, 'fromArray'], $data['share'] ?: []);

		return new Blueprint($users, $shares);
	}

	public static function parse(string $toml): Blueprint {
		return Blueprint::fromArray(Toml::parse($toml));
	}

	public static function fromFile(string $file): Blueprint {
		return Blueprint::fromArray(Toml::parseFile($file));
	}
}
