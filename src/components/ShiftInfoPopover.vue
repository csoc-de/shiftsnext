<template>
	<NcPopover
		no-focus-trap
		no-close-on-click-outside
		class="absolute left-1/2 bottom-0 -translate-x-1/2"
		:shown="visible">
		<div class="flex flex-col gap-2 p-2 max-w-[300px]">
			<div>
				{{ groupName }}<br>
				{{ typeName }}
			</div>
			<div>{{ formattedRange }}</div>
			<div v-if="description">
				{{ description }}
			</div>
		</div>
	</NcPopover>
</template>

<script setup lang="ts">
import type { Temporal } from 'temporal-polyfill'
import type { Shift } from '../models/shift.ts'
import type { ShiftTypeWrapper } from '../models/shiftsTable.ts'

import NcPopover from '@nextcloud/vue/components/NcPopover'
import { formatRange } from '../utils/date.ts'

const { shiftOrTypeWrapper } = defineProps<{
	shiftOrTypeWrapper: Shift | ShiftTypeWrapper
	visible: boolean
}>()

let groupName: string
let typeName: string

let start: Temporal.ZonedDateTime | Temporal.PlainDate
let end: Temporal.ZonedDateTime | Temporal.PlainDate

let description: string

if ('id' in shiftOrTypeWrapper) {
	({
		start,
		end,
		shift_type: {
			group: { display_name: groupName },
			name: typeName, description,
		},
	} = shiftOrTypeWrapper)
} else {
	({
		shiftStart: start,
		shiftEnd: end,
		shiftType: {
			group: { display_name: groupName },
			name: typeName, description,
		},
	} = shiftOrTypeWrapper)
}
const formattedRange = formatRange(
	start,
	end,
	{
		dateStyle: 'short',
		timeStyle: 'toZonedDateTime' in start ? undefined : 'short',
	},
)
</script>
