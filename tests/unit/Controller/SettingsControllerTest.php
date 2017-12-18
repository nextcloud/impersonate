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

use OCA\Impersonate\Controller\SettingsController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;
use OCP\IRequest;
use OCP\ILogger;
use OCP\ISession;
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
	/** @var IUserSession|\PHPUnit_Framework_MockObject_MockObject */
	private $userSession;
	/** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
	private $logger;
	/** @var ISession|\PHPUnit_Framework_MockObject_MockObject */
	private $session;
	/** @var SettingsController */
	private $controller;

	public function setUp() {
		$this->appName = 'impersonate';
		$this->request = $this->createMock(IRequest::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->logger = $this->createMock(ILogger::class);
		$this->session = $this->createMock(ISession::class);

		$this->controller = new SettingsController(
			$this->appName,
			$this->request,
			$this->userManager,
			$this->userSession,
			$this->session,
			$this->logger
		);

		parent::setUp();
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
			new JSONResponse(['message' => 'No user found for notexisting@uid'], Http::STATUS_NOT_FOUND),
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
	public function testImpersonate($query, $uid) {
		$user = $this->createMock('\OCP\IUser');
		$user->method('getUID')
			->willReturn($uid);

		$this->userSession
			->method('getUser')
			->willReturn($user);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->userSession->expects($this->once())
			->method('setUser')
			->with($user);

		$this->assertEquals(
			new JSONResponse(),
			$this->controller->impersonate($query)
		);
	}

	public function normalUsers() {
		return [
			['username', 'username'],
			['UserName', 'username']
		];
	}

	/**
	 * @dataProvider normalUsers
	 * @param $query
	 */

	public function testAdminImpersonateNormalUsers($query,$uid) {
		$loggedInUser = $this->createMock('OCP\IUser');
		$loggedInUser
			->expects($this->once())
			->method('getUID')
			->willReturn('admin');

		$this->userSession
			->expects($this->once())
			->method('getUser')
			->willReturn($loggedInUser);

		$userManager = $this->getMockBuilder('OC\User\Manager')
			->disableOriginalConstructor()
			->getMock();

		$userManager->expects($this->any())
			->method('createUser')
			->with($query,'123');

		$user = $this->createMock('\OCP\IUser');
		$user->expects($this->any())
			->method('getUID')
			->willReturn($uid);

		$this->userManager->expects($this->once())
			->method('get')
			->with($query)
			->willReturn($user);

		$this->userSession->expects($this->once())
			->method('setUser')
			->with($user);

		$this->assertEquals(
			new JSONResponse(),
			$this->controller->impersonate($query)
		);
	}

}
