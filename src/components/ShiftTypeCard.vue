<template>
	<div :style="`border-color: ${shiftType.color}`"
		class="size-full h-full transform rounded-nc-large border-8 border-solid p-4 pt-2 text-center shadow transition duration-300 hover:-translate-y-1.5 hover:shadow-xl"
		:class="{ 'line-through': deleting }">
		<div class="flex justify-between items-center">
			<NcUserStatusIcon v-if="shiftType.active"
				class="rounded-full border-2 border-solid border-white"
				status="online" />
			<NcUserStatusIcon v-else
				class="rounded-full border-2 border-solid border-white"
				status="dnd" />

			<NcActions>
				<NcActionButton :close-after-click="true"
					@click="editDialogMounted = true">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit") }}
				</NcActionButton>
				<NcActionButton :close-after-click="true"
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

		<DelayBox v-if="delayBoxVisible"
			@done="_remove"
			@undone="() => {
				deleting = false
				delayBoxVisible = false
			}" />

		<ShiftTypeDialog v-if="editDialogMounted"
			:shift-type="shiftType"
			@close="editDialogMounted = false" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcActionButton from '@nextcloud/vue/dist/Components/NcActionButton.js'
import NcActions from '@nextcloud/vue/dist/Components/NcActions.js'
import NcUserStatusIcon from '@nextcloud/vue/dist/Components/NcUserStatusIcon.js'
import { inject, ref } from 'vue'
// @ts-expect-error no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error no types
import Pencil from 'vue-material-design-icons/Pencil.vue'
import { APP_ID } from '../appId'
import { removeInjectionKey, type ShiftType } from '../models/shiftType'
import DelayBox from './DelayBox.vue'
import ShiftTypeDialog from './ShiftTypeDialog.vue'

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
