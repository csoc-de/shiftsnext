<template>
	<tr>
		<td :class="getCellClasses(ROW_TYPE)">
			{{ user.display_name }}
		</td>
		<ShiftsTableShiftsRowCellForShifts
			v-for="(shifts, i) in shiftsMulti"
			:key="i"
			:user-id="user.id"
			:shifts="shifts"
			:column-index="i + 1"
			:class="getCellClasses(
				ROW_TYPE,
				{
					cell: i + 1,
					focus: multiStepAction.columnIndex,
					today: columnIndexOfToday,
				})" />
	</tr>
</template>

<script setup lang="ts">
import type { Shift } from '../models/shift.ts'
import type { User } from '../models/user.ts'

import { inject } from 'vue'
import ShiftsTableShiftsRowCellForShifts from './ShiftsTableShiftsRowCellForShifts.vue'
import { columnIndexOfTodayIK, getCellClasses, multiStepActionIK, ROW_TYPES } from '../models/shiftsTable.ts'

defineProps<{
	user: User
	shiftsMulti: Shift[][]
}>()

const ROW_TYPE = ROW_TYPES[2]

const multiStepAction = inject(multiStepActionIK)!

const columnIndexOfToday = inject(columnIndexOfTodayIK)!
</script>
