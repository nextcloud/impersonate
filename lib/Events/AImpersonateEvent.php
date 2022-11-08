<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022 SnappyMail
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 * @author the-djmaze
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Impersonate\Events;

use OCP\EventDispatcher\Event;
use OCP\IUser;

abstract class AImpersonateEvent extends Event {
	private IUser $impersonator;

	private IUser $impersonatee;

	public function __construct(IUser $impersonator, IUser $impersonatee) {
		parent::__construct();
		$this->impersonator = $impersonator;
		$this->impersonatee = $impersonatee;
	}

	public function getImpersonator(): IUser {
		return $this->impersonator;
	}

	public function getImpersonatedUser(): IUser {
		return $this->impersonatee;
	}
}
