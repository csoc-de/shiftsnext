<template>
	<NcDialog
		noClose
		:name="dialogName"
		size="normal">
		<form id="shift-type-form" class="flex flex-col gap-2" @submit.prevent="onSubmit">
			<div class="grid grid-cols-2 gap-2 sm:grid-cols-6">
				<InputGroup class="sm:col-span-2">
					<label for="shift-type-group-id">{{ t(APP_ID, "Group") }}</label>
					<NcSelect
						v-model="group"
						:disabled="!!shiftType"
						inputId="shift-type-group-id"
						:options="shiftAdminGroups"
						label="display_name"
						labelOutside
						:clearable="false"
						required
						class="w-full !min-w-0" />
				</InputGroup>
				<InputGroup class="sm:col-span-2">
					<label for="shift-type-name">{{ t(APP_ID, "Name") }}</label>
					<NcTextField
						id="shift-type-name"
						v-model.trim="name"
						labelOutside
						required />
				</InputGroup>
				<InputGroup>
					<span class="cursor-pointer" @click="showColorPicker = true">{{ t(APP_ID, "Color") }}</span>
					<NcColorPicker
						v-slot="{ attrs }"
						v-model="color"
						v-model:shown="showColorPicker"
						container=".modal-mask">
						<div
							v-bind="attrs"
							:style="{ backgroundColor: color }"
							class="h-nc-clickable-area !w-full rounded-nc-element cursor-pointer" />
					</NcColorPicker>
				</InputGroup>
				<InputGroup>
					<label for="shift-type-active">{{ t(APP_ID, "Active") }}</label>
					<NcCheckboxRadioSwitch id="shift-type-active" v-model="active" :aria-label="t(APP_ID, 'Active')" />
				</InputGroup>
				<InputGroup class="col-span-2 sm:col-span-6">
					<label for="shift-type-description">{{
						t(APP_ID, "Description")
					}}</label>
					<NcTextArea
						id="shift-type-description"
						v-model.trim="description"
						labelOutside
						resize="vertical" />
				</InputGroup>
			</div>
			<CustomFieldset>
				<template #legend>
					{{ t(APP_ID, "Calendar event fields") }}
				</template>
				<div class="flex flex-col gap-2">
					<div>{{ t(APP_ID, 'The values of these fields will be inserted into the corresponding calendar event fields when synchronizing shifts to the calendar app.') }}</div>
					<InputGroup>
						<label for="shift-type-caldav-description">{{ t(APP_ID, "Description") }}</label>
						<NcTextArea
							id="shift-type-caldav-description"
							v-model.trim="caldavDescription"
							labelOutside
							resize="vertical" />
					</InputGroup>
					<InputGroup>
						<label for="shift-type-caldav-location">{{ t(APP_ID, "Location") }}</label>
						<NcTextField
							id="shift-type-caldav-location"
							v-model.trim="caldavLocation"
							labelOutside />
					</InputGroup>
					<InputGroup>
						<label for="shift-type-caldav-categories">{{ t(APP_ID, "Categories") }}</label>
						<NcTextField
							id="shift-type-caldav-categories"
							v-model.trim="caldavCategories"
							labelOutside
							:placeholder="t(APP_ID, 'Category 1, Category 2\\, with comma')"
							:helperText="t(APP_ID, `Separate categories by commas. To make the comma a part of the category, prepend the comma using a backslash: \\,`)" />
					</InputGroup>
				</div>
			</CustomFieldset>
			<CustomFieldset>
				<template #legend>
					{{ t(APP_ID, "Repetition") }}
				</template>
				<div class="flex flex-col gap-2">
					<template v-if="!shiftType">
						<div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
							<InputGroup class="col-span-2">
								<label for="shift-type-repetition-frequency">{{
									t(APP_ID, "Frequency")
								}}</label>
								<NcSelect
									v-model="frequency"
									inputId="shift-type-repetition-frequency"
									labelOutside
									:options="frequencies"
									:clearable="false" />
							</InputGroup>
							<InputGroup>
								<label for="shift-type-repetition-interval">{{
									t(APP_ID, "Interval")
								}}</label>
								<NcTextField
									id="shift-type-repetition-interval"
									v-model.trim="interval"
									labelOutside
									type="number"
									min="1"
									required />
							</InputGroup>
						</div>
						<InputGroup>
							<div>{{ t(APP_ID, "Weekly type") }}</div>
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
						</InputGroup>
						<CustomFieldset>
							<template #legend>
								{{ t(APP_ID, "Config") }}
							</template>
							<div class="flex flex-col gap-2">
								<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
									<template v-if="weeklyType === 'by_day'">
										<InputGroup>
											<label for="shift-type-repetition-config-reference">
												{{ t(APP_ID, "Reference date & time") }}
											</label>
											<NcDateTimePickerNative
												id="shift-type-repetition-config-reference"
												v-model="byDayReferenceDate"
												class="w-full"
												type="datetime-local"
												hideLabel
												required
												@update:modelValue="setByDayReference()" />
										</InputGroup>
										<InputGroup>
											<label for="shift-type-repetition-config-time-zone">{{ t(APP_ID, "Time zone") }}</label>
											<NcTimezonePicker
												v-model="timeZone"
												inputId="shift-type-repetition-config-time-zone"
												@update:modelValue="setByDayReference()" />
										</InputGroup>
									</template>
									<IsoWeekDateInput
										v-else
										v-model="byWeekReference"
										:yearLabel="t(APP_ID, 'Reference year')"
										:weekLabel="t(APP_ID, 'Reference week')"
										class="sm:col-span-2" />
								</div>
								<InputGroup v-if="weeklyType === 'by_day'">
									<div>{{ t(APP_ID, 'Amount') }}</div>
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
								</InputGroup>
								<NcTextField
									v-else
									v-model.trim="byWeekAmount"
									class="w-28"
									type="number"
									:label="t(APP_ID, 'Amount')"
									min="1"
									required />
								<InputGroup v-if="weeklyType === 'by_day'">
									<div>{{ t(APP_ID, "Duration") }} ({{ durationString }})</div>
									<DurationBuilder v-model="duration" class="grid grid-cols-2 sm:grid-cols-4 gap-2" />
								</InputGroup>
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

