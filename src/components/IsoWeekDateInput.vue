<template>
	<div class="flex flex-wrap gap-1">
		<NcSelect v-model="year"
			class="min-w-[8.2rem]"
			:options="years"
			:clearable="false"
			:input-id="inputId"
			:label-outside="true" />
		<NcSelect v-model="week"
			class="min-w-[7.1rem]"
			:options="weeks"
			:clearable="false"
			:label-outside="true" />
	</div>
</template>

<script setup lang="ts">
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import { Temporal } from 'temporal-polyfill'
import { computed, ref, watch, watchEffect } from 'vue'
import {
	buildIsoWeekDate,
	getNumberOfWeeks,
	localTimeZone,
	parseIsoWeekDate,
	type IsoWeekDateWithoutDay,
} from '../date'

const isoWeekDate = defineModel<IsoWeekDateWithoutDay>({ required: true })

defineProps<{ inputId?: string }>()

defineExpose({ decrease, increase })

const today = Temporal.Now.zonedDateTimeISO(localTimeZone)

// Array of 11 years, from 5 years before to 5 years after the current year
const years = Array.from({ length: 11 }, (_, i) => today.yearOfWeek! - 5 + i)

const year = ref(today.yearOfWeek!)

const numberOfWeeks = computed(() => {
	return getNumberOfWeeks(year.value)
})

const weeks = computed(() =>
	Array.from({ length: numberOfWeeks.value }, (_, i) => i + 1),
)

const week = ref(today.weekOfYear!)

// It is necessary to use watch because watchEffect leads to an error if `week`
// is 53 and `year` is changed to a year with 52 weeks using the `year` NcSelect
watch(
	numberOfWeeks,
	(numberOfWeeks) => {
		if (week.value === 53 && numberOfWeeks === 52) {
			week.value = 52
		}
	},
	{ immediate: true },
)

watchEffect(() => {
	isoWeekDate.value = buildIsoWeekDate(year.value, week.value)
})

watchEffect(() => {
	const zdt = parseIsoWeekDate(`${isoWeekDate.value}-1`)
	year.value = zdt.yearOfWeek!
	week.value = zdt.weekOfYear!
})

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
