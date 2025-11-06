<template>
	<NcContent app-name="shiftsnext">
		<template v-if="missingConfigs.length === 0">
			<NcAppNavigation>
				<template #list>
					<NcAppNavigationItem :name="t(APP_ID, 'Shifts')" to="/shifts" />
					<NcAppNavigationItem
						v-if="isShiftAdmin"
						:name="t(APP_ID, 'Types')"
						to="/shift-types" />
					<NcAppNavigationItem
						:name="t(APP_ID, 'Exchanges')"
						to="/shift-exchanges" />
				</template>

				<template #footer>
					<span class="pl-2">Shifts Next {{ _appVersion }}</span>
				</template>
			</NcAppNavigation>

			<NcAppContent class="flex flex-col">
				<RouterView />
			</NcAppContent>

			<DelayBoxesWrapper />
		</template>
		<ConfigMissing v-else :missing-configs="missingConfigs" />
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
import { APP_ID } from './appId.ts'
import { checkConfig } from './checkConfig.ts'
import { getInitialIsShiftAdmin } from './initialState.ts'

const missingConfigs = checkConfig()

const isShiftAdmin = getInitialIsShiftAdmin()

// @ts-expect-error global variable injected by the build process
const _appVersion = appVersion
</script>
