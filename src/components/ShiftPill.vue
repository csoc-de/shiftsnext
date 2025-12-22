<template>
	<div
		:style="{
			backgroundColor: shift.shift_type.color,
			color: contrastColor,
		}"
		class="flex items-center justify-between gap-1 rounded-nc-container p-2 relative"
		:class="{
			'opacity-40': !isSelected && disabled,
			'outline outline-2 outline-offset-2 outline-nc-error': deleting,
		}">
		<div class="truncate leading-[1.1]">
			<ShiftInfoPopover
				:shift-or-type-wrapper="shift"
				:visible="showInfo" />
			{{ shift.shift_type.group.display_name }}<br>
			{{ shift.shift_type.name }}
		</div>
		<NcActions
			:disabled="disabled"
			:inline="1"
			variant="tertiary-no-background"
			class="gap-1">
			<NcActionButton
				class="!bg-transparent"
				:style="{ color: contrastColor, borderColor: contrastColor }"
				close-after-click
				@click.stop="showInfo = !showInfo">
				<template #icon>
					<InformationOutline :size="24" />
				</template>
				{{ t(APP_ID, 'Show info') }}
			</NcActionButton>
			<NcActionButton
				v-if="_isShiftAdmin"
				class="!bg-transparent"
				:style="{ color: contrastColor, borderColor: contrastColor }"
				close-after-click
				@click.stop="onMoveButtonClick">
				<template #icon>
					<ArrowAll :size="24" />
				</template>
				{{ t(APP_ID, 'Move shift') }}
			</NcActionButton>
			<NcActionButton
				v-if="_isShiftAdmin"
				class="!bg-transparent"
				:style="{ color: contrastColor, borderColor: contrastColor }"
				close-after-click
				@click.stop="startDeletion">
				<template #icon>
					<Delete :size="24" />
				</template>
				{{ t(APP_ID, 'Delete shift') }}
			</NcActionButton>
		</NcActions>
		<DelayBox
			v-if="delayBoxVisible"
			@finished="continueDeletion"
			@canceled="cancelDeletion" />
	</div>
</template>

<script setup lang="ts">
import type { Shift } from '../models/shift.ts'

import { t } from '@nextcloud/l10n'
import { computed, ref, watch } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
// @ts-expect-error package has no types
import ArrowAll from 'vue-material-design-icons/ArrowAll.vue'
// @ts-expect-error package has no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error package has no types
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import { injectShiftsContext } from '../views/ShiftsView.vue'
import DelayBox from './DelayBox.vue'
import ShiftInfoPopover from './ShiftInfoPopover.vue'
import { postSynchronizeByShifts } from '../db/calendarSync.ts'
import { APP_ID } from '../utils/appId.ts'
import { getContrastColor } from '../utils/color.ts'
import { isShiftAdmin } from '../utils/groupShiftAdmin.ts'

const { shift, columnIndex } = defineProps<{
	shift: Shift
	columnIndex: number
}>()

const showInfo = ref(false)

const _isShiftAdmin = isShiftAdmin(shift.shift_type.group.id)

const {
	multiStepAction,
	setMultiStepAction,
	deletionShifts,
	addDeletionShift,
	removeDeletionShift,
	onShiftDeletionAttempt,
} = injectShiftsContext()

const isSelected = computed(() => multiStepAction.value.type === 'motion'
	&& multiStepAction.value.columnIndex === columnIndex
	&& multiStepAction.value.shift === shift)

const contrastColor = computed(() => getContrastColor(shift.shift_type.color))

/**
 * Handle click event
 */
function onMoveButtonClick() {
	setMultiStepAction({ type: 'motion', columnIndex, shift })
}

const delayBoxVisible = ref(false)

const deleting = computed(() => deletionShifts.value.some(({ id }) => id === shift.id))

const disabled = computed(() => Boolean(multiStepAction.value.type || deleting.value))

watch(disabled, () => showInfo.value = false)

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
	addDeletionShift(shift)
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
	removeDeletionShift(shift)
	toggleDelayBox(false)
}
</script>

<style scoped lang="scss">
:deep(.action-item__menutoggle) {
	background-color: transparent !important;
	border-color: v-bind(contrastColor) !important;
	color: v-bind(contrastColor) !important;
}
</style>
