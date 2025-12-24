<template>
	<div class="flex flex-wrap gap-2">
		<InputGroup
			:inline="inline"
			:class="{ 'items-start': !inline }">
			<label :for="_yearInputId">{{ yearLabel }}</label>
			<NcSelect
				v-model="year"
				:disabled="disabled"
				class="min-w-[8.2rem]"
				:options="years"
				:clearable="false"
				:input-id="_yearInputId"
				label-outside />
		</InputGroup>
		<InputGroup
			:inline="inline"
			:class="{ 'items-start': !inline }">
			<label :for="_weekInputId">{{ weekLabel }}</label>
			<NcSelect
				v-model="week"
				:disabled="disabled"
				class="min-w-[7.1rem]"
				:options="weeks"
				:clearable="false"
				:input-id="_weekInputId"
				label-outside />
		</InputGroup>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { watchImmediate } from '@vueuse/core'
import { Temporal } from 'temporal-polyfill'
import { computed, nextTick, ref, useId, watch } from 'vue'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import InputGroup from './InputGroup.vue'
import { APP_ID } from '../utils/appId.ts'
import {
	type IsoWeekDateWithoutDay,

	buildIsoWeekDate,
	getNumberOfWeeks,
	parseIsoWeekDate,
	userTimeZone,
} from '../utils/date.ts'

const isoWeekDate = defineModel<IsoWeekDateWithoutDay>({ required: true })

const {
	yearLabel = t(APP_ID, 'Year'),
	weekLabel = t(APP_ID, 'Week'),
	yearInputId = '',
	weekInputId = '',
	inline = false,
	disabled = false,
} = defineProps<{
	/**
	 * The label for the year input
	 *
	 * @default t(APP_ID, 'Year')
	 */
	yearLabel?: string
	/**
	 * The label for the week input
	 *
	 * @default t(APP_ID, 'Week')
	 */
	weekLabel?: string
	/**
	 * The HTML element ID for the year input
	 *
	 * @default `iso-week-date-year-${useId()}`
	 */
	yearInputId?: string
	/**
	 * The HTML element ID for the week input
	 *
	 * @default `iso-week-date-week-${useId()}`
	 */
	weekInputId?: string
	/**
	 * Whether the year and week labels and inputs should be rendered as inline
	 *
	 * @default false
	 */
	inline?: boolean
	/**
	 * Whether the year and week inputs should be disabled
	 *
	 * @default false
	 */
	disabled?: boolean
}>()

const id = useId()

const _yearInputId = computed(() => yearInputId || `iso-week-date-year-${id}`)
const _weekInputId = computed(() => weekInputId || `iso-week-date-week-${id}`)

defineExpose({ decrease, increase })

const today = Temporal.Now.zonedDateTimeISO(userTimeZone)

// Array of 11 years, from 5 years before to 5 years after the current year
const years = Array.from({ length: 11 }, (_, i) => today.yearOfWeek! - 5 + i)

const year = ref(today.yearOfWeek!)

const numberOfWeeks = computed(() => {
	return getNumberOfWeeks(year.value)
})

const weeks = computed(() => Array.from({ length: numberOfWeeks.value }, (_, i) => i + 1))

const week = ref(today.weekOfYear!)

// It is necessary to use watch because watchEffect leads to an error if `week`
// is 53 and `year` is changed to a year with 52 weeks using the `year` NcSelect
watchImmediate(
	numberOfWeeks,
	(numberOfWeeks) => {
		if (week.value === 53 && numberOfWeeks === 52) {
			week.value = 52
		}
	},
)

const { pause: pauseYearWeekWatcher, resume: resumeYearWeekWatcher } = watch([year, week], yearWeekWatcherCallback)

const { pause: pauseIsoWeekDateWatcher, resume: resumeIsoWeekDateWatcher } = watch(isoWeekDate, isoWeekDateWatcherCallback, { immediate: true })

/**
 * Callback for the year and week watcher.
 * Updates the isoWeekDate model when year or week changes.
 *
 * @param newValue - The new values of year and week as a tuple.
 */
async function yearWeekWatcherCallback(newValue: [number, number]): Promise<void> {
	const [year, week] = newValue
	pauseIsoWeekDateWatcher()
	isoWeekDate.value = buildIsoWeekDate(year, week)
	await nextTick()
	resumeIsoWeekDateWatcher()
}

/**
 * Callback for the isoWeekDate watcher.
 * Updates the year and week values when isoWeekDate changes.
 *
 * @param newValue - The new ISO week date without day.
 */
async function isoWeekDateWatcherCallback(newValue: IsoWeekDateWithoutDay): Promise<void> {
	const zdt = parseIsoWeekDate(`${newValue}-1`)
	pauseYearWeekWatcher()
	year.value = zdt.yearOfWeek!
	week.value = zdt.weekOfYear!
	await nextTick()
	resumeYearWeekWatcher()
}

/**
 * Decrease the ISO week date by one week
 */
function decrease(): void {
	if (week.value > 1) {
		week.value -= 1
		return
	}
	year.value -= 1
	week.value = numberOfWeeks.value
}

/**
 * Increase the ISO week date by one week
 */
function increase(): void {
	if (week.value < numberOfWeeks.value) {
		week.value += 1
		return
	}
	year.value += 1
	week.value = 1
}
</script>
