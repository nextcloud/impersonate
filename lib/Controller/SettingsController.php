<?php

/**
 * ownCloud - impersonate
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Jörn Friedrich Dreyer <jfd@owncloud.com>
 * @copyright Jörn Friedrich Dreyer 2015
 */

namespace OCA\Impersonate\Controller;

use OCA\Impersonate\Events\BeginImpersonateEvent;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Exceptions\AppConfigTypeConflictException;
use OCP\IAppConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		protected IUserManager $userManager,
		protected IGroupManager $groupManager,
		protected IUserSession $userSession,
		protected ISession $session,
		protected IAppConfig $config,
		protected LoggerInterface $logger,
		protected IL10N $l,
		protected IEventDispatcher $eventDispatcher,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @UseSession
	 * @NoAdminRequired
	 * @throws AppConfigTypeConflictException
	 */
	public function impersonate(string $userId): JSONResponse {
		/** @var IUser $impersonator */
		$impersonator = $this->userSession->getUser();

		$this->logger->warning(
			sprintf(
				'User %s trying to impersonate user %s',
				$impersonator->getUID(),
				$userId
			),
			[
				'app' => 'impersonate',
			]
		);

		$impersonatee = $this->userManager->get($userId);
		if ($impersonatee === null) {
			return new JSONResponse(
				[
					'message' => $this->l->t('User not found'),
				],
				Http::STATUS_NOT_FOUND
			);
		}

		if (!$this->groupManager->isAdmin($impersonator->getUID())
			&& !$this->groupManager->isDelegatedAdmin($impersonator->getUID())
			&& !$this->groupManager->getSubAdmin()->isUserAccessible($impersonator, $impersonatee)) {
			return new JSONResponse(
				[
					'message' => $this->l->t('Insufficient permissions to impersonate user'),
				],
				Http::STATUS_FORBIDDEN
			);
		}

		$authorized = json_decode($this->config->getValueString('impersonate', 'authorized', '["admin"]'));
		if (!empty($authorized)) {
			$userGroups = $this->groupManager->getUserGroupIds($impersonator);

			if (!array_intersect($userGroups, $authorized)) {
				return new JSONResponse(
					[
						'message' => $this->l->t('Insufficient permissions to impersonate user'),
					],
					Http::STATUS_FORBIDDEN
				);
			}
		}

		if ($impersonatee->getLastLogin() === 0) {
			return new JSONResponse(
				[
					'message' => $this->l->t('Cannot impersonate the user because it was never logged in'),
				],
				Http::STATUS_FORBIDDEN
			);
		}

		if ($impersonatee->getUID() === $impersonator->getUID()) {
			return new JSONResponse(
				[
					'message' => $this->l->t('Cannot impersonate yourself'),
				],
				Http::STATUS_FORBIDDEN
			);
		}

		$this->logger->warning(
			sprintf(
				'Changing to user %s',
				$userId
			),
			[
				'app' => 'impersonate',
			]
		);
		if ($this->session->get('oldUserId') === null) {
			$this->session->set('oldUserId', $impersonator->getUID());
		}

		$this->eventDispatcher->dispatchTyped(new BeginImpersonateEvent($impersonator, $impersonatee));

		$this->userSession->setUser($impersonatee);
		return new JSONResponse();
	}
}
