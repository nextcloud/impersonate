/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig(
	{
		adminSettings: 'src/admin_settings.ts',
		accountAction: 'src/impersonate.ts',
		logout: 'src/impersonate_logout.ts',
	}
)

