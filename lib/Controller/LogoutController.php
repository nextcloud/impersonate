<?php

namespace OCA\Impersonate\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\ILogger;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\ISession;
use OCP\IUserManager;
use OCP\IUserSession;

class LogoutController extends Controller {
	/** @var IUserManager */
	private $userManager;
	/** @var IUserSession */
	private $userSession;
	/** @var ILogger */
	private $logger;
	/** @var ISession */
	private $session;

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
	 * @NoAdminRequired
	 *
	 * @param string $userId
	 * @return JSONResponse
	 */
	public function logout($userId) {
		$user = $this->session->get('oldUserId');
		$user = $this->userManager->get($user);

		if($user === null) {
			return new JSONResponse(
				sprintf(
					'No user found for %s',
					$userId
				),
				Http::STATUS_NOT_FOUND
			);
		}

		$this->userSession->setUser($user);

		$this->logger->info(
			sprintf(
				'Switching back to previous user %s',
				$userId
			),
			[
				'app' => 'impersonate',
			]
		);
		$this->session->remove('oldUserId');
		return new JSONResponse();
	}
}
