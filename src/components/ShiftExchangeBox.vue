<template>
	<div class="rounded-nc-large border border-solid border-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800"
		:class="{ 'line-through': delayBoxVisible }">
		<div class="flex justify-end pr-2 h-nc-default-clickable-area">
			<NcActions>
				<NcActionButton v-if="participant && !shiftExchange.done"
					:close-after-click="true"
					@click="() =>{
						editor = participant,
						editDialogMounted = true
					}">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit") }}
				</NcActionButton>
				<NcActionButton v-if="isGroupShiftAdmin && !shiftExchange.done"
					:close-after-click="true"
					@click="() =>{
						editor = 'admin'
						editDialogMounted = true
					}">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit as admin") }}
				</NcActionButton>
				<NcActionButton v-if="isGroupShiftAdmin || (participant && !shiftExchange.done)"
					:close-after-click="true"
					@click="delayBoxVisible = true">
					<template #icon>
						<Delete :size="20" />
					</template>
					{{ t(APP_ID, "Delete") }}
				</NcActionButton>
			</NcActions>
		</div>

		<div class="grid grid-cols-2 gap-2 px-3 pb-3">
			<div class="border-r border-t border-solid border-gray-800 pt-1 px-2">
				<div class="flex items-center justify-between gap-1">
					<span>{{ shiftExchange.user_a_approval.user?.display_name }}</span>
					<ShiftExchangeApprovedStatus v-if="approvalType !== 'admin'"
						:approved="shiftExchange.user_a_approval.approved" />
				</div>
				<div>{{ shiftExchange.shift_a.shift_type.group.display_name }}</div>
				<div>{{ shiftExchange.shift_a.shift_type.name }}</div>
				<div>
					{{ formatRange(
						[shiftExchange.shift_a.start, shiftExchange.shift_a.end],
						'toZonedDateTime' in shiftExchange.shift_a.start
							? dateOnlyFormatOptions
							: dateTimeFormatOptions
					) }}
				</div>
			</div>

			<div class="border-l border-t border-solid border-gray-800 pt-1 px-2">
				<template v-if="'shift_b' in shiftExchange">
					<div class="flex items-center justify-between gap-1">
						<span>{{ shiftExchange.user_b_approval.user?.display_name }}</span>
						<ShiftExchangeApprovedStatus v-if="approvalType !== 'admin'"
							:approved="shiftExchange.user_b_approval.approved" />
					</div>
					<div>{{ shiftExchange.shift_b.shift_type.group.display_name }}</div>
					<div>{{ shiftExchange.shift_b.shift_type.name }}</div>
					<div>
						{{ formatRange(
							[shiftExchange.shift_b.start, shiftExchange.shift_b.end],
							'toZonedDateTime' in shiftExchange.shift_b.start
								? dateOnlyFormatOptions
								: dateTimeFormatOptions
						) }}
					</div>
				</template>
				<template v-if="'transfer_to_user' in shiftExchange">
					<div class="flex items-center justify-between gap-1">
						<span>{{ shiftExchange.user_b_approval.user?.display_name }}</span>
						<ShiftExchangeApprovedStatus v-if="approvalType !== 'admin'"
							:approved="shiftExchange.user_b_approval.approved" />
					</div>
				</template>
			</div>

			<div class="mt-2 text-center col-span-2">
				<div>
					<span class="font-bold">{{ t(APP_ID, "Comment") }}: </span>
					<span>{{ shiftExchange.comment }}</span>
				</div>
				<div v-if="approvalType !== 'users'"
					class="flex items-center justify-center gap-1">
					<span class="font-bold">{{ t(APP_ID, "Admin approval") }}: </span>
					<ShiftExchangeApprovedStatus :approved="shiftExchange.admin_approval.approved" />
				</div>
			</div>
		</div>

		<DelayBox v-if="delayBoxVisible"
			@done="_remove"
			@undone="delayBoxVisible = false" />

		<EditShiftExchangeDialog v-if="editDialogMounted"
			:shift-exchange="shiftExchange"
			:editor="editor!"
			@close="editDialogMounted = false" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
import { inject, ref } from 'vue'
// @ts-expect-error no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error no types
import Pencil from 'vue-material-design-icons/Pencil.vue'
import { APP_ID } from '../appId'
import { formatRange } from '../date'
import {
	exchangeApprovalTypeIK,
	relationsIK,
	removeIK,
	type ExchangeEditor,
	type ExchangeParticipant,
	type ShiftExchange,
} from '../models/shiftExchange'
import { authUser } from '../user'
import DelayBox from './DelayBox.vue'
import EditShiftExchangeDialog from './EditShiftExchangeDialog.vue'
import ShiftExchangeApprovedStatus from './ShiftExchangeApprovedStatus.vue'

const { shiftExchange } = defineProps<{ shiftExchange: ShiftExchange }>()

const approvalType = inject(exchangeApprovalTypeIK)!
const relations = inject(relationsIK)!

const dateOnlyFormatOptions: Intl.DateTimeFormatOptions = {
	dateStyle: 'short',
}

const dateTimeFormatOptions: Intl.DateTimeFormatOptions = {
	dateStyle: 'short',
	timeStyle: 'short',
}

const remove = inject(removeIK)!

const delayBoxVisible = ref(false)
const editDialogMounted = ref(false)
const editor = ref<ExchangeEditor>()

const userAId = shiftExchange.shift_a.user.id

const userBId = 'shift_b' in shiftExchange
	? shiftExchange.shift_b.user.id
	: shiftExchange.transfer_to_user.id

const groupIds = [shiftExchange.shift_a.shift_type.group.id]
if ('shift_b' in shiftExchange) {
	groupIds.push(shiftExchange.shift_b.shift_type.group.id)
}

const participant: ExchangeParticipant | undefined
	= authUser.id === userAId
		? 'userA'
		: authUser.id === userBId
			? 'userB'
			: undefined

const isGroupShiftAdmin = groupIds.every((groupId) => {
	const relation = relations.find((relation) => relation.group.id === groupId)
	return relation?.users.some((user) => user.id === authUser.id)
})

/**
 * Remove shift exchange
 */
async function _remove(): Promise<void> {
	delayBoxVisible.value = false
	await remove(shiftExchange.id)
}
</script>
