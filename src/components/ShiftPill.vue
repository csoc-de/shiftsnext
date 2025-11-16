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
		@click.stop="_isShiftAdmin && onClick()">
		<div class="truncate leading-[1.1]">
			{{ shift.shift_type.group.display_name }}<br>
			{{ shift.shift_type.name }}
		</div>
		<NcButton
			v-if="_isShiftAdmin"
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
			@finished="continueDeletion"
			@canceled="cancelDeletion" />
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
import { postSynchronizeByShifts } from '../db/calendarSync.ts'
import {
	deletionShiftIK,
	multiStepActionIK,
	onShiftDeletionAttemptIK,
	resetDeletionShiftIK,
	setDeletionShiftIK,
	setMultiStepActionIK,
} from '../models/shiftsTable.ts'
import { APP_ID } from '../utils/appId.ts'
import { getContrastColor } from '../utils/color.ts'
import { isShiftAdmin } from '../utils/groupShiftAdmin.ts'

const { shift, columnIndex } = defineProps<{
	shift: Shift
	columnIndex: number
}>()

const _isShiftAdmin = isShiftAdmin(shift.shift_type.group.id)

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
 * Toggle the delay box
 *
 * @param visible Whether the delay box should be mounted
 */
function toggleDelayBox(visible: boolean) {
	delayBoxVisible.value = visible
}

/**
 * Start the deletion of the shift
 */
function startDeletion() {
	setDeletionShift(shift)
	toggleDelayBox(true)
}

/**
 * Continue the deletion of the shift
 */
async function continueDeletion() {
	toggleDelayBox(false)
	const { id } = await onShiftDeletionAttempt(shift, columnIndex)
	postSynchronizeByShifts({ shift_ids: [id] })
}

/**
 * Cancel the deletion of the shift
 */
function cancelDeletion() {
	resetDeletionShift()
	toggleDelayBox(false)
}
</script>
