<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<NcSettingsSection
		:name="t('impersonate', 'Impersonate')">
		<div>
			<NcFormGroup
				:label="t('impersonate', 'Authorized groups')"
				:description="t('impersonate', 'These groups will be able to impersonate users they are allowed to administrate. If you remove all groups, every group administrator will be allowed to impersonate.')">
				<NcSettingsSelectGroup
					id="impersonate-authorizedGroups"
					v-model="authorizedGroups"
					:label="t('impersonate', 'These groups will be able to impersonate users they are allowed to administrate. If you remove all groups, every group administrator will be allowed to impersonate.')"
					style="width: 100%"
					@update:modelValue="onSelectAuthorizedGroups" />
			</NcFormGroup>
			<NcFormGroup
				:label="t('impersonate', 'Protected groups')"
				:description="t('impersonate', 'Members of these groups cannot be impersonated, not even by members of the authorized groups. If you remove all groups, every user can be impersonated.')">
				<NcSettingsSelectGroup
					id="impersonate-protectedGroups"
					v-model="protectedGroups"
					:label="t('impersonate', 'Members of these groups cannot be impersonated, not even by members of the authorized groups. If you remove all groups, every user can be impersonated.')"
					style="width: 100%"
					@update:modelValue="onSelectProtectedGroups" />
			</NcFormGroup>
		</div>
	</NcSettingsSection>
</template>

<script lang="ts">
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { defineComponent } from 'vue'
import NcFormGroup from '@nextcloud/vue/components/NcFormGroup'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import NcSettingsSelectGroup from '@nextcloud/vue/components/NcSettingsSelectGroup'

// eslint-disable-next-line  @typescript-eslint/no-explicit-any
declare const OCP: any

export default defineComponent({
	name: 'AdminSettings',
	components: {
		NcFormGroup,
		NcSettingsSection,
		NcSettingsSelectGroup,
	},

	data() {
		return {
			authorizedGroups: loadState<string[]>('impersonate', 'authorized'),
			protectedGroups: loadState<string[]>('impersonate', 'protected'),
		}
	},

	methods: {
		t,
		onSelectAuthorizedGroups(groups: string[]) {
			this.authorizedGroups = [...new Set(['admin', ...groups])]
			OCP.AppConfig.setValue('impersonate', 'authorized', JSON.stringify(this.authorizedGroups))
		},

		onSelectProtectedGroups(groups: string[]) {
			OCP.AppConfig.setValue('impersonate', 'protected', JSON.stringify(groups))
		},
	},
})

</script>
