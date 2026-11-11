<template>
	<div class="p-[var(--app-navigation-padding)] flex flex-col gap-2">
		<IsoWeekDateInput
			ref="isoWeekDateInput"
			v-model="isoWeekDate" />
		<div class="flex gap-1">
			<NcButton
				:aria-label="t(APP_ID, 'Previous week')"
				@click="isoWeekDateInput?.decrease()">
				<template #icon>
					<ChevronLeft :size="20" />
				</template>
			</NcButton>
			<NcButton
				:aria-label="t(APP_ID, 'Next week')"
				@click="isoWeekDateInput?.increase()">
				<template #icon>
					<ChevronRight :size="20" />
				</template>
			</NcButton>
			<NcButton
				class="flex-1"
				@click="resetIsoWeekDate">
				{{ t(APP_ID, "Today") }}
			</NcButton>
		</div>
		<NcSelect
			v-model="selectedGroups"
			:options="groups"
			label="display_name"
			:inputLabel="t(APP_ID, 'Groups')"
			keepOpen
			multiple
			noWrap
			class="mt-1 min-w-48" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { storeToRefs } from 'pinia'
import { ref, useTemplateRef } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import { useUserSettingsStore } from '../stores/userSettings.ts'
import { APP_ID } from '../utils/appId.ts'
import { getInitialGroups } from '../utils/initialState.ts'

const store = useUserSettingsStore()

const { isoWeekDate, selectedGroups } = storeToRefs(store)

const { resetIsoWeekDate } = store

const isoWeekDateInput = useTemplateRef('isoWeekDateInput')

const groups = ref(getInitialGroups())
</script>
