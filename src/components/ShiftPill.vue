<template>
	<div
		:style="{
			backgroundColor: shift.shift_type.color,
			color: contrastColor,
		}"
		class="flex items-center justify-between gap-2 rounded-nc-container p-2"
		:class="{
			underline: isSelected,
			'line-through': deleting,
			'pointer-events-none': disabled,
		}"
		@click.stop="onClick">
		<div class="overflow-hidden text-ellipsis whitespace-nowrap leading-[1.1]">
			{{ shift.shift_type.group.display_name }}<br>
			{{ shift.shift_type.name }}
		</div>
		<NcButton
			:disabled="disabled"
			:aria-label="t(APP_ID, 'Delete shift')"
			variant="tertiary-no-background"
			:style="{ color: contrastColor }"
			@click.stop="startDeletion">
			<template #icon>
				<Delete :size="24" />
			</template>
		</NcButton>
		<DelayBox
			v-if="delayBoxVisible"
			@done="continueDeletion"
			@undone="cancelDeletion" />
	</div>
</template>

<script setup lang="ts">
import type { Shift } from '../models/shift.ts'

import { t } from '@nextcloud/l10n'
import { computed, inject, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
// @ts-expect-error package has no types
import Delete from 'vue-material-design-icons/Delete.vue'
import DelayBox from './DelayBox.vue'
import { APP_ID } from '../appId.ts'
import { getContrastColor } from '../color.ts'
import {
	deletionShiftIK,
	multiStepActionIK,
	onShiftDeletionAttemptIK,
	resetDeletionShiftIK,
	setDeletionShiftIK,
	setMultiStepActionIK,
} from '../models/shiftsTable.ts'

const { shift, columnIndex } = defineProps<{
	shift: Shift
	columnIndex: number
}>()

const multiStepAction = inject(multiStepActionIK)!
const setMultiStepAction = inject(setMultiStepActionIK)!

const deletionShift = inject(deletionShiftIK)!
const setDeletionShift = inject(setDeletionShiftIK)!
const resetDeletionShift = inject(resetDeletionShiftIK)!
const onShiftDeletionAttempt = inject(onShiftDeletionAttemptIK)!

const isSelected = computed(() => multiStepAction.value.type === 'motion'
	&& multiStepAction.value.columnIndex === columnIndex
	&& multiStepAction.value.shift === shift)

const contrastColor = computed(() => getContrastColor(shift.shift_type.color))

/**
 * Handle click event
 */
function onClick() {
	setMultiStepAction({ type: 'motion', columnIndex, shift })
}

const delayBoxVisible = ref(false)

const deleting = computed(() => deletionShift.value?.id === shift.id)

const disabled = computed(() => Boolean(multiStepAction.value.type || deleting.value))

/**
 * Toggle the undo popover
 *
 * @param visible Whether the undo popover should be visible
 */
function toggleUndoPopover(visible: boolean) {
	delayBoxVisible.value = visible
}

/**
 * Start the deletion of the shift
 */
function startDeletion() {
	setDeletionShift(shift)
	toggleUndoPopover(true)
}

/**
 * Continue the deletion of the shift
 */
function continueDeletion() {
	toggleUndoPopover(false)
	onShiftDeletionAttempt(shift, columnIndex)
}

/**
 * Cancel the deletion of the shift
 */
function cancelDeletion() {
	resetDeletionShift()
	toggleUndoPopover(false)
}
</script>
