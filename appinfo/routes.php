<?php

/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2015 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
