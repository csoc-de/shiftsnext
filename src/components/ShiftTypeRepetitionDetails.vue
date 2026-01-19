<template>
	<ul class="list-disc ms-[1.1rem]">
		<li>
			{{ t(APP_ID, 'Interval') }}: {{ n(
				APP_ID,
				'Every week',
				'Every %n weeks',
				repetition.interval,
			) }}
		</li>
		<template v-if="repetition.weekly_type === 'by_day'">
			<li>
				{{ t(APP_ID, 'Shifts start at {formattedTime}', { formattedTime }) }}
			</li>
			<li>
				{{ t(APP_ID, 'Amount per day') }}: {{ daysWithAmounts }}
			</li>
			<li>
				{{ t(APP_ID, 'Duration') }}: {{ formattedDuration }}
			</li>
		</template>
		<template v-else>
			<li>
				{{ t(APP_ID, 'Shifts start on Monday and last a full week') }}
			</li>
			<li>
				{{ t(APP_ID, 'Amount per week') }}: {{ repetition.config.amount }}
			</li>
		</template>
	</ul>
</template>

<script setup lang="ts">
import { n, t } from '@nextcloud/l10n'
import { computed } from 'vue'
import {
	type Repetition,

	reorderedShortDays,
	shortDayToLocalDayMap,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'
import { formatDate, formatDuration } from '../utils/date.ts'

const { repetition } = defineProps<{
	repetition: Repetition
}>()

const daysWithAmounts = computed(() => {
	if (repetition.weekly_type === 'by_week') {
		return ''
	}
	const entries: string[] = []
	for (const shortDay of reorderedShortDays) {
		const amount = repetition.config.short_day_to_amount_map[shortDay]
		if (amount > 0) {
			const localDayMin = shortDayToLocalDayMap[shortDay]
			const entry = t(APP_ID, '{amount} × on {localDayMin}', { amount, localDayMin })
			entries.push(entry)
		}
	}
	return entries.join(', ') || t(APP_ID, 'None')
})

const formattedTime = computed(() => repetition.weekly_type === 'by_day'
	? formatDate(
		repetition.config.reference,
		{
			timeStyle: 'short',
			timeZone: repetition.config.reference.timeZoneId,
		},
	) + ' ' + repetition.config.reference.timeZoneId
	: '')

const formattedDuration = computed<string>(() => repetition.weekly_type === 'by_day'
	? formatDuration(repetition.config.duration, 'long') || t(APP_ID, 'None')
	: '')
</script>
