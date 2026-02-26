<template>
	<NcContent :appName="APP_ID">
		<template v-if="missingConfigs.length === 0">
			<NcAppNavigation>
				<NavigationControls />
				<template #list>
					<NcAppNavigationItem :name="t(APP_ID, 'Shifts')" to="/shifts" />
					<NcAppNavigationItem
						v-if="isShiftAdmin"
						:name="t(APP_ID, 'Shift types')"
						to="/shift-types" />
					<NcAppNavigationItem
						:name="t(APP_ID, 'Shift exchanges')"
						to="/shift-exchanges" />
				</template>

				<template #footer>
					<span class="pl-2">Shifts Next {{ APP_VERSION }}</span>
				</template>
			</NcAppNavigation>

			<NcAppContent class="flex flex-col">
				<RouterView />
			</NcAppContent>

			<DelayBoxesWrapper />
		</template>
		<ConfigMissing v-else :missingConfigs="missingConfigs" />
	</NcContent>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcAppContent from '@nextcloud/vue/components/NcAppContent'
import NcAppNavigation from '@nextcloud/vue/components/NcAppNavigation'
import NcAppNavigationItem from '@nextcloud/vue/components/NcAppNavigationItem'
import NcContent from '@nextcloud/vue/components/NcContent'
import ConfigMissing from './components/ConfigMissing.vue'
import DelayBoxesWrapper from './components/DelayBoxesWrapper.vue'
import NavigationControls from './components/NavigationControls.vue'
import { APP_ID, APP_VERSION } from './utils/appId.ts'
import { checkConfig } from './utils/checkConfig.ts'
import { getInitialIsShiftAdmin } from './utils/initialState.ts'

const missingConfigs = checkConfig()

const isShiftAdmin = getInitialIsShiftAdmin()
</script>
