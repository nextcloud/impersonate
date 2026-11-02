/**
 * SPDX-FileCopyrightText: 2017-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2017 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import axios from '@nextcloud/axios'
import { showConfirmation, showWarning } from '@nextcloud/dialogs'
import { t } from '@nextcloud/l10n'
import { addPasswordConfirmationInterceptors, PwdConfirmationMode } from '@nextcloud/password-confirmation'
import { generateUrl } from '@nextcloud/router'
import logger from './services/logger.ts'

interface User {
	id: string
}

(function() {
	/**
	 *
	 * @param userId of the targeted user
	 */
	function impersonate(userId: string): void {
		const url = generateUrl('apps/impersonate/user')
		const params = new URLSearchParams({
			userId,
		})

		axios.post(url, params, {
			confirmPassword: PwdConfirmationMode.Strict,
		}).then(() => {
			window.location = generateUrl('/')
		}).catch(() => {
			showWarning(t('impersonate', 'Could not impersonate user'))
		})
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
		addPasswordConfirmationInterceptors(axios)

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
})()
