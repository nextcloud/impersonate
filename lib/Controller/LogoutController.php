<?php

namespace OCA\Impersonate\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use Psr\Log\LoggerInterface;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\ISession;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\EventDispatcher\IEventDispatcher;
use OCA\Impersonate\Events\EndImpersonateEvent;

class LogoutController extends Controller {
	/** @var IUserManager */
	private $userManager;
	/** @var IUserSession */
	private $userSession;
	/** @var LoggerInterface */
	private $logger;
	/** @var ISession */
	private $session;
	/** @var IEventDispatcher */
	private $eventDispatcher;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param IUserSession $userSession
	 * @param ISession $session
	 * @param LoggerInterface $logger
	 */
	public function __construct($appName,
								IRequest $request,
								IUserManager $userManager,
								IUserSession $userSession,
								ISession $session,
								LoggerInterface $logger,
								IEventDispatcher $eventDispatcher) {
		parent::__construct($appName, $request);
		$this->userManager = $userManager;
		$this->userSession = $userSession;
		$this->session = $session;
		$this->logger = $logger;
		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @UseSession
	 * @NoAdminRequired
	 */
	public function logout(): JSONResponse {
		/** @var ?string $impersonatorUid */
		$impersonatorUid = $this->session->get('oldUserId');
		$impersonator = $this->userManager->get($impersonatorUid);

		if ($impersonator === null) {
			return new JSONResponse(
				'No impersonating user found.',
				Http::STATUS_NOT_FOUND
			);
		}

		$impersonatedUser = $this->userSession->getUser();

		$this->eventDispatcher->dispatchTyped(new EndImpersonateEvent($impersonator, $impersonatedUser));

		$this->userSession->setUser($impersonator);

		$this->logger->info(
			sprintf(
				'Switching back to previous user %s from user %s',
				$impersonatorUid, $impersonatedUser->getUID()
			),
			[
				'app' => 'impersonate',
			]
		);
		$this->session->remove('oldUserId');
		return new JSONResponse();
	}
}
