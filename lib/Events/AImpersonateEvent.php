<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
