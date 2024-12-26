<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\AppInfo;

use OCA\Impersonate\Listener\BeforeTemplateRenderedListener;
use OCA\Settings\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\ISession;
use OCP\Util;

class Application extends App implements IBootstrap {
	public const APP_ID = 'impersonate';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {
		$session = $context->getServerContainer()->get(ISession::class);

		if ($session->get('oldUserId') !== null) {
			Util::addScript(self::APP_ID, 'impersonate_logout');
		}
	}
}
