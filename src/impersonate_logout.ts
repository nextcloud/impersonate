/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { getCurrentUser } from '@nextcloud/auth'
import { showWarning } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import logger from './services/logger.ts'

// eslint-disable-next-line  @typescript-eslint/no-explicit-any
declare const OC: any

(function(OC): void {
	/**
	 *
	 */
	function logoutHandler(): void {
		const xhr = new XMLHttpRequest()
		xhr.onreadystatechange = function(): void {
			if (xhr.readyState === XMLHttpRequest.DONE) {
				if (xhr.status === 0 || (xhr.status >= 200 && xhr.status < 400)) {
					window.location = generateUrl('settings/users')
				} else {
					showWarning(t('impersonate', 'Could not log out, please try again'))
				}
			}
		}
		xhr.open('POST', generateUrl('apps/impersonate/logout'))
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
		xhr.send('requesttoken=' + encodeURIComponent(OC.requestToken))
	}
	/**
	 *
	 */
	function modifyLogout(): void {
		const logoutElement = document.getElementById('logout')
		if (!logoutElement) {
			logger.error('Could not locate logout element')
			return
		}
		logoutElement.onclick = (event: PointerEvent): void => {
			event.preventDefault()
			logoutHandler()
		}
		document.getElementById('logout')?.setAttribute('href', '#')

		const text = '<a href="' + generateUrl('apps/files') + '">'
			+ t('impersonate', 'Logged in as {name} ({uid})', { uid: getCurrentUser()?.uid, name: getCurrentUser()?.displayName })
			+ '</a>'

		OC.Notification.showHtml(
			text,
			{
				isHTML: true,
				timeout: 0,
			},
		)
	}

	const enableImpersonateLogout = function(event: Event, delay: number): void {
		if (document.getElementById('logout') === null) {
			delay = delay * 2
			if (delay === 0) {
				delay = 15
			}
			if (delay > 500) {
				logger.error('Could not register impersonate script')
				return
			}
			setTimeout(function() {
				enableImpersonateLogout(event, delay)
			}, delay)
		} else {
			modifyLogout()
		}
	}

	document.addEventListener('DOMContentLoaded', function(e: Event) {
		enableImpersonateLogout(e, 0)
	})
})(OC)
