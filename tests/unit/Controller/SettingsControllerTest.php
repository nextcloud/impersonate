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
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\ILogger;
use OCP\ISession;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;
use Test\TestCase;

/**
 * Class SettingsControllerTest
 * @group DB
 */

class SettingsControllerTest extends TestCase {

	/** @var string */
	private $appName;
	/** @var IRequest|\PHPUnit_Framework_MockObject_MockObject */
	private $request;
	/** @var IUserManager|\PHPUnit_Framework_MockObject_MockObject */
	private $userManager;
	/** @var IGroupManager|\PHPUnit_Framework_MockObject_MockObject */
	private $groupManager;
	/** @var SubAdmin|\PHPUnit_Framework_MockObject_MockObject */
	private $subadmin;
	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	private $userSession;
	/** @var ISession|\PHPUnit_Framework_MockObject_MockObject */
	private $session;
	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
	private $config;
	/** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
	private $logger;
	/** @var IL10N|\PHPUnit_Framework_MockObject_MockObject */
	private $l;
	/** @var SettingsController */
	private $controller;

	public function setUp() {
		parent::setUp();

		$this->appName = 'impersonate';
		$this->request = $this->createMock(IRequest::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->groupManager = $this->createMock(Manager::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->session = $this->createMock(ISession::class);
		$this->config = $this->createMock(IConfig::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->l = $this->createMock(IL10N::class);
		$this->l->expects($this->any())
			->method('t')
			->will($this->returnCallback(function($text, $parameters = array()) {
				return vsprintf($text, $parameters);
			}));
		$this->subadmin = $this->createMock(SubAdmin::class);
		$this->groupManager->expects($this->any())
			->method('getSubAdmin')
			->willReturn($this->subadmin);

		$this->controller = new SettingsController(
			$this->appName,
			$this->request,
			$this->userManager,
			$this->groupManager,
			$this->userSession,
			$this->session,
			$this->config,
			$this->logger,
			$this->l
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

		$this->assertEquals(
			new JSONResponse(['message' => 'User not found'], Http::STATUS_NOT_FOUND),
			$this->controller->impersonate('notexisting@uid')
		);
	}

	public function usersProvider() {
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
			->method('getAppValue')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturnArgument(2);

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
			->method('getAppValue')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturn(json_encode(['admin', 'subadmin']));

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
			->method('getAppValue')
			->with('impersonate', 'authorized', '["admin"]')
			->willReturnArgument(2);

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

		$this->assertEquals(
			new JSONResponse(['message' => 'Insufficient permissions to impersonate user'], Http::STATUS_FORBIDDEN),
			$this->controller->impersonate($query)
		);
	}
}
