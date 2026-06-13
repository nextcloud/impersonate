<?php

/**
 * SPDX-FileCopyrightText: 2026 Framasoft
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Notification;

use OCA\Impersonate\AppInfo\Application;
use OCP\IDateTimeFormatter;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

class Notifier implements INotifier {

	public function __construct(
		private IFactory $l10nFactory,
		private IDateTimeFormatter $dateTimeFormatter,
		private IURLGenerator $urlGenerator,
	) {
	}

	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 *
	 * @return string
	 */
	public function getID(): string {
		return Application::APP_ID;
	}

	/**
	 * Human readable name describing the notifier
	 *
	 * @return string
	 * @throws UnknownNotificationException
	 */
	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('Impersonate');
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new UnknownNotificationException();
		}

		$l = $this->l10nFactory->get(Application::APP_ID, $languageCode);

		$parameters = $notification->getSubjectParameters();
		$impersonator_name = $parameters['actor'];
		$formattedDate = $this->dateTimeFormatter->formatDate($notification->getDateTime(), 'short');
		$formattedTime = $this->dateTimeFormatter->formatTime($notification->getDateTime(), 'short');

		$notification->setIcon($this->urlGenerator->getAbsoluteURL($this->urlGenerator->imagePath(Application::APP_ID, 'actions/user-admin.svg')));

		$notification->setParsedSubject(
			$l->t('An administrator (\'%s\') accessed your account on %s at %s.', [$impersonator_name, $formattedDate, $formattedTime])
		);
		return $notification;
	}
}
