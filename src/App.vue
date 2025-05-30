<template>
	<NcContent app-name="shiftsnext">
		<template v-if="checkConfig()">
			<NcAppNavigation>
				<template #list>
					<NcAppNavigationItem :name="t(APP_ID, 'Shifts')" to="/shifts" />
					<NcAppNavigationItem v-if="isShiftAdmin"
						:name="t(APP_ID, 'Shift types')"
						to="/shift-types" />
					<NcAppNavigationItem :name="t(APP_ID, 'Shift exchanges')"
						to="/shift-exchanges" />
				</template>

				<template #footer>
					<span class="pl-2">ShiftsApp 2.0.0</span>
				</template>
			</NcAppNavigation>

			<NcAppContent class="flex flex-col">
				<RouterView />
			</NcAppContent>

			<DelayBoxesWrapper />
		</template>
		<ConfigMissing v-else />
	</NcContent>
</template>

<script setup lang="ts">
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/components/NcAppNavigationItem'
import NcContent from '@nextcloud/vue/components/NcContent'
import { APP_ID } from './appId'
import { checkConfig } from './checkConfig'
import ConfigMissing from './components/ConfigMissing.vue'
import DelayBoxesWrapper from './components/DelayBoxesWrapper.vue'
import type { Group } from './models/group'

const shiftAdminGroups = loadState<Group[]>(APP_ID, 'shift_admin_groups', [])

const isShiftAdmin = shiftAdminGroups.length > 0
</script>
