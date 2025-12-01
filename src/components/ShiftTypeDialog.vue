<template>
	<NcDialog
		no-close
		:name="dialogName"
		size="normal">
		<form id="shift-type-form" @submit.prevent="onSubmit">
			<!-- Trap autofocus to this invisible input to prevent NcSelect getting focused -->
			<AutoFocusTrap />
			<div class="grid grid-cols-2 gap-3 sm:grid-cols-6">
				<InputGroup class="sm:col-span-2">
					<label for="shift-type-group-id">{{ t(APP_ID, "Group") }}</label>
					<NcSelect
						v-model="group"
						:disabled="!!shiftType"
						input-id="shift-type-group-id"
						:options="shiftAdminGroups"
						label="display_name"
						label-outside
						:clearable="false"
						required
						class="w-full !min-w-0"
						@update:model-value="groupId = group?.id ?? ''" />
				</InputGroup>
				<InputGroup class="sm:col-span-2">
					<label for="shift-type-name">{{ t(APP_ID, "Name") }}</label>
					<NcTextField
						id="shift-type-name"
						v-model.trim="name"
						label-outside
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
					<NcTextField
						id="shift-type-description"
						v-model.trim="description"
						label-outside />
				</InputGroup>
				<InputGroup class="col-span-2 sm:col-span-6">
					<label for="shift-type-categories">{{ t(APP_ID, "Categories") }}</label>
					<NcTextField
						id="shift-type-categories"
						v-model.trim="categories"
						label-outside />
				</InputGroup>
			</div>

			<CustomFieldset class="mt-3">
				<template #legend>
					{{ t(APP_ID, "Repetition") }}
				</template>
				<div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
					<InputGroup class="col-span-2">
						<label for="shift-type-repetition-frequency">{{
							t(APP_ID, "Frequency")
						}}</label>
						<NcSelect
							v-model="frequency"
							:disabled="!!shiftType"
							input-id="shift-type-repetition-frequency"
							label-outside
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
							:disabled="!!shiftType"
							label-outside
							type="number"
							min="1"
							required />
					</InputGroup>
				</div>
				<CustomFieldset class="mt-3">
					<template #legend>
						{{ t(APP_ID, "Weekly type") }}
					</template>
					<div class="flex">
						<NcCheckboxRadioSwitch
							v-for="(type, index) in REPETITION_WEEKLY_TYPES"
							:key="index"
							v-model="weeklyType"
							:disabled="!!shiftType"
							:value="type"
							button-variant
							name="repetition-weekly-type"
							type="radio"
							button-variant-grouped="horizontal">
							{{ weeklyTypeTranslations[type] }}
						</NcCheckboxRadioSwitch>
					</div>
				</CustomFieldset>
				<CustomFieldset class="mt-3">
					<template #legend>
						{{ t(APP_ID, "Config") }}
					</template>
					<div class="flex flex-col gap-3">
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
							<InputGroup>
								<template v-if="weeklyType === 'by_day'">
									<label for="shift-type-repetition-config-reference">
										{{ t(APP_ID, "Reference date & time") }}
									</label>
									<NcDateTimePickerNative
										id="shift-type-repetition-config-reference"
										v-model="byDayReferenceDate"
										:disabled="!!shiftType"
										class="w-full"
										type="datetime-local"
										hide-label
										required
										@update:model-value="setByDayReference()" />
								</template>
								<template v-else>
									<label for="shift-type-repetition-config-reference">
										{{ t(APP_ID, "Reference week") }}
									</label>
									<IsoWeekDateInput
										v-model="byWeekReference"
										:disabled="!!shiftType"
										input-id="shift-type-repetition-config-reference"
										class="w-full" />
								</template>
							</InputGroup>
							<template v-if="weeklyType === 'by_day'">
								<InputGroup>
									<label for="shift-type-repetition-config-time-zone">{{ t(APP_ID, "Time zone") }}</label>
									<NcTimezonePicker
										v-model="timeZone"
										:disabled="!!shiftType"
										input-id="shift-type-repetition-config-time-zone"
										@update:model-value="setByDayReference()" />
								</InputGroup>
							</template>
						</div>
						<InputGroup v-if="weeklyType === 'by_day'">
							<div>{{ t(APP_ID, "Duration") }} ({{ durationString }})</div>
							<DurationBuilder v-model="duration" :disabled="!!shiftType" class="grid grid-cols-2 sm:grid-cols-4 gap-1" />
						</InputGroup>
						<div
							class="mt-1"
							:class="{
								'grid grid-cols-3 gap-x-2 gap-y-3 sm:grid-cols-7': weeklyType === 'by_day',
							}">
							<template v-if="weeklyType === 'by_day'">
								<NcTextField
									v-for="(localDayMin, shortDay, index) in shortDayToLocalMinDayMap"
									:key="index"
									v-model.trim="shortDayToAmountMap[shortDay]"
									:disabled="!!shiftType"
									type="number"
									:label="localDayMin"
									min="0"
									required />
							</template>
							<template v-else>
								<NcTextField
									v-model.trim="byWeekAmount"
									:disabled="!!shiftType"
									class="w-28"
									type="number"
									:label="t(APP_ID, 'Amount')"
									min="1"
									required />
							</template>
						</div>
						<div>
							<ShiftTypeRepetitionSummary :repetition />
						</div>
					</div>
				</CustomFieldset>
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
import { t } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'
import { computed, inject, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcColorPicker from '@nextcloud/vue/components/NcColorPicker'
import NcDateTimePickerNative from '@nextcloud/vue/components/NcDateTimePickerNative'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcTimezonePicker from '@nextcloud/vue/components/NcTimezonePicker'
import AutoFocusTrap from './AutoFocusTrap.vue'
import CustomFieldset from './CustomFieldset.vue'
import DurationBuilder from './DurationBuilder.vue'
import InputGroup from './InputGroup.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import ShiftTypeRepetitionSummary from './ShiftTypeRepetitionSummary.vue'
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

	createInjectionKey,
	REPETITION_FREQUENCIES,
	REPETITION_WEEKLY_TYPES,
	shortDayToLocalMinDayMap,
	updateInjectionKey,
	weeklyTypeTranslations,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'
import { getIsoWeekDate, localTimeZone } from '../utils/date.ts'
import { getInitialShiftAdminGroups } from '../utils/initialState.ts'

const { shiftType = undefined } = defineProps<{ shiftType?: ShiftType }>()

const emit = defineEmits<{ close: [] }>()

const create = inject(createInjectionKey)!
const update = inject(updateInjectionKey)!

const saving = ref(false)

const dialogName = shiftType
	? t(APP_ID, 'Edit shift type')
	: t(APP_ID, 'Create shift type')

const frequencies = ref(REPETITION_FREQUENCIES)
const showColorPicker = ref(false)

const groupId = ref('')
const name = ref('')
const description = ref('')
const color = ref('#E40303')
const active = ref(true)
const frequency = ref<RepetitionFrequency>('weekly')
const interval = ref(1)
const weeklyType = ref<RepetitionWeeklyType>('by_day')
const byDayReference = ref(Temporal.Now.zonedDateTimeISO(localTimeZone).with({
	second: 0,
	millisecond: 0,
	microsecond: 0,
	nanosecond: 0,
}))
const timeZone = ref(localTimeZone)
const duration = ref(new Temporal.Duration())
const shortDayToAmountMap = ref<ShortDayToAmountMap>({
	MO: 0, TU: 0, WE: 0, TH: 0, FR: 0, SA: 0, SU: 0,
})
const byWeekReference = ref(getIsoWeekDate(undefined, false))
const byWeekAmount = ref(1)
const categories = ref('')

if (shiftType) {
	groupId.value = shiftType.group.id
	name.value = shiftType.name
	description.value = shiftType.description
	color.value = shiftType.color
	active.value = shiftType.active
	categories.value = shiftType.caldav.categories

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

const shiftAdminGroups = getInitialShiftAdminGroups()

const group = ref(shiftAdminGroups.find(({ id }) => id === groupId.value))

const byDayReferenceDate = ref<Date | null>(new Date(byDayReference.value
	.toPlainDateTime()
	.toZonedDateTime(localTimeZone)
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
		caldav: { categories: categories.value },
	}

	if (type === 'post') {
		const payload: ShiftTypePostPayload = {
			...common,
			group_id: groupId.value,
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
		.toZonedDateTimeISO(localTimeZone)
		.toPlainDateTime()
		.toZonedDateTime(timeZone.value)
}
</script>
