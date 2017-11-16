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

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\ISession;
use OCP\IUserManager;
use OCP\IUserSession;

class SettingsController extends Controller {
	/** @var IUserManager */
	private $userManager;
	/** @var IUserSession */
	private $userSession;
	/** @var ISession */
	private $session;
	/** @var ILogger */
	private $logger;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param IUserSession $userSession
	 * @param ISession $session
	 * @param ILogger $logger
	 */
	public function __construct($appName,
								IRequest $request,
								IUserManager $userManager,
								IUserSession $userSession,
								ISession $session,
								ILogger $logger) {
		parent::__construct($appName, $request);
		$this->userManager = $userManager;
		$this->userSession = $userSession;
		$this->session = $session;
		$this->logger = $logger;
	}

	/**
	 * @UseSession
	 *
	 * @param string $userId
	 * @return JSONResponse
	 */
	public function impersonate($userId) {
		$oldUserId = $this->userSession->getUser()->getUID();
		if($this->session->get('oldUserId') === null) {
			$this->session->set('oldUserId', $oldUserId);
		}
		$this->logger->warning(
			sprintf(
				'User %s trying to impersonate user %s',
				$oldUserId,
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
					'message' => sprintf('No user found for %s', $userId),
				],
				Http::STATUS_NOT_FOUND
			);
		}

		if ($user->getLastLogin() === 0) {
			return new JSONResponse(
				[
					'message' => sprintf('Can\'t impersonate %s, user has to be logged in at least once.', $userId),
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
		$this->userSession->setUser($user);
		return new JSONResponse();
	}
}

