<template>
	<div class="p-[var(--app-navigation-padding)] flex flex-col gap-2">
		<IsoWeekDateInput
			ref="isoWeekDateInput"
			v-model="isoWeekDate"
			fluid />
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
		<InputGroup class="-mt-1">
			<label for="groups">{{ t(APP_ID, "Groups") }}</label>
			<NcSelect
				v-model="selectedGroups"
				inputId="groups"
				:options="groups"
				label="display_name"
				labelOutside
				keepOpen
				multiple
				noWrap
				class="min-w-48" />
		</InputGroup>
		<InputGroup class="-mt-1">
			<label for="hidden-users">{{ t(APP_ID, "Hidden users") }}</label>
			<NcSelect
				v-model="hiddenUserOptions"
				inputId="hidden-users"
				:options="userOptions"
				label="displayName"
				labelOutside
				keepOpen
				multiple
				noWrap
				class="min-w-48" />
		</InputGroup>
		<InputGroup class="-mt-1">
			<label for="show-weekly-shifts">{{ t(APP_ID, "Weekly shifts") }}</label>
			<NcCheckboxRadioSwitch
				id="show-weekly-shifts"
				v-model="showWeeklyShifts"
				type="checkbox">
				{{ t(APP_ID, "Show weekly shifts") }}
			</NcCheckboxRadioSwitch>
		</InputGroup>
	</div>
</template>

<script setup lang="ts">
import type { NcSelectUsersOption } from '../models/nextcloudVue.ts'

import { t } from '@nextcloud/l10n'
import { storeToRefs } from 'pinia'
import { computed, onMounted, ref, useTemplateRef } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSelect from '@nextcloud/vue/components/NcSelect'
// @ts-expect-error package has no types
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
// @ts-expect-error package has no types
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import InputGroup from './InputGroup.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import { getUsers } from '../db/user.ts'
import { useUserSettingsStore } from '../stores/userSettings.ts'
import { APP_ID } from '../utils/appId.ts'
import { getInitialGroups } from '../utils/initialState.ts'
import { getNcSelectUsersOption } from '../utils/nextcloudVue.ts'

const store = useUserSettingsStore()

const { isoWeekDate, selectedGroups, hiddenUserIds, showWeeklyShifts } = storeToRefs(store)

const { resetIsoWeekDate } = store

const isoWeekDateInput = useTemplateRef('isoWeekDateInput')

const groups = ref(getInitialGroups())
const userOptions = ref<NcSelectUsersOption[]>([])

const hiddenUserOptions = computed({
	get() {
		return hiddenUserIds.value
			.map((id) => userOptions.value.find((option) => option.id === id))
			.filter((option): option is NcSelectUsersOption => option !== undefined)
	},
	set(options: NcSelectUsersOption[]) {
		hiddenUserIds.value = options.map(({ id }) => id)
	},
})

onMounted(async () => {
	const users = await getUsers()
	userOptions.value = users.map(getNcSelectUsersOption)
})
</script>
