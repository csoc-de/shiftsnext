<template>
	<div
		:style="{
			backgroundColor: shiftTypeWrapper.shiftType.color,
			color: contrastColor,
		}"
		class="flex items-center justify-between gap-1 rounded-nc-container p-2 relative"
		:class="{
			'opacity-40': !isSelected && disabled,
		}">
		<div class="max-w-48 truncate leading-[1.1]">
			<ShiftInfoPopover
				v-model:visible="showInfo"
				:shiftOrTypeWrapper="shiftTypeWrapper" />
			{{ shiftTypeWrapper.shiftType.group.display_name }}<br>
			{{ shiftTypeWrapper.shiftType.name }}
		</div>
		<div class="flex gap-1 items-center">
			<NcCounterBubble
				v-if="shiftTypeWrapper.amount > 1"
				:style="{
					backgroundColor: contrastColor,
					color: doubledContrastColor,
				}"
				:count="shiftTypeWrapper.amount" />
			<NcActions
				:disabled="disabled"
				:inline="2"
				variant="tertiary-no-background"
				class="gap-1">
				<NcActionButton
					class="!bg-transparent"
					:style="{ color: contrastColor, borderColor: contrastColor }"
					closeAfterClick
					@click.stop="showInfo = !showInfo">
					<template #icon>
						<InformationOutline :size="20" />
					</template>
					{{ t(APP_ID, 'Show info') }}
				</NcActionButton>
				<NcActionButton
					v-if="_isShiftAdmin"
					class="!bg-transparent"
					:style="{ color: contrastColor, borderColor: contrastColor }"
					closeAfterClick
					@click.stop="onAssignButtonClick">
					<template #icon>
						<ArrowAll :size="20" />
					</template>
					{{ t(APP_ID, 'Assign shift') }}
				</NcActionButton>
			</NcActions>
		</div>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { computed, ref, watch } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
// @ts-expect-error package has no types
import ArrowAll from 'vue-material-design-icons/ArrowAll.vue'
// @ts-expect-error package has no types
import InformationOutline from 'vue-material-design-icons/InformationOutline.vue'
import { injectShiftsContext } from '../views/ShiftsView.vue'
import ShiftInfoPopover from './ShiftInfoPopover.vue'
import {
	type ShiftTypeWrapper,
} from '../models/shiftsTable.ts'
import { APP_ID } from '../utils/appId.ts'
import { getContrastColor } from '../utils/color.ts'
import { isShiftAdmin } from '../utils/groupShiftAdmin.ts'

const { shiftTypeWrapper, columnIndex } = defineProps<{
	shiftTypeWrapper: ShiftTypeWrapper
	columnIndex: number
}>()

const showInfo = ref(false)

const _isShiftAdmin = isShiftAdmin(shiftTypeWrapper.shiftType.group.id)

const { multiStepAction, setMultiStepAction } = injectShiftsContext()

const isSelected = computed(() => multiStepAction.value.type === 'creation'
	&& multiStepAction.value.columnIndex === columnIndex
	&& multiStepAction.value.shiftTypeWrapper === shiftTypeWrapper)

const contrastColor = computed(() => getContrastColor(shiftTypeWrapper.shiftType.color))
const doubledContrastColor = computed(() => getContrastColor(contrastColor.value))

/**
 * Handle click event
 */
function onAssignButtonClick() {
	setMultiStepAction({ type: 'creation', columnIndex, shiftTypeWrapper })
}

const disabled = computed(() => Boolean(multiStepAction.value.type))

watch(disabled, () => showInfo.value = false)
</script>