import { t } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'
import { computed, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcColorPicker from '@nextcloud/vue/components/NcColorPicker'
import NcDateTimePickerNative from '@nextcloud/vue/components/NcDateTimePickerNative'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcRadioGroup from '@nextcloud/vue/components/NcRadioGroup'
import NcRadioGroupButton from '@nextcloud/vue/components/NcRadioGroupButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcTimezonePicker from '@nextcloud/vue/components/NcTimezonePicker'
import { injectShiftTypesContext } from '../views/ShiftTypesView.vue'
import CustomFieldset from './CustomFieldset.vue'
import DurationBuilder from './DurationBuilder.vue'
import InputGroup from './InputGroup.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import ShiftTypeRepetitionDetails from './ShiftTypeRepetitionDetails.vue'
import {
	type Repetition,
	type RepetitionFrequency,
	type RepetitionWeeklyType,
	type ShiftType,
	type ShiftTypePayload,
	type ShiftTypePayloadType,
	type ShiftTypePostPayload,
	type ShiftTypePutPayload,
	type ShortDayToAmountMap,

	REPETITION_FREQUENCIES,
	REPETITION_WEEKLY_TYPES,
	shortDayToLocalDayMap,
	weeklyTypeTranslations,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'
import { getIsoWeekDate, userTimeZone } from '../utils/date.ts'
import { getInitialShiftAdminGroups } from '../utils/initialState.ts'

const { shiftType = undefined } = defineProps<{ shiftType?: ShiftType }>()

const emit = defineEmits<{ close: [] }>()

const { create, update } = injectShiftTypesContext()

const saving = ref(false)

const dialogName = shiftType
	? t(APP_ID, 'Edit shift type')
	: t(APP_ID, 'Create shift type')

const frequencies = ref(REPETITION_FREQUENCIES)
const showColorPicker = ref(false)

const shiftAdminGroups = getInitialShiftAdminGroups()

const group = ref<Group>()
const name = ref('')
const description = ref('')
const color = ref('#E40303')
const active = ref(true)
const frequency = ref<RepetitionFrequency>('weekly')
const interval = ref(1)
const weeklyType = ref<RepetitionWeeklyType>('by_day')
const byDayReference = ref(Temporal.Now.zonedDateTimeISO(userTimeZone).with({
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

if (shiftType) {
	group.value = shiftType.group
	name.value = shiftType.name
	description.value = shiftType.description
	color.value = shiftType.color
	active.value = shiftType.active
	caldavDescription.value = shiftType.caldav.description ?? ''
	caldavLocation.value = shiftType.caldav.location ?? ''
	caldavCategories.value = shiftType.caldav.categories

	frequency.value = shiftType.repetition.frequency
	interval.value = shiftType.repetition.interval
	weeklyType.value = shiftType.repetition.weekly_type

	if (shiftType.repetition.weekly_type === 'by_day') {
		byDayReference.value
			= Temporal.ZonedDateTime.from(shiftType.repetition.config.reference)
		timeZone.value = byDayReference.value.timeZoneId
		duration.value
			= Temporal.Duration.from(shiftType.repetition.config.duration)
		shortDayToAmountMap.value
			= structuredClone(shiftType.repetition.config.short_day_to_amount_map)
	} else {
		byWeekReference.value = shiftType.repetition.config.reference
		byWeekAmount.value = shiftType.repetition.config.amount
	}
}

const byDayReferenceDate = ref<Date | null>(new Date(byDayReference.value
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
					reference: byDayReference.value,
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

/**
 * Sets the `byDayReference` by combining the value of the date time picker
 * and time zone picker
 */
function setByDayReference(): void {
	if (!byDayReferenceDate.value) {
		return
	}
	byDayReference.value = Temporal.Instant.fromEpochMilliseconds(byDayReferenceDate.value.valueOf())
		.toZonedDateTimeISO(userTimeZone)
		.toPlainDateTime()
		.toZonedDateTime(timeZone.value)
}
</script>
