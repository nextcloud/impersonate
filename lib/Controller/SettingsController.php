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

use OC\Group\Manager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Notification\IManager;

class SettingsController extends Controller {
	/** @var IUserManager */
	private $userManager;
	/** @var IGroupManager|Manager */
	private $groupManager;
	/** @var IUserSession */
	private $userSession;
	/** @var ISession */
	private $session;
	/** @var IConfig */
	private $config;
	/** @var ILogger */
	private $logger;
	/** @var IL10N */
	private $l;
	/** @var IManager */
	protected $notificationManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param IGroupManager $groupManager
	 * @param IUserSession $userSession
	 * @param ISession $session
	 * @param IConfig $config
	 * @param ILogger $logger
	 * @param IL10N $l
	 * @param IManager $notificationManager
	 */
	public function __construct($appName,
								IRequest $request,
								IUserManager $userManager,
								IGroupManager $groupManager,
								IUserSession $userSession,
								ISession $session,
								IConfig $config,
								ILogger $logger,
								IL10N $l,
								IManager $notificationManager) {
		parent::__construct($appName, $request);
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
		$this->userSession = $userSession;
		$this->session = $session;
		$this->config = $config;
		$this->logger = $logger;
		$this->l = $l;
		$this->notificationManager = $notificationManager;
	}

	/**
	 * @UseSession
	 * @NoAdminRequired
	 *
	 * @param string $userId
	 * @return JSONResponse
	 */
	public function impersonate($userId) {
		/** @var IUser $currentUser */
		$currentUser = $this->userSession->getUser();

		if($this->session->get('oldUserId') === null) {
			$this->session->set('oldUserId', $currentUser->getUID());
		}
		$this->logger->warning(
			sprintf(
				'User %s trying to impersonate user %s',
				$currentUser->getUID(),
				$userId
				),
				[
					'app' => 'impersonate',
				]
		);

		$user = $this->userManager->get($userId);
		if ($user === null) {
			return new JSONResponse(
				[
					'message' => $this->l->t('User not found'),
				],
				Http::STATUS_NOT_FOUND
			);
		}

		if (!$this->groupManager->isAdmin($currentUser->getUID())
			&& !$this->groupManager->getSubAdmin()->isUserAccessible($currentUser, $user)) {
			return new JSONResponse(
				[
					'message' => $this->l->t('Insufficient permissions to impersonate user'),
				],
				Http::STATUS_FORBIDDEN
			);
		}

		$authorized = json_decode($this->config->getAppValue('impersonate', 'authorized', '["admin"]'));
		if (!empty($authorized)) {
			$userGroups = $this->groupManager->getUserGroupIds($currentUser);

			if (!array_intersect($userGroups, $authorized)) {
				return new JSONResponse(
					[
						'message' => $this->l->t('Insufficient permissions to impersonate user'),
					],
					Http::STATUS_FORBIDDEN
				);
			}
		}

		if ($user->getLastLogin() === 0) {
			return new JSONResponse(
				[
					'message' => $this->l->t('Can not impersonate the user because it was never logged in.'),
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
		$this->issueWarning($userId, $currentUser->getUID());
		$this->userSession->setUser($user);
		return new JSONResponse();
	}

	/**
	 * Issues the warning by creating a notification
	 *
	 * @param string $userId
	 * @param string $impersonator
	 */
	protected function issueWarning($userId, $impersonator) {
		$notification = $this->notificationManager->createNotification();
		try {
			$notification->setApp('impersonate')
				->setObject('impersonation', $userId)
				->setUser($userId)
				->setDateTime(new \DateTime())
				->setSubject('impersonate', ['impersonator' => $impersonator]);
			$this->notificationManager->notify($notification);
		} catch (\InvalidArgumentException $e) {
			$this->logger->logException($e, ['app' => 'impersonate']);
		}
	}
}
