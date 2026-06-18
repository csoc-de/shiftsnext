<template>
	<th>
		<div
			class="flex size-full flex-col gap-1 p-2"
			:class="{
				'!bg-nc-primary-element': isValidTarget,
			}"
			@click="isValidTarget && onShiftCellClick(userId)">
			<ShiftPill
				v-for="(shift, i) in shifts"
				:key="i"
				:shift="shift"
				:column-index="columnIndex" />
		</div>
	</th>
</template>

<script setup lang="ts">
import type { Shift } from '../models/shift.ts'

import { computed, inject } from 'vue'
import ShiftPill from './ShiftPill.vue'
import { multiStepActionIK, onShiftCellClickIK } from '../models/shiftsTable.ts'
import { isMember } from '../utils/groupUserRelation.ts'

const { userId, columnIndex } = defineProps<{
	userId: string
	columnIndex: number
	shifts: Shift[]
}>()

const onShiftCellClick = inject(onShiftCellClickIK)!

const multiStepAction = inject(multiStepActionIK)!

const multiStepActionGroupId = computed(() => {
	switch (multiStepAction.value.type) {
		case 'creation':
			return multiStepAction.value.shiftTypeWrapper.shiftType.group.id
		case 'motion':
			return multiStepAction.value.shift.shift_type.group.id
	}
	return undefined
})

const hasColumnFocus
	= computed(() => multiStepAction.value.columnIndex === columnIndex)

const isValidTarget = computed(() => {
	const groupId = multiStepActionGroupId.value
	if (groupId === undefined) {
		return false
	}
	return hasColumnFocus.value && isMember(groupId, userId)
})
</script>
