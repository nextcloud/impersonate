<?php

namespace OCA\Impersonate\Events;

use OCP\EventDispatcher\Event;
use OCP\IUser;

class ImpersonateEvent extends Event {
	private IUser $oldUser;

	private IUser $newUser;

	public function __construct(IUser $oldUser, IUser $newUser) {
		parent::__construct();
		$this->oldUser = $oldUser;
		$this->newUser = $newUser;
	}

	public function getOldUser(): IUser {
		return $this->oldUser;
	}

	public function getNewUser(): IUser {
		return $this->newUser;
	}
}
