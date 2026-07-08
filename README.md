<!--
  - SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# Impersonate

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/impersonate)](https://api.reuse.software/info/github.com/nextcloud/impersonate)

Allow administrators to become a different user by adding an impersonate action
to the user list. This is especially useful for debugging issues reported by users.

To impersonate a user an administrator has to simply follow the following four steps:

1. Login as administrator to Nextcloud.
2. Open users administration interface.
3. Select the impersonate button on the affected user.
4. Confirm the impersonation.

The administrator is then logged-in as the user, to switch back to the regular user account they simply have to press the logout button.

## Notifications and audit trail

Impersonation actions are logged in the Nextcloud log file at `warning` level, and Impersonate can also notify users when their account is impersonated by another user.

Notifications are configurable depending on whether the impersonated account belongs to a regular user or to an administrator. This allows stronger alerting policies for administrator impersonation (for example, email notifications).

Configuration is stored in the `impersonate.notifications` system setting. Default values:

`
'impersonate.notifications' => [
    'user' => 0,
    'admin' => 0,
],
`

Available notification modes are bitwise flags and can be combined together:
* 0 : No notification
* 1	: Push notification via the Notifications app
* 2	: Email notification
* 4	: Entry in the Activity app

## Note:

* This app is _not_ compatible with instances that have encryption enabled.
* While impersonate actions are logged, note that actions performed impersonated will be logged as the impersonated user.
* Impersonating a user is only possible after their _first login_.
