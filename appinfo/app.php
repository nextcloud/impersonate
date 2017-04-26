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

if(\OC::$server->getSession()->get('oldUserId') !== null) {
	\OCP\Util::addScript('impersonate','impersonate_logout');
}
// --- register js for user management------------------------------------------
$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener(
	'OC\Settings\Users::loadAdditionalScripts',
	function() {
		\OCP\Util::addScript('impersonate', 'impersonate');
	}
);
