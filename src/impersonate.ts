/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { showConfirmation, showWarning } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import logger from './services/logger.ts'

// eslint-disable-next-line  @typescript-eslint/no-explicit-any
declare const OC: any
// eslint-disable-next-line  @typescript-eslint/no-explicit-any
declare const OCA: any

interface User {
	id: string
}

(function(OC) {
	/**
	 *
	 * @param userId of the targeted user
	 */
	function impersonate(userId: string): void {
		const xhr = new XMLHttpRequest()
		xhr.onreadystatechange = function(): void {
			if (xhr.readyState === XMLHttpRequest.DONE) {
				if (xhr.status === 0 || (xhr.status >= 200 && xhr.status < 300)) {
					window.location = generateUrl('/')
				} else {
					showWarning(t('impersonate', 'Could not impersonate user'))
				}
			}
		}
		xhr.open('POST', generateUrl('apps/impersonate/user'))
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
		xhr.send('userId=' + encodeURIComponent(userId) + '&requesttoken=' + encodeURIComponent(OC.requestToken))
	}

	/**
	 *
	 * @param event (unused)
	 * @param user user object of the targeted account
	 */
	async function impersonateDialog(event: Event, user: User): void {
		const confirmed = await showConfirmation({
			name: t('impersonate', 'Impersonate user'),
			text: t('impersonate', 'Are you sure you want to impersonate "{userId}"?', { userId: user.id }),
		})
		if (confirmed) {
			impersonate(user.id)
		}
	}

	const registerFunction = function(event: Event, delay: number): void {
		if (OCA.Settings === undefined) {
			delay = delay * 2
			if (delay === 0) {
				delay = 15
			}
			if (delay > 500) {
				logger.error('Could not register impersonate script')
				return
			}
			setTimeout(function() {
				registerFunction(event, delay)
			}, delay)
		} else {
			OCA.Settings.UserList.registerAction('icon-user', t('impersonate', 'Impersonate'), impersonateDialog)
		}
	}

	document.addEventListener('DOMContentLoaded', function(e: Event): void {
		registerFunction(e, 0)
	})
})(OC)
