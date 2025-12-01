<template>
	{{ repetitionSummary }}
</template>

<script setup lang="ts">
import { n, t } from '@nextcloud/l10n'
import { computed } from 'vue'
import {
	type Repetition,

	reorderedShortDays, shortDayToLocalMinDayMap,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'

const { repetition } = defineProps<{
	repetition: Repetition
}>()

const daysWithOccurences = computed(() => {
	const entries: string[] = []
	if (repetition.weekly_type === 'by_week') {
		return entries
	}
	for (const shortDay of reorderedShortDays) {
		const amount = repetition.config.short_day_to_amount_map[shortDay]
		if (amount > 0) {
			const localDayMin = shortDayToLocalMinDayMap[shortDay]
			const entry = `${localDayMin} × ${amount}`
			entries.push(entry)
		}
	}
	return entries
})

const repetitionSummary = computed(() => {
	if (repetition.weekly_type === 'by_day') {
		if (daysWithOccurences.value.length === 0) {
			return t(APP_ID, 'Never')
		}
		return n(
			APP_ID,
			'{occurences}, every week',
			'{occurences}, every %n weeks',
			repetition.interval,
			{ occurences: daysWithOccurences.value.join(', ') },
		)
	} else {
		return n(
			APP_ID,
			'{amount} × every week',
			'{amount} × every %n weeks',
			repetition.interval,
			{ amount: repetition.config.amount },
		)
	}
})
</script>
