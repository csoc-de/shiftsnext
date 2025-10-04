<template>
	<div
		:style="{ borderColor: shiftType.color }"
		class="rounded-nc-container border-8 border-solid p-4 pt-2 text-center hover:bg-nc-hover"
		:class="{ 'line-through': deleting }">
		<div class="flex justify-between items-center">
			<NcUserStatusIcon :status="shiftType.active ? 'online' : 'dnd'" />

			<NcActions :inline="2">
				<NcActionButton
					close-after-click
					@click="editDialogMounted = true">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit") }}
				</NcActionButton>
				<NcActionButton
					close-after-click
					@click="() => {
						deleting = true
						delayBoxVisible = true
					}">
					<template #icon>
						<Delete :size="20" />
					</template>
					{{ t(APP_ID, "Delete") }}
				</NcActionButton>
			</NcActions>
		</div>

		<ul class="flex flex-col gap-1">
			<li>
				<span class="font-bold">{{ t(APP_ID, "Group") }}: </span>
				<span>{{ shiftType.group.display_name }}</span>
			</li>
			<li>
				<span class="font-bold">{{ t(APP_ID, "Name") }}: </span>
				<span>{{ shiftType.name }}</span>
			</li>
			<li>
				<span class="font-bold">{{ t(APP_ID, "Description") }}: </span>
				<span>{{ shiftType.description }}</span>
			</li>
		</ul>

		<DelayBox
			v-if="delayBoxVisible"
			@finished="_remove"
			@canceled="() => {
				deleting = false
				delayBoxVisible = false
			}" />

		<ShiftTypeDialog
			v-if="editDialogMounted"
			:shift-type="shiftType"
			@close="editDialogMounted = false" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { inject, ref } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import NcUserStatusIcon from '@nextcloud/vue/components/NcUserStatusIcon'
// @ts-expect-error package has no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error package has no types
import Pencil from 'vue-material-design-icons/Pencil.vue'
import DelayBox from './DelayBox.vue'
import ShiftTypeDialog from './ShiftTypeDialog.vue'
import { APP_ID } from '../appId.ts'
import {
	type ShiftType,

	removeInjectionKey,
} from '../models/shiftType.ts'

const { shiftType } = defineProps<{ shiftType: ShiftType }>()

const remove = inject(removeInjectionKey)!

const delayBoxVisible = ref(false)
const editDialogMounted = ref(false)

const deleting = ref(false)

/**
 * Remove shift type
 */
async function _remove(): Promise<void> {
	delayBoxVisible.value = false
	try {
		await remove(shiftType.id)
	} finally {
		deleting.value = false
	}
}
</script>
