<template>
	<div class="p-[var(--app-navigation-padding)] flex flex-col gap-2">
		<InputGroup v-if="isMobileViewport" class="-mt-1">
			<label for="shifts-display-mode">{{ t(APP_ID, "Display mode") }}</label>
			<div id="shifts-display-mode" class="flex gap-1">
				<NcButton
					class="flex-1"
					:variant="shiftsDisplayMode === 'team-day' ? 'primary' : 'secondary'"
					@click="shiftsDisplayMode = 'team-day'">
					{{ t(APP_ID, "Team day") }}
				</NcButton>
				<NcButton
					class="flex-1"
					:variant="shiftsDisplayMode === 'personal-week' ? 'primary' : 'secondary'"
					@click="shiftsDisplayMode = 'personal-week'">
					{{ t(APP_ID, "My week") }}
				</NcButton>
			</div>
		</InputGroup>
		<IsoWeekDateInput
			ref="isoWeekDateInput"
			v-model="isoWeekDate"
			fluid />
		<div class="flex gap-1">
			<NcButton
				:aria-label="t(APP_ID, activeDisplayMode === 'team-day' ? 'Previous day' : 'Previous week')"
				@click="decrease()">
				<template #icon>
					<ChevronLeft :size="20" />
				</template>
			</NcButton>
			<NcButton
				:aria-label="t(APP_ID, activeDisplayMode === 'team-day' ? 'Next day' : 'Next week')"
				@click="increase()">
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
		<InputGroup v-if="activeDisplayMode === 'team-day'" class="-mt-1">
			<label for="selected-day">{{ t(APP_ID, "Day") }}</label>
			<div id="selected-day" class="grid grid-cols-3 gap-1">
				<NcButton
					v-for="dayOption in dayOptions"
					:key="dayOption.value"
					:variant="selectedIsoWeekDateWithDay === dayOption.value ? 'primary' : 'secondary'"
					@click="setSelectedIsoWeekDateWithDay(dayOption.value)">
					{{ dayOption.label }}
				</NcButton>
			</div>
		</InputGroup>
		<InputGroup class="-mt-1">
			<label for="groups">{{ t(APP_ID, "Groups") }}</label>
			<NcSelect
				v-model="selectedGroups"
				inputId="groups"
				:options="groups"
				:appendToBody="false"
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
				:appendToBody="false"
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
				v-model="hideWeeklyShifts"
				type="checkbox">
				{{ t(APP_ID, "Hide weekly shifts") }}
			</NcCheckboxRadioSwitch>
		</InputGroup>
		<InputGroup class="-mt-1">
			<label for="hide-weekends">{{ t(APP_ID, "Weekends") }}</label>
			<NcCheckboxRadioSwitch
				id="hide-weekends"
				v-model="hideWeekends"
				type="checkbox">
				{{ t(APP_ID, "Hide weekends") }}
			</NcCheckboxRadioSwitch>
		</InputGroup>
		<InputGroup class="-mt-1">
			<label for="hide-open-shifts">{{ t(APP_ID, "Open shifts") }}</label>
			<NcCheckboxRadioSwitch
				id="hide-open-shifts"
				v-model="hideOpenShifts"
				type="checkbox">
				{{ t(APP_ID, "Hide open shifts") }}
			</NcCheckboxRadioSwitch>
		</InputGroup>
	</div>
</template>

<script setup lang="ts">
import type { NcSelectUsersOption } from '../models/nextcloudVue.ts'
import type { IsoWeekDateWithDay } from '../utils/date.ts'

import { t } from '@nextcloud/l10n'
import { useWindowSize } from '@vueuse/core'
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
import { formatDate, parseIsoWeekDate } from '../utils/date.ts'
import { getInitialGroups } from '../utils/initialState.ts'
import { getNcSelectUsersOption } from '../utils/nextcloudVue.ts'

const store = useUserSettingsStore()
const { width } = useWindowSize()

const {
	isoWeekDate,
	selectedGroups,
	hiddenUserIds,
	showWeeklyShifts,
	shiftsDisplayMode,
	selectedIsoWeekDateWithDay,
	hideWeekends,
	hideOpenShifts,
} = storeToRefs(store)

const isMobileViewport = computed(() => width.value <= 834)
const activeDisplayMode = computed(() => isMobileViewport.value ? shiftsDisplayMode.value : 'team-week')

const {
	resetIsoWeekDate,
	setSelectedIsoWeekDateWithDay,
	decreaseSelectedDay,
	increaseSelectedDay,
} = store

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

const hideWeeklyShifts = computed({
	get: () => !showWeeklyShifts.value,
	set: (hide: boolean) => {
		showWeeklyShifts.value = !hide
	},
})

const dayOptions = computed(() => {
	const startDay = 1
	const endDay = hideWeekends.value ? 5 : 7
	const options: Array<{ value: IsoWeekDateWithDay, label: string }> = []
	for (let day = startDay; day <= endDay; day++) {
		const value = `${isoWeekDate.value}-${day}` as IsoWeekDateWithDay
		options.push({
			value,
			label: formatDate(parseIsoWeekDate(value), {
				weekday: 'short',
				day: 'numeric',
			}),
		})
	}
	return options
})

/**
 * Decreases day or week depending on active display mode.
 */
function decrease(): void {
	if (activeDisplayMode.value === 'team-day') {
		decreaseSelectedDay()
		return
	}
	isoWeekDateInput.value?.decrease()
}

/**
 * Increases day or week depending on active display mode.
 */
function increase(): void {
	if (activeDisplayMode.value === 'team-day') {
		increaseSelectedDay()
		return
	}
	isoWeekDateInput.value?.increase()
}

onMounted(async () => {
	const users = await getUsers()
	userOptions.value = users.map(getNcSelectUsersOption)
})
</script>
