<?php

namespace OCA\Impersonate\Controller;

use OCA\Impersonate\Events\EndImpersonateEvent;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUserManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class LogoutController extends Controller {
	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param IUserManager $userManager
	 * @param IUserSession $userSession
	 * @param ISession $session
	 * @param LoggerInterface $logger
	 * @param IEventDispatcher $eventDispatcher
	 */
	public function __construct(
		string                   $appName,
		IRequest                 $request,
		private IUserManager     $userManager,
		private IUserSession     $userSession,
		private ISession         $session,
		private LoggerInterface  $logger,
		private IEventDispatcher $eventDispatcher,
	) {
		parent::__construct($appName, $request);
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
