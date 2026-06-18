<template>
	<tr>
		<th :class="getCellClasses(ROW_TYPE)">
			{{ t(APP_ID, 'User') }}
		</th>
		<th
			:class="getCellClasses(
				ROW_TYPE,
				{ cell: 1, focus: multiStepAction.columnIndex })">
			{{ t(APP_ID, 'Weekly shifts') }}
		</th>
		<ShiftsTableHeaderCellZdt
			v-for="(zdt, i) in zdts"
			:key="i"
			:zdt="zdt"
			:class="getCellClasses(
				ROW_TYPE,
				{
					cell: i + 2,
					focus: multiStepAction.columnIndex,
					today: columnIndexOfToday,
				})" />
	</tr>
</template>

<script setup lang="ts">
import type { Temporal } from 'temporal-polyfill'

import { t } from '@nextcloud/l10n'
import { inject } from 'vue'
import ShiftsTableHeaderCellZdt from './ShiftsTableHeaderRowCellForZdt.vue'
import { columnIndexOfTodayIK, getCellClasses, multiStepActionIK, ROW_TYPES } from '../models/shiftsTable.ts'
import { APP_ID } from '../utils/appId.ts'

defineProps<{
	zdts: Temporal.ZonedDateTime[]
}>()

const ROW_TYPE = ROW_TYPES[0]

const multiStepAction = inject(multiStepActionIK)!

const columnIndexOfToday = inject(columnIndexOfTodayIK)!
</script>
