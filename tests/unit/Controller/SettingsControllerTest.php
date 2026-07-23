<?php

/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2015 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Impersonate\Tests\Controller;

use OC\Group\Manager;
use OC\SubAdmin;
use OCA\Impersonate\Controller\SettingsController;
use OCA\Impersonate\Events\BeginImpersonateEvent;
use OCA\Impersonate\Listener\BeginImpersonateListener;
use OCA\Impersonate\Service\ConfigService;
use OCA\Impersonate\Service\NotifierService;
use OCP\App\IAppManager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IAppConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Test\TestCase;

/**
 * Class SettingsControllerTest
 * @group DB
 */

class SettingsControllerTest extends TestCase {
	private string $appName;
	private IUserManager|MockObject $userManager;
	private IGroupManager|MockObject $groupManager;
	private SubAdmin|MockObject $subadmin;
	private IUserSession|MockObject $userSession;
	private ISession|MockObject $session;
	private IAppConfig|MockObject $config;
	private LoggerInterface|MockObject $logger;
	private IL10N|MockObject $l;
	private SettingsController $controller;
	/** @var IEventDispatcher|IEventDispatcher&MockObject|MockObject */
	private $eventDispatcher;
	private IAppManager|MockObject $appManager;
	private ConfigService|MockObject $configService;
	/** @var NotifierService|NotifierService&MockObject|MockObject */
	private NotifierService $notifierService;

	protected function setUp(): void {
		parent::setUp();

		$this->appName = 'impersonate';
		$request = $this->createMock(IRequest::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->groupManager = $this->createMock(Manager::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->session = $this->createMock(ISession::class);
		$this->config = $this->createMock(IAppConfig::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->l = $this->createMock(IL10N::class);
		$this->l->expects($this->any())
			->method('t')
			->will($this->returnCallback(function ($text, $parameters = []) {
				return vsprintf($text, $parameters);
			}));
		$this->subadmin = $this->createMock(SubAdmin::class);
		$this->groupManager->expects($this->any())
			->method('getSubAdmin')
			->willReturn($this->subadmin);
		$this->eventDispatcher = $this->createMock(IEventDispatcher::class);
		$this->appManager = $this->createMock(IAppManager::class);
		$this->configService = $this->createMock(ConfigService::class);
		$this->notifierService = $this->getMockBuilder(NotifierService::class)
			->disableOriginalConstructor()
			->onlyMethods(['notifyPush', 'notifyMail', 'notifyActivity'])
			->getMock();

		$this->controller = new SettingsController(
			$this->appName,
			$request,
			$this->userManager,
			$this->groupManager,
			$this->userSession,
			$this->session,
			$this->config,
			$this->logger,
			$this->l,
			$this->eventDispatcher,
			$this->appManager,
			$this->configService,
		);
	}

	public function testImpersonateNotFound() {
		$user = $this->createMock('OCP\IUser');
		$user->method('getUID')
			->willReturn('admin');
		$this->userSession
			->method('getUser')
			->willReturn($user);

		$this->userSession->expects($this->never())
			->method('setUser');

		$this->eventDispatcher->expects($this->never())
			->method('dispatchTyped');

		$this->assertEquals(
			new JSONResponse(['message' => 'User not found'], Http::STATUS_NOT_FOUND),
			$this->controller->impersonate('notexisting@uid')
		);
	}

	public function usersProvider(): array {
		return [
			['username', 'username'],
			['UserName', 'username']
		];
	}

	public function usersProviderNotifications(): array {
		return [
			['username', 'username', ConfigService::NOTIFICATION_PUSH, 'notifyPush'],
			['username', 'username', ConfigService::NOTIFICATION_ACTIVITY, 'notifyActivity'],
			['username', 'username', ConfigService::NOTIFICATION_MAIL, 'notifyMail'],
		];
	}

	/**
	 * @dataProvider usersProvider
	 * @param $query
	 * @param $uid
	 */
	public function testAdminImpersonate($query, $uid) {
		$currentUser = $this->createMock(IUser::class);
		$currentUser->expects($this->any())
			->method('getUID')
			->willReturn('admin');

		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);
		$user->expects($this->any())
			->method('getLastLogin')
			->willReturn(1737662989);

		$this->userSession
			->method('getUser')
			->willReturn($currentUser);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->userSession->expects($this->once())
			->method('setUser')
			->with($user);

		$this->groupManager->expects($this->exactly(2))
			->method('isAdmin')
			->willReturnCallback(function ($user) {
				return $user === 'admin';
			});

		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($currentUser)
			->willReturn(['admin']);

		$this->config->expects($this->once())
			->method('getValueString')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturnArgument(2);

		$this->eventDispatcher->expects($this->once())
			->method('dispatchTyped')
			->willReturnCallback(function (BeginImpersonateEvent $event) use ($currentUser, $user) {
				$this->assertSame($currentUser, $event->getImpersonator());
				$this->assertSame($user, $event->getImpersonatedUser());
			});

		$this->assertEquals(
			new JSONResponse(),
			$this->controller->impersonate($query)
		);
	}

	/**
	 * @dataProvider usersProvider
	 * @param $query
	 * @param $uid
	 */
	public function testSubAdminImpersonate($query, $uid) {
		$currentUser = $this->createMock(IUser::class);
		$currentUser->expects($this->any())
			->method('getUID')
			->willReturn('admin');

		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);
		$user->expects($this->any())
			->method('getLastLogin')
			->willReturn(1737662989);

		$this->userSession
			->method('getUser')
			->willReturn($currentUser);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->userSession->expects($this->once())
			->method('setUser')
			->with($user);

		$this->groupManager->expects($this->exactly(2))
			->method('isAdmin')
			->willReturn(false);

		$this->subadmin->expects($this->once())
			->method('isUserAccessible')
			->with($currentUser, $user)
			->willReturn(true);

		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($currentUser)
			->willReturn(['subadmin']);

		$this->config->expects($this->once())
			->method('getValueString')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturn(json_encode(['admin', 'subadmin']));

		$this->eventDispatcher->expects($this->once())
			->method('dispatchTyped')
			->willReturnCallback(function (BeginImpersonateEvent $event) use ($currentUser, $user) {
				$this->assertSame($currentUser, $event->getImpersonator());
				$this->assertSame($user, $event->getImpersonatedUser());
			});

		$this->assertEquals(
			new JSONResponse(),
			$this->controller->impersonate($query)
		);
	}

	/**
	 * @dataProvider usersProvider
	 * @param $query
	 * @param $uid
	 */
	public function testSubAdminImpersonateNotAllowed($query, $uid) {
		$currentUser = $this->createMock(IUser::class);
		$currentUser->expects($this->any())
			->method('getUID')
			->willReturn('admin');

		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);

		$this->userSession
			->method('getUser')
			->willReturn($currentUser);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->groupManager->expects($this->once())
			->method('isAdmin')
			->with('admin')
			->willReturn(false);

		$this->subadmin->expects($this->once())
			->method('isUserAccessible')
			->with($currentUser, $user)
			->willReturn(true);

		$this->userSession->expects($this->never())
			->method('setUser')
			->with($user);

		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($currentUser)
			->willReturn(['subadmin']);

		$this->config->expects($this->once())
			->method('getValueString')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturnArgument(2);

		$this->eventDispatcher->expects($this->never())
			->method('dispatchTyped');

		$this->assertEquals(
			new JSONResponse(['message' => 'Insufficient permissions to impersonate user'], Http::STATUS_FORBIDDEN),
			$this->controller->impersonate($query)
		);
	}

