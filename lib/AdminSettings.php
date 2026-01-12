<?php

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate;

use OCA\Impersonate\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IAppConfig;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings {

	public function __construct(
		protected IAppConfig $config,
	) {
	}

	public function getForm(): TemplateResponse {
		$authorized = $this->config->getValueString(Application::APP_ID, 'authorized', '["admin"]');
		return new TemplateResponse(Application::APP_ID, 'admin_settings', [
			'authorized' => implode('|', json_decode($authorized, true)),
		], 'blank');
	}

	public function getSection(): string {
		return 'additional';
	}

	public function getPriority(): int {
		return 50;
	}
}
