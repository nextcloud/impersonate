<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate;

use OCA\Impersonate\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IAppConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {

	public function __construct(
		protected IAppConfig $config,
		protected IInitialState $initialState,
	) {
	}

	public function getForm(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'impersonate-adminSettings');
		Util::addStyle(Application::APP_ID, 'impersonate-adminSettings');

		$authorized = $this->config->getValueString(Application::APP_ID, 'authorized', '["admin"]');
		$this->initialState->provideInitialState('authorized', json_decode($authorized, true));
		return new TemplateResponse(Application::APP_ID, 'admin_settings', [], 'blank');
	}

	public function getSection(): string {
		return 'additional';
	}

	public function getPriority(): int {
		return 50;
	}
}
