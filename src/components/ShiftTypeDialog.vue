<template>
	<NcDialog
		noClose
		:name="dialogName"
		size="normal">
		<form id="shift-type-form" class="flex flex-col gap-2" @submit.prevent="onSubmit">
			<div class="grid grid-cols-2 gap-2 items-end sm:grid-cols-6">
				<NcSelect
					v-model="group"
					:disabled="!!shiftType"
					:options="shiftAdminGroups"
					label="display_name"
					:inputLabel="t(APP_ID, 'Group')"
					:clearable="false"
					required
					class="sm:col-span-2 w-full !min-w-0" />
				<NcTextField
					v-model.trim="name"
					:label="t(APP_ID, 'Name')"
					required
					class="sm:col-span-2" />
				<NcColorPicker
					v-model="color"
					container=".modal-mask">
					<NcButton
						class="w-full border-none"
						:style="{
							backgroundColor: color,
							color: getContrastColor(color),
						}">
						{{ t(APP_ID, "Color") }}
					</NcButton>
				</NcColorPicker>
				<NcCheckboxRadioSwitch v-model="active">
					{{ t(APP_ID, "Active") }}
				</NcCheckboxRadioSwitch>
				<NcTextArea
					v-model.trim="description"
					:label="t(APP_ID, 'Description')"
					resize="vertical"
					class="col-span-2 sm:col-span-6" />
			</div>
			<CustomFieldset>
				<template #legend>
					{{ t(APP_ID, "Calendar settings") }}
				</template>
				<div class="flex flex-col gap-2">
					<NcNoteCard
						v-if="shiftType"
						type="info"
						:heading="t(APP_ID, 'Attention')"
						:text="t(APP_ID, 'Changing these settings will not automatically update calendar events for already existing shifts.')"
						class="m-0" />
					<div class="flex flex-wrap gap-2 items-end">
						<NcCheckboxRadioSwitch
							v-model="syncToCalendar"
							type="checkbox">
							{{ t(APP_ID, "Enable synchronization") }}
						</NcCheckboxRadioSwitch>
						<NcSelect
							v-model="calendarOption"
							:inputLabel="t(APP_ID, 'Select calendar')"
							:options="calendarOptions"
							:clearable="false"
							required
							:disabled="!syncToCalendar"
							class="flex-1 min-w-64" />
					</div>
					<CustomFieldset v-if="syncToCalendar">
						<template #legend>
							{{ t(APP_ID, "Calendar event fields") }}
						</template>
						<div class="flex flex-col gap-2">
							<div>{{ t(APP_ID, 'The values of these fields will be inserted into the corresponding calendar event fields when synchronizing shifts to the calendar app.') }}</div>
							<NcTextArea
								v-model.trim="caldavDescription"
								:label="t(APP_ID, 'Description')"
								resize="vertical" />
							<NcTextField
								v-model.trim="caldavLocation"
								:label="t(APP_ID, 'Location')" />
							<NcTextField
								v-model.trim="caldavCategories"
								:label="t(APP_ID, 'Categories')"
								:placeholder="t(APP_ID, 'Category 1, Category 2\\, with comma')"
								:helperText="t(APP_ID, `Separate categories by commas. To make the comma a part of the category, prepend the comma using a backslash: \\,`)" />
						</div>
					</CustomFieldset>
				</div>
			</CustomFieldset>
			<CustomFieldset>
				<template #legend>
					{{ t(APP_ID, "Repetition") }}
				</template>
				<div class="flex flex-col gap-2">
					<template v-if="!shiftType">
						<div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
							<NcSelect
								v-model="frequency"
								:inputLabel="t(APP_ID, 'Frequency')"
								:options="frequencies"
								:clearable="false"
								class="col-span-2" />
							<NcTextField
								v-model.trim="interval"
								:label="t(APP_ID, 'Interval')"
								type="number"
								min="1"
								required />
						</div>
						<CustomFieldset>
							<template #legend>
								{{ t(APP_ID, "Weekly type") }}
							</template>
							<NcRadioGroup
								v-model="weeklyType"
								:label=" t(APP_ID, 'Weekly type')"
								hideLabel>
								<NcRadioGroupButton
									v-for="type in REPETITION_WEEKLY_TYPES"
									:key="type"
									:value="type"
									:label="weeklyTypeTranslations[type]"
									class="whitespace-nowrap" />
							</NcRadioGroup>
						</CustomFieldset>
						<CustomFieldset>
							<template #legend>
								{{ t(APP_ID, "Config") }}
							</template>
							<div class="flex flex-col gap-2">
								<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
									<template v-if="weeklyType === 'by_day'">
										<NcDateTimePickerNative
											v-model="byDayReferenceDate"
											class="w-full"
											type="datetime-local"
											:label="t(APP_ID, 'Reference date & time')"
											required />
										<NcTimezonePicker v-model="timeZone" />
									</template>
									<IsoWeekDateInput
										v-else
										v-model="byWeekReference"
										:yearLabel="t(APP_ID, 'Reference year')"
										:weekLabel="t(APP_ID, 'Reference week')"
										class="sm:col-span-2" />
								</div>
								<CustomFieldset v-if="weeklyType === 'by_day'">
									<template #legend>
										{{ t(APP_ID, 'Amount') }}
									</template>
									<div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
										<NcTextField
											v-for="(localDay, shortDay) in shortDayToLocalDayMap"
											:key="shortDay"
											v-model.trim="shortDayToAmountMap[shortDay]"
											type="number"
											:label="localDay"
											min="0"
											required />
									</div>
								</CustomFieldset>
								<NcTextField
									v-else
									v-model.trim="byWeekAmount"
									class="w-28"
									type="number"
									:label="t(APP_ID, 'Amount')"
									min="1"
									required />
								<CustomFieldset v-if="weeklyType === 'by_day'">
									<template #legend>
										{{ t(APP_ID, "Duration") }} ({{ durationString }})
									</template>
									<DurationBuilder v-model="duration" class="grid grid-cols-2 sm:grid-cols-4 gap-2" />
								</CustomFieldset>
							</div>
						</CustomFieldset>
					</template>
					<ShiftTypeRepetitionDetails :repetition />
				</div>
			</CustomFieldset>
		</form>
		<template #actions>
			<NcButton :disabled="saving" @click="emit('close')">
				{{ t(APP_ID, "Cancel") }}
			</NcButton>
			<NcButton
				:disabled="saving"
				type="submit"
				variant="primary"
				form="shift-type-form">
				{{ t(APP_ID, "Save") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script setup lang="ts">
import type { Group } from '../models/group.ts'
import type {
	Repetition,
	RepetitionFrequency,
	RepetitionWeeklyType,
	ShiftType,
	ShiftTypePayload,
	ShiftTypePayloadType,
	ShiftTypePostPayload,
	ShiftTypePutPayload,
	ShortDayToAmountMap,
} from '../models/shiftType.ts'

import { t } from '@nextcloud/l10n'
import { whenever } from '@vueuse/core'
import { Temporal, toTemporalInstant } from 'temporal-polyfill'
import { computed, ref, watchEffect } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcColorPicker from '@nextcloud/vue/components/NcColorPicker'
import NcDateTimePickerNative from '@nextcloud/vue/components/NcDateTimePickerNative'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'
import NcRadioGroup from '@nextcloud/vue/components/NcRadioGroup'
import NcRadioGroupButton from '@nextcloud/vue/components/NcRadioGroupButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcTimezonePicker from '@nextcloud/vue/components/NcTimezonePicker'
import { injectShiftTypesContext } from '../views/ShiftTypesView.vue'
import CustomFieldset from './CustomFieldset.vue'
import DurationBuilder from './DurationBuilder.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import ShiftTypeRepetitionDetails from './ShiftTypeRepetitionDetails.vue'
import {
	REPETITION_FREQUENCIES,
	REPETITION_WEEKLY_TYPES,
	shortDayToLocalDayMap,
	weeklyTypeTranslations,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'
import { getContrastColor } from '../utils/color.ts'
import { getIsoWeekDate, userTimeZone } from '../utils/date.ts'
import { getInitialCommonCalendar, getInitialShiftAdminGroups, getInitialWritableCalendars } from '../utils/initialState.ts'
import { getNcSelectCalendarOption } from '../utils/nextcloudVue.ts'

const { shiftType = undefined } = defineProps<{ shiftType?: ShiftType }>()

const emit = defineEmits<{ close: [] }>()

const { create, update } = injectShiftTypesContext()

const saving = ref(false)

const dialogName = shiftType
	? t(APP_ID, 'Edit shift type')
	: t(APP_ID, 'Create shift type')

const frequencies = ref(REPETITION_FREQUENCIES)

const shiftAdminGroups = getInitialShiftAdminGroups()

const initialCommonCalendar = getInitialCommonCalendar()!
const commonCalendarOption = ref({
	...initialCommonCalendar,
	id: null,
	label: `${t(APP_ID, 'Global setting')} (${initialCommonCalendar.ownerDisplayName} - ${initialCommonCalendar.displayName})`,
})

const calendars = getInitialWritableCalendars()
const calendarOptions = [
	commonCalendarOption.value,
	...calendars.map(getNcSelectCalendarOption),
]

const group = ref<Group>()
const name = ref('')
const description = ref('')
const color = ref('#E40303')
const active = ref(true)
const frequency = ref<RepetitionFrequency>('weekly')
const interval = ref(1)
const weeklyType = ref<RepetitionWeeklyType>('by_day')
const byDayReferenceZdt = ref(Temporal.Now.zonedDateTimeISO(userTimeZone).with({
	second: 0,
	millisecond: 0,
	microsecond: 0,
	nanosecond: 0,
}))
const timeZone = ref(userTimeZone)
const duration = ref(new Temporal.Duration())
const shortDayToAmountMap = ref<ShortDayToAmountMap>({
	MO: 0,
	TU: 0,
	WE: 0,
	TH: 0,
	FR: 0,
	SA: 0,
	SU: 0,
})
const byWeekReference = ref(getIsoWeekDate(undefined, false))
const byWeekAmount = ref(1)
const caldavDescription = ref('')
const caldavLocation = ref('')
const caldavCategories = ref('')
const syncToCalendar = ref(true)
const calendarOption = ref(calendarOptions[0]!)

if (shiftType) {
	group.value = shiftType.group
	name.value = shiftType.name
	description.value = shiftType.description
	color.value = shiftType.color
	active.value = shiftType.active
	caldavDescription.value = shiftType.caldav.description ?? ''
	caldavLocation.value = shiftType.caldav.location ?? ''
	caldavCategories.value = shiftType.caldav.categories
	syncToCalendar.value = shiftType.sync_to_calendar
	if (shiftType.calendar) {
		calendarOption.value = getNcSelectCalendarOption(shiftType.calendar)
		if (!calendarOptions.some(({ id }) => id === calendarOption.value.id)) {
			calendarOptions.push(calendarOption.value)
		}
	}
	frequency.value = shiftType.repetition.frequency
	interval.value = shiftType.repetition.interval
	weeklyType.value = shiftType.repetition.weekly_type

	if (shiftType.repetition.weekly_type === 'by_day') {
		byDayReferenceZdt.value
			= Temporal.ZonedDateTime.from(shiftType.repetition.config.reference)
		timeZone.value = byDayReferenceZdt.value.timeZoneId
		duration.value
			= Temporal.Duration.from(shiftType.repetition.config.duration)
		shortDayToAmountMap.value
			= structuredClone(shiftType.repetition.config.short_day_to_amount_map)
	} else {
		byWeekReference.value = shiftType.repetition.config.reference
		byWeekAmount.value = shiftType.repetition.config.amount
	}
}

const byDayReferenceDate = ref<Date | null>(new Date(byDayReferenceZdt.value
	.toPlainDateTime()
	.toZonedDateTime(userTimeZone)
	.epochMilliseconds))

const durationString = computed(() => duration.value.toString())

/**
 * Handle the form submission
 */
async function onSubmit() {
	try {
		saving.value = true
		if (shiftType) {
			await update(shiftType.id, buildPayload('put'))
		} else {
			await create(buildPayload('post'))
		}
		emit('close')
	} finally {
		saving.value = false
	}
}

const repetition = computed<Repetition>(() => ({
	frequency: frequency.value,
	interval: interval.value,
	...weeklyType.value === 'by_day'
		? {
				weekly_type: 'by_day',
				config: {
					reference: byDayReferenceZdt.value,
					short_day_to_amount_map: shortDayToAmountMap.value,
					duration: duration.value,
				},
			}
		: {
				weekly_type: 'by_week',
				config: {
					reference: byWeekReference.value,
					amount: byWeekAmount.value,
				},
			},
}))

/**
 * Builds the request payload
 *
 * @param type The type of the payload, either 'post' for creating a new shift type or 'put' for updating an existing one
 */
function buildPayload<T extends ShiftTypePayloadType>(type: T): ShiftTypePayload<T> {
	const common = {
		name: name.value,
		description: description.value,
		color: color.value,
		active: active.value,
		caldav: {
			description: caldavDescription.value,
			location: caldavLocation.value,
			categories: caldavCategories.value,
		},
		sync_to_calendar: syncToCalendar.value,
		calendar_id: calendarOption.value.id,
	}

	if (type === 'post') {
		const payload: ShiftTypePostPayload = {
			...common,
			group_id: group.value!.id,
			repetition: repetition.value,
		}
		return payload as ShiftTypePayload<T>
	} else {
		const payload: ShiftTypePutPayload = common
		return payload as ShiftTypePayload<T>
	}
}

watchEffect(() => {
	if (!byDayReferenceDate.value) {
		return
	}
	byDayReferenceZdt.value = toTemporalInstant.call(byDayReferenceDate.value)
		.toZonedDateTimeISO(userTimeZone)
		.toPlainDateTime()
		.toZonedDateTime(timeZone.value)
})

whenever(() => !syncToCalendar.value, () => calendarOption.value = calendarOptions[0]!)
</script>
