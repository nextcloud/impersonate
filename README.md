<!--
  - SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Impersonate

Allow administrators to become a different user by adding an impersonate action
to the user list. This is especially useful for debugging issues reported by users.

To impersonate a user an administrator has to simply follow the following four steps:

1. Login as administrator to Nextcloud.
2. Open users administration interface.
3. Select the impersonate button on the affected user.
4. Confirm the impersonation.

The administrator is then logged-in as the user, to switch back to the regular user account they simply have to press the logout button.

## Note:

* This app is _not_ compatible with instances that have encryption enabled.
* While impersonate actions are logged, note that actions performed impersonated will be logged as the impersonated user.
* Impersonating a user is only possible after their _first login_.
