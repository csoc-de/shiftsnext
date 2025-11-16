<template>
	<div
		:style="{
			backgroundColor: shiftTypeWrapper.shiftType.color,
			color: contrastColor,
		}"
		class="flex items-center justify-between gap-2 rounded-nc-container p-2"
		:class="{
			underline: isSelected,
			'pointer-events-none': multiStepAction.type,
		}"
		@click.stop="onClick">
		<div class="truncate leading-[1.1]">
			{{ shiftTypeWrapper.shiftType.group.display_name }}<br>
			{{ shiftTypeWrapper.shiftType.name }}
		</div>
		<NcCounterBubble
			v-if="shiftTypeWrapper.amount > 1"
			:style="{
				backgroundColor: contrastColor,
				color: doubledContrastColor,
			}"
			:count="shiftTypeWrapper.amount" />
	</div>
</template>

<script setup lang="ts">
import { computed, inject } from 'vue'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
import {
	type ShiftTypeWrapper,

	multiStepActionIK,
	setMultiStepActionIK,
} from '../models/shiftsTable.ts'
import { getContrastColor } from '../utils/color.ts'

const { shiftTypeWrapper, columnIndex } = defineProps<{
	shiftTypeWrapper: ShiftTypeWrapper
	columnIndex: number
}>()

const multiStepAction = inject(multiStepActionIK)!
const setTableAction = inject(setMultiStepActionIK)!

const isSelected = computed(() => multiStepAction.value.type === 'creation'
	&& multiStepAction.value.columnIndex === columnIndex
	&& multiStepAction.value.shiftTypeWrapper === shiftTypeWrapper)

const contrastColor = computed(() => getContrastColor(shiftTypeWrapper.shiftType.color))
const doubledContrastColor = computed(() => getContrastColor(contrastColor.value))

/**
 * Handle click event
 */
function onClick() {
	setTableAction({ type: 'creation', columnIndex, shiftTypeWrapper })
}
</script>
