<?php
/**
 * @copyright Copyright (c) 2016, Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
