<template>
	<table class="h-fit w-full border-collapse">
		<caption>
			<h3 class="m-0">
				{{ caption }}
			</h3>
		</caption>
		<thead>
			<ShiftsTableHeadRow :zdts="headerRowData" class="h-12" />
		</thead>
		<tbody>
			<ShiftsTableShiftTypesRow :shift-type-wrappers-multi="shiftTypesRowData" />
			<ShiftsTableShiftsRow
				v-for="({ user, shiftsMulti }, index) in shiftsRowsData"
				:key="index"
				:user="user"
				:shifts-multi="shiftsMulti" />
		</tbody>
	</table>
</template>

<script setup lang="ts">
import type { Temporal } from 'temporal-polyfill'
import type { Shift } from '../models/shift.ts'
import type { ShiftTypeWrapper } from '../models/shiftsTable.ts'
import type { User } from '../models/user.ts'

import ShiftsTableHeadRow from './ShiftsTableHeaderRow.vue'
import ShiftsTableShiftsRow from './ShiftsTableShiftsRow.vue'
import ShiftsTableShiftTypesRow from './ShiftsTableShiftTypesRow.vue'

defineProps<{
	caption: string
	headerRowData: Temporal.ZonedDateTime[]
	shiftTypesRowData: ShiftTypeWrapper[][]
	shiftsRowsData: Array<{
		user: User
		shiftsMulti: Shift[][]
	}>
}>()
</script>
