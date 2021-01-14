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

return [
	'routes' => [

		// Land in users setting page ( for admin user only )
		[
			'name' => 'Settings#impersonate',
			'url' => '/user',
			'verb' => 'POST',
		],

		//Land in index page
		[
			'name' => 'Logout#logout',
			'url' => '/logout',
			'verb' => 'POST',
		],
	],
];
