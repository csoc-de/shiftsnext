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
					<label for="shift-type-color">{{ t(APP_ID, "Color") }}</label>
					<NcColorPicker
						id="shift-type-color"
						v-slot="{ attrs }"
						v-model="color"
						v-model:shown="showColorPicker"
						container=".modal-mask">
						<div
							v-bind="attrs"
							:style="{ backgroundColor: color }"
							class="h-nc-clickable-area !w-full rounded-nc-element" />
					</NcColorPicker>
				</InputGroup>
				<InputGroup>
					<label for="shift-type-active">{{ t(APP_ID, "Active") }}</label>
					<NcCheckboxRadioSwitch id="shift-type-active" v-model="active" />
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
								<InputGroup>
									<label for="shift-type-duration">{{ t(APP_ID, "Duration") }}</label>
									<NcPopover @show="durationBuilderMounted = true" @after-hide="durationBuilderMounted = false">
										<template #trigger>
											<NcTextField
												id="shift-type-duration"
												v-model.trim="durationString"
												:disabled="!!shiftType"
												label-outside
												readonly
												minlength="3" />
										</template>
										<DurationBuilder v-if="durationBuilderMounted" v-model="duration" />
									</NcPopover>
								</InputGroup>
							</template>
						</div>
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
						<p>{{ repetitionSummary }}</p>
					</div>
				</CustomFieldset>
			</CustomFieldset>
		</form>
		<template #actions>
			<NcButton :disabled="saving || durationBuilderMounted" @click="emit('close')">
				{{ t(APP_ID, "Cancel") }}
			</NcButton>
			<NcButton
				:disabled="saving || durationBuilderMounted"
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

import { loadState } from '@nextcloud/initial-state'
import { getDayNamesMin, getFirstDay, n, t } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'
import { computed, inject, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcColorPicker from '@nextcloud/vue/components/NcColorPicker'
import NcDateTimePickerNative from '@nextcloud/vue/components/NcDateTimePickerNative'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcPopover from '@nextcloud/vue/components/NcPopover'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import NcTimezonePicker from '@nextcloud/vue/components/NcTimezonePicker'
import AutoFocusTrap from './AutoFocusTrap.vue'
import CustomFieldset from './CustomFieldset.vue'
import DurationBuilder from './DurationBuilder.vue'
import InputGroup from './InputGroup.vue'
import IsoWeekDateInput from './IsoWeekDateInput.vue'
import { APP_ID } from '../appId.ts'
import { rotate } from '../array.ts'
import { getIsoWeekDate, localTimeZone } from '../date.ts'
import { logger } from '../logger.ts'
import {
	type RepetitionFrequency,
	type RepetitionWeeklyType,
	type ShiftType,
	type ShiftTypePayload,
	type ShiftTypePayloadType,
	type ShiftTypePostPayload,
	type ShiftTypePutPayload,
	type ShortDay,
	type ShortDayToAmountMap,

	createInjectionKey,
	REPETITION_FREQUENCIES,
	REPETITION_WEEKLY_TYPES,
	SHORT_DAYS,
	updateInjectionKey,
	weeklyTypeTranslations,
} from '../models/shiftType.ts'

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

const durationBuilderMounted = ref(false)

const shiftAdminGroups = loadState<Group[]>(APP_ID, 'shift_admin_groups', [])

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
			repetition: {
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
			},
		}
		return payload as ShiftTypePayload<T>
	} else {
		const payload: ShiftTypePutPayload = common
		return payload as ShiftTypePayload<T>
	}
}

const firstDay = getFirstDay()
const reorderedlocalDaysMin = rotate(getDayNamesMin(), firstDay, 0)
const reorderedShortDays = rotate(SHORT_DAYS, firstDay, 0)
const shortDayLocalMinDayTuples: [ShortDay, string][] = reorderedShortDays.map((shortDay, index) => {
	let localDayMin = reorderedlocalDaysMin[index]
	if (localDayMin === undefined) {
		logger.fatal(
			'reorderedlocalDaysMin accessed with invalid index',
			{
				reorderedlocalDaysMin,
				reorderedShortDays,
				shortDay,
				index,
				localDayMin,
			},
		)
		localDayMin = 'undefined'
	}
	return [shortDay, localDayMin]
})
// @ts-expect-error Object.fromEntries doesn't infer the proper return type
const shortDayToLocalMinDayMap: Record<ShortDay, string> = Object.fromEntries(shortDayLocalMinDayTuples)

const daysWithOccurences = computed(() => {
	const entries: string[] = []
	if (weeklyType.value === 'by_week') {
		return entries
	}
	for (const shortDay of reorderedShortDays) {
		const amount = shortDayToAmountMap.value[shortDay]
		if (amount > 0) {
			const localDayMin = shortDayToLocalMinDayMap[shortDay]
			const entry = `${localDayMin} × ${amount}`
			entries.push(entry)
		}
	}
	return entries
})

const repetitionSummary = computed(() => {
	if (weeklyType.value === 'by_day') {
		if (daysWithOccurences.value.length === 0) {
			return t(APP_ID, 'Never')
		}
		return n(
			APP_ID,
			'{occurences}, every week',
			'{occurences}, every %n weeks',
			interval.value,
			{ occurences: daysWithOccurences.value.join(', ') },
		)
	} else {
		return n(
			APP_ID,
			'{amount} × every week',
			'{amount} × every %n weeks',
			interval.value,
			{ amount: byWeekAmount.value },
		)
	}
})

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
