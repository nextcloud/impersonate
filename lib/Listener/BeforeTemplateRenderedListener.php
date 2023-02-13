<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
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

namespace OCA\Impersonate\Listener;

use OCA\Impersonate\AppInfo\Application;
use OCA\Settings\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\Util;

class BeforeTemplateRenderedListener implements IEventListener {
	/** @var IConfig */
	private $config;
	/** @var IGroupManager */
	private $groupManager;
	/** @var IUserSession */
	private $userSession;

	public function __construct(IConfig $config, IGroupManager $groupManager, IUserSession $userSession) {
		$this->config = $config;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent) {
			return;
		}

		$authorized = json_decode($this->config->getAppValue(Application::APP_ID, 'authorized', '["admin"]'));

		if (!empty($authorized)) {
			$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
			if (!array_intersect($userGroups, $authorized)) {
				return;
			}
		}
		Util::addScript(Application::APP_ID, 'impersonate');
	}
}
