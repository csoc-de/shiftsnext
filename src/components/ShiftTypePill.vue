<template>
	<div
		:style="{
			backgroundColor: shiftTypeWrapper.shiftType.color,
			color: contrastColor,
		}"
		class="flex items-center justify-between gap-1 rounded-nc-container p-2"
		:class="{
			'opacity-40': !isSelected && disabled,
		}">
		<div
			class="truncate leading-[1.1]">
			{{ shiftTypeWrapper.shiftType.group.display_name }}<br>
			{{ shiftTypeWrapper.shiftType.name }}
		</div>
		<div
			v-if="_isShiftAdmin"
			class="flex gap-1 items-center">
			<NcCounterBubble
				v-if="shiftTypeWrapper.amount > 1"
				:style="{
					backgroundColor: contrastColor,
					color: doubledContrastColor,
				}"
				:count="shiftTypeWrapper.amount" />
			<NcButton
				:disabled="disabled"
				:aria-label="t(APP_ID, 'Assign shift')"
				variant="tertiary-no-background"
				class="border"
				:style="{ color: contrastColor, borderColor: contrastColor }"
				@click.stop="onAssignButtonClick">
				<template #icon>
					<ArrowAll :size="24" />
				</template>
			</NcButton>
		</div>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { computed, inject } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCounterBubble from '@nextcloud/vue/components/NcCounterBubble'
// @ts-expect-error package has no types
import ArrowAll from 'vue-material-design-icons/ArrowAll.vue'
import {
	type ShiftTypeWrapper,

	multiStepActionIK,
	setMultiStepActionIK,
} from '../models/shiftsTable.ts'
import { APP_ID } from '../utils/appId.ts'
import { getContrastColor } from '../utils/color.ts'
import { isShiftAdmin } from '../utils/groupShiftAdmin.ts'

const { shiftTypeWrapper, columnIndex } = defineProps<{
	shiftTypeWrapper: ShiftTypeWrapper
	columnIndex: number
}>()

const _isShiftAdmin = isShiftAdmin(shiftTypeWrapper.shiftType.group.id)

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
function onAssignButtonClick() {
	setTableAction({ type: 'creation', columnIndex, shiftTypeWrapper })
}

const disabled = computed(() => Boolean(multiStepAction.value.type))
</script>
