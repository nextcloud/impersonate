<?php

/**
 * SPDX-FileCopyrightText: 2026 Framasoft
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Impersonate\Service;

use DateTime;
use OCA\Impersonate\AppInfo\Application;
use OCP\Activity\IManager as ActivityManager;
use OCP\Config\IUserConfig;
use OCP\IDateTimeFormatter;
use OCP\IUser;
use OCP\L10N\IFactory;
use OCP\Mail\IMailer;
use OCP\Notification\IManager as NotificationManager;

class NotifierService {
	public function __construct(
		private IFactory $languageFactory,
		private IUserConfig $userConfig,
		private NotificationManager $notificationManager,
		private IMailer $mailer,
		private ActivityManager $activityManager,
		private IDateTimeFormatter $dateTimeFormatter,
	) {
	}

	/**
	 * @param IUser $user the user to notify
	 * @param int $notificationSetting
	 * @param IUser $impersonator the user who is impersonating
	 */
	public function notifyUser(IUser $user, int $notificationSetting, IUser $impersonator): void {
		if ($notificationSetting === ConfigService::NOTIFICATION_NONE) {
			return;
		}
		$now = new DateTime();

		if ($notificationSetting & ConfigService::NOTIFICATION_PUSH) {
			$this->notifyPush($user, $impersonator, $now);
		}

		if (($notificationSetting & ConfigService::NOTIFICATION_MAIL) && ($user->getEMailAddress() !== null)) {
			$this->notifyMail($user, $impersonator, $now);
		}

		if ($notificationSetting & ConfigService::NOTIFICATION_ACTIVITY) {
			$this->notifyActivity($user, $impersonator, $now);
		}
	}

	/**
	 * Notify the user via push notification
	 * @param IUser $user the user to notify
	 * @param IUser $impersonator the user who is impersonating
	 * @param DateTime $when the time of the impersonation
	 * @return void
	 */
	protected function notifyPush(IUser $user, IUser $impersonator, DateTime $when): void {
		$notification = $this->notificationManager->createNotification();
		$notification->setUser($user->getUID())
			->setApp(Application::APP_ID)
			->setDateTime($when)
			->setSubject('impersonate_login', [
				'actor' => $impersonator->getDisplayName(),
			])
			->setObject('impersonate_login', $impersonator->getUID());
		$this->notificationManager->notify($notification);
	}

	/**
	 * Notify the user via email
	 * @param IUser $user the user to notify
	 * @param IUser $impersonator the user who is impersonating
	 * @param DateTime $when the time of the impersonation
	 * @return void
	 */
	protected function notifyMail(IUser $user, IUser $impersonator, DateTime $when): void {
		$template = $this->mailer->createEMailTemplate('impersonate.ImpersonateLogin', [
			'actor' => $impersonator->getDisplayName(),
		]);

		$language = $this->languageFactory->getUserLanguage($user);
		$l = $this->languageFactory->get(Application::APP_ID, $language, $this->userConfig->getValueString($user->getUID(), 'core', 'locale'));
		$timezone = $this->userConfig->getValueString($user->getUID(), 'core', 'timezone') ?? date_default_timezone_get();
		$dtz = new \DateTimeZone($timezone);
		$formattedDate = $this->dateTimeFormatter->formatDate($when, 'short', $dtz, $l);
		$formattedTime = $this->dateTimeFormatter->formatTime($when, 'short', $dtz, $l);

		$template->setSubject($l->t('An administrator accessed your account'));
		$template->addHeader();
		$template->addHeading($l->t('An administrator (\'%s\') accessed your account on %s at %s.', [$impersonator->getDisplayName(), $formattedDate, $formattedTime]));
		$template->addBodyText($l->t('This action may be necessary as part of platform maintenance. Please contact this administrator if you find this action suspicious or if you would like more information about it.'));
		$template->addFooter();

		$message = $this->mailer->createMessage();
		$message->setTo([$user->getEMailAddress() => $user->getDisplayName()]);
		$message->useTemplate($template);
		$this->mailer->send($message);
	}

	/**
	 * Keep track of impersonation in the activity app
	 * @param IUser $user the user to notify
	 * @param IUser $impersonator the user who is impersonating
	 * @param DateTime $when the time of the impersonation
	 * @return void
	 */
	protected function notifyActivity(IUser $user, IUser $impersonator, DateTime $when): void {

		$activity = $this->activityManager->generateEvent();
		$activity->setApp(Application::APP_ID)
			->setType('impersonate')
			->setTimestamp($when->getTimestamp())
			->setAuthor($impersonator->getUID())
			->setAffectedUser($user->getUID())
			->setSubject('impersonate_login', [
				'actor' => $impersonator->getUID(),
			]);
		$this->activityManager->publish($activity);
	}
}
