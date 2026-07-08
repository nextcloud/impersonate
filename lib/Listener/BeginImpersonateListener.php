<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2026 Framasoft
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Listener;

use OCA\Impersonate\Events\BeginImpersonateEvent;
use OCA\Impersonate\Service\ConfigService;
use OCA\Impersonate\Service\NotifierService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IGroupManager;

/**
 * @template-implements IEventListener<Event|BeginImpersonateEvent>
 */
class BeginImpersonateListener implements IEventListener {

	public function __construct(
		private ConfigService $config,
		private IGroupManager $groupManager,
		private NotifierService $notifier,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeginImpersonateEvent) {
			return;
		}

		$impersonated = $event->getImpersonatedUser();
		$notificationSetting = $this->config->getUserNotificationSetting($this->groupManager->isAdmin($impersonated->getUID()));

		if ($notificationSetting === ConfigService::NOTIFICATION_NONE) {
			return;
		}

		$this->notifier->notifyUser($impersonated, $notificationSetting, $event->getImpersonator());
	}
}
