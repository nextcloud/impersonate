<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Listener;

use OCA\Impersonate\AppInfo\Application;
use OCA\Settings\Events\BeforeTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IAppConfig;
use OCP\IGroupManager;
use OCP\IUserSession;
use OCP\Util;

/**
 * @template-implements IEventListener<Event|BeforeTemplateRenderedEvent>
 */
readonly class BeforeTemplateRenderedListener implements IEventListener {

	public function __construct(
		private IAppConfig $config,
		private IGroupManager $groupManager,
		private IUserSession $userSession,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeTemplateRenderedEvent) {
			return;
		}

		$authorized = json_decode($this->config->getValueString(Application::APP_ID, 'authorized', '["admin"]'));

		if (!empty($authorized)) {
			$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
			if (!array_intersect($userGroups, $authorized)) {
				return;
			}
		}
		Util::addScript(Application::APP_ID, 'impersonate');
	}
}
