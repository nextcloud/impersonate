<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

script('impersonate', 'admin_settings');

/** @var array $_ */
/** @var \OCP\IL10N $l */
?>
<div id="impersonate" class="section">
	<h2 class="inlineblock"><?php p($l->t('Impersonate user')); ?></h2>

	<p>
		<input type="hidden" name="authorized" class="authorized" value="<?php p($_['authorized']) ?>" style="width: 320px;" />
		<br />
		<em><?php p($l->t('These groups will be able to impersonate users they are allowed to administrate. If you remove all groups, every group administrator will be allowed to impersonate.')); ?></em>
	</p>
</div>