	/**
	 * @dataProvider usersProvider
	 * @param $query
	 * @param $uid
	 */
	public function testSubAdminImpersonateNotAccessible($query, $uid) {
		$currentUser = $this->createMock(IUser::class);
		$currentUser->expects($this->any())
			->method('getUID')
			->willReturn('admin');

		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);

		$this->userSession
			->method('getUser')
			->willReturn($currentUser);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->groupManager->expects($this->once())
			->method('isAdmin')
			->with('admin')
			->willReturn(false);

		$this->subadmin->expects($this->once())
			->method('isUserAccessible')
			->with($currentUser, $user)
			->willReturn(false);

		$this->userSession->expects($this->never())
			->method('setUser')
			->with($user);

		$this->eventDispatcher->expects($this->never())
			->method('dispatchTyped');

		$this->assertEquals(
			new JSONResponse(['message' => 'Insufficient permissions to impersonate user'], Http::STATUS_FORBIDDEN),
			$this->controller->impersonate($query)
		);
	}

	/**
	 * @dataProvider usersProviderNotifications
	 * @param $query
	 * @param $uid
	 */
	public function testAdminImpersonateNotify($query, $uid, $configValue, $notifierMethod) {

		$beginImpersonateListener = new BeginImpersonateListener(
			$this->configService,
			$this->groupManager,
			$this->notifierService,
		);

		$currentUser = $this->createMock(IUser::class);
		$currentUser->expects($this->any())
			->method('getUID')
			->willReturn('admin');

		$user = $this->createMock(IUser::class);
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);
		$user->expects($this->any())
			->method('getLastLogin')
			->willReturn(1737662989);
		$user->expects($this->any())
			->method('getEmailAddress')
			->willReturn('user@test.com');

		$this->configService->expects($this->any())
			->method('getUserNotificationSetting')
			->with(false)
			->willReturn($configValue);

		$this->appManager->expects($this->any())
			->method('isEnabledForUser')
			->willReturn(true);

		$this->userSession
			->method('getUser')
			->willReturn($currentUser);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->userSession->expects($this->once())
			->method('setUser')
			->with($user);

		$this->groupManager->expects($this->exactly(3))
			->method('isAdmin')
			->willReturnCallback(function ($user) {
				return $user === 'admin';
			});

		$this->groupManager->expects($this->once())
			->method('getUserGroupIds')
			->with($currentUser)
			->willReturn(['admin']);

		$this->config->expects($this->once())
			->method('getValueString')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturnArgument(2);

		$this->eventDispatcher->expects($this->once())
			->method('dispatchTyped')
			->willReturnCallback(function (BeginImpersonateEvent $event) use ($currentUser, $user, $beginImpersonateListener) {
				$this->assertSame($currentUser, $event->getImpersonator());
				$this->assertSame($user, $event->getImpersonatedUser());
				$beginImpersonateListener->handle($event);
			});

		$this->notifierService->expects($this->once())
			->method($notifierMethod)
			->with($user, $currentUser);

		$this->assertEquals(
			new JSONResponse(),
			$this->controller->impersonate($query)
		);
	}
}
