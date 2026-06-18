<template>
	<tr>
		<td :class="getCellClasses(ROW_TYPE)">
			{{ t(APP_ID, 'Open shifts') }}
		</td>
		<ShiftsTableShiftTypesRowCellShiftTypeWrappers
			v-for="(shiftTypeWrappers, i) in shiftTypeWrappersMulti"
			:key="i"
			:shift-type-wrappers="shiftTypeWrappers"
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
import { t } from '@nextcloud/l10n'
import { inject } from 'vue'
import ShiftsTableShiftTypesRowCellShiftTypeWrappers from './ShiftsTableShiftTypesRowCellForShiftTypeWrappers.vue'
import {
	type ShiftTypeWrapper,

	columnIndexOfTodayIK,
	getCellClasses, multiStepActionIK, ROW_TYPES,
} from '../models/shiftsTable.ts'
import { APP_ID } from '../utils/appId.ts'

defineProps<{
	shiftTypeWrappersMulti: ShiftTypeWrapper[][]
}>()

const ROW_TYPE = ROW_TYPES[1]

const multiStepAction = inject(multiStepActionIK)!

const columnIndexOfToday = inject(columnIndexOfTodayIK)!
</script>
