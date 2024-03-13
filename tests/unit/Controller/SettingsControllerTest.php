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

namespace OCA\Impersonate\Tests\Controller;

use OC\Group\Manager;
use OC\SubAdmin;
use OCA\Impersonate\Controller\SettingsController;
use OCA\Impersonate\Events\BeginImpersonateEvent;
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
			$this->eventDispatcher
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

		$this->groupManager->expects($this->once())
			->method('isAdmin')
			->with('admin')
			->willReturn(true);

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

		$this->groupManager->expects($this->once())
			->method('isAdmin')
			->with('admin')
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
}
