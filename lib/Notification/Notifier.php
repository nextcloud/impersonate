<?php
/**
 * @copyright Copyright (c) 2017 Cagdas Bas <cagdasbs@gmail.com>
 *
 * @author Cagdas Bas <cagdasbs@gmail.com>
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

namespace OCA\Impersonate\Notification;


use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\AlreadyProcessedException;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {

	/** @var IConfig */
	protected $config;
	/** @var IFactory */
	protected $l10nFactory;
	/** @var IURLGenerator */
	protected $url;

	/**
	 * @param IConfig $config
	 * @param IFactory $l10nFactory
	 * @param IURLGenerator $urlGenerator
	 */
	public function __construct(IConfig $config, IFactory $l10nFactory, IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->l10nFactory = $l10nFactory;
		$this->url = $urlGenerator;
	}

	/**
	 * Identifier of the notifier, only use [a-z0-9_]
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getID(): string {
		return "impersonate";
	}

	/**
	 * Human readable name describing the notifier
	 *
	 * @return string
	 * @since 17.0.0
	 */
	public function getName(): string {
		return $this->l10nFactory->get("impersonate")->t('Impersonate');
	}

	/**
	 * @param INotification $notification
	 * @param string $languageCode The code of the language that should be used to prepare the notification
	 * @return INotification
	 * @throws \InvalidArgumentException When the notification was not prepared by a notifier
	 */
	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== "impersonate") {
			// Wrong app
			throw new \InvalidArgumentException('Unknown app');
		}

		$parameters = $notification->getSubjectParameters();
		$impersonator = $parameters['impersonator'];

		$imagePath = $this->url->imagePath("impersonate", 'app-alert.svg');

		$notification->setIcon($this->url->getAbsoluteURL($imagePath));

		// Read the language from the notification
		$l = $this->l10nFactory->get("impersonate", $languageCode);

		$notification->setParsedSubject(
			$l->t('User %s logged in as you', $impersonator)
		);
		return $notification;
	}
}
