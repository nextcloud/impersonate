<?php

/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Controller;

use OCA\Impersonate\Events\EndImpersonateEvent;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
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
		string $appName,
		IRequest $request,
		private readonly IUserManager $userManager,
		private readonly IUserSession $userSession,
		private readonly ISession $session,
		private readonly LoggerInterface $logger,
		private readonly IEventDispatcher $eventDispatcher,
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

		/** @var IUser $impersonatedUser */
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
