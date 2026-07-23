<?php

/**
 * SPDX-FileCopyrightText: 2026 Framasoft
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Impersonate\Service;

use OCP\IConfig;

class ConfigService {
	private const SETTING_NOTIFICATION_KEY = 'impersonate.notifications';
	private const SETTING_NOTIFICATION_DEFAULT = [
		'user' => self::NOTIFICATION_NONE,
		'admin' => self::NOTIFICATION_NONE
	];

	// notification values (bit field)
	public const NOTIFICATION_NONE = 0;
	public const NOTIFICATION_PUSH = 1;
	public const NOTIFICATION_MAIL = 2;
	public const NOTIFICATION_ACTIVITY = 4;

	private ?array $notificationsConfig = null;

	public function __construct(
		private IConfig $config,
	) {
	}

	/**
	 * @return array{user: int, admin: int}
	 */
	private function getConfig(): array {
		if ($this->notificationsConfig !== null) {
			return $this->notificationsConfig;
		}

		$data = $this->config->getSystemValue(self::SETTING_NOTIFICATION_KEY, self::SETTING_NOTIFICATION_DEFAULT);
		if (!is_array($data)) {
			// disable notifications if incorrect or empty setting
			$data = [];
		}
		if (!isset($data['user'])) {
			$data['user'] = self::NOTIFICATION_NONE;
		}
		if (!isset($data['admin'])) {
			$data['admin'] = self::NOTIFICATION_NONE;
		}

		$this->notificationsConfig = $data;

		return $data;
	}

	/**
	 * @param bool $isAdmin
	 * @return int
	 */
	public function getUserNotificationSetting(bool $isAdmin = false): int {
		$config = $this->getConfig();
		return $config[$isAdmin ? 'admin' : 'user'] ?? self::NOTIFICATION_NONE;
	}
}
