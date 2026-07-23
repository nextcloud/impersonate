<?php

/**
 * SPDX-FileCopyrightText: Framasoft
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Activity;

use OCA\Impersonate\AppInfo\Application;
use OCP\Activity\Exceptions\UnknownActivityException;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\Activity\IProvider;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;

class Provider implements IProvider {
	public function __construct(
		protected IFactory $languageFactory,
		protected IURLGenerator $url,
		protected IUserManager $userManager,
		protected IManager $activityManager,
	) {
	}

	/**
	 * @param string $language
	 * @param IEvent $event
	 * @param IEvent|null $previousEvent
	 * @return IEvent
	 * @throws UnknownActivityException
	 * @since 11.0.0
	 */
	public function parse($language, IEvent $event, ?IEvent $previousEvent = null): IEvent {
		if ($event->getApp() !== Application::APP_ID) {
			throw new UnknownActivityException();
		}

		if ($event->getSubject() !== 'impersonate_login') {
			throw new UnknownActivityException();
		}

		$l = $this->languageFactory->get(Application::APP_ID, $language);

		$params = $event->getSubjectParameters();
		$affectedUser = $event->getAffectedUser();
		$parsedParameters = [
			'actor' => $this->generateUserParameter($params['actor']),
		];

		$subject = $l->t('An administrator ({actor}) accessed your account');

		$event->setRichSubject($subject, $parsedParameters);
		$event->setIcon($this->url->getAbsoluteURL($this->url->imagePath(Application::APP_ID, 'actions/user-admin.' . ($this->activityManager->getRequirePNG() ? 'png' : 'svg'))));

		return $event;
	}

	/**
	 * @param string $uid
	 * @return array
	 */
	protected function generateUserParameter(string $uid): array {
		return [
			'type' => 'user',
			'id' => $uid,
			'name' => $this->userManager->getDisplayName($uid) ?? $uid,
		];
	}
}
