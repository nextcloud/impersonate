/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/* global OC, OCA */
(function(OC, OCA) {
	OCA.Impersonate = {
		initSettings: function() {
			var $authorized = $('#impersonate .authorized')

			OC.Settings.setupGroupsSelect($authorized)
			$authorized.change(function(event) {
				var groups = event.val || ['admin']
				groups = JSON.stringify(groups)
				OCP.AppConfig.setValue('impersonate', 'authorized', groups)
			})
		}
	}

	document.addEventListener('DOMContentLoaded', OCA.Impersonate.initSettings)
})(OC, OCA)
