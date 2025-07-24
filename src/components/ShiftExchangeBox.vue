<template>
	<div
		class="rounded-nc-large border border-solid border-neutral-500 hover:bg-nc-primary-element-light-hover"
		:class="{ 'line-through': delayBoxVisible }">
		<div
			class="flex items-center px-2 h-nc-default-clickable-area"
			:class="[renderActions ? 'justify-between' : 'justify-center']">
			<div v-if="renderActions" class="size-nc-default-clickable-area" />
			<div>{{ exchangeTypeTranslations[exchangeType] }}</div>
			<NcActions v-if="renderActions">
				<NcActionButton
					v-if="renderEditButton"
					close-after-click
					@click="() => {
						editor = participant,
						editDialogMounted = true
					}">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit") }}
				</NcActionButton>
				<NcActionButton
					v-if="renderEditAsAdminButton"
					close-after-click
					@click="() => {
						editor = 'admin'
						editDialogMounted = true
					}">
					<template #icon>
						<Pencil :size="20" />
					</template>
					{{ t(APP_ID, "Edit as admin") }}
				</NcActionButton>
				<NcActionButton
					v-if="renderDeleteButton"
					close-after-click
					@click="delayBoxVisible = true">
					<template #icon>
						<Delete :size="20" />
					</template>
					{{ t(APP_ID, "Delete") }}
				</NcActionButton>
			</NcActions>
		</div>

		<div class="flex flex-col gap-2 px-3 pb-3">
			<div class="grid grid-cols-2 gap-2">
				<div class="border-r border-y border-solid border-gray-800 py-1 px-2">
					<div class="flex items-center justify-between gap-1">
						<span>{{ shiftExchange.user_a_approval.user?.display_name }}</span>
						<ShiftExchangeApprovedStatus
							v-if="approvalType !== 'admin'"
							:approved="shiftExchange.user_a_approval.approved" />
					</div>
					<div>{{ shiftExchange.shift_a.shift_type.group.display_name }}</div>
					<div>{{ shiftExchange.shift_a.shift_type.name }}</div>
					<div>
						{{ formatRange(
							[shiftExchange.shift_a.start, shiftExchange.shift_a.end],
							'toZonedDateTime' in shiftExchange.shift_a.start
								? dateOnlyFormatOptions
								: dateTimeFormatOptions,
						) }}
					</div>
				</div>

				<div class="border-l border-y border-solid border-gray-800 py-1 px-2">
					<template v-if="'shift_b' in shiftExchange">
						<div class="flex items-center justify-between gap-1">
							<span>{{ shiftExchange.user_b_approval.user?.display_name }}</span>
							<ShiftExchangeApprovedStatus
								v-if="approvalType !== 'admin'"
								:approved="shiftExchange.user_b_approval.approved" />
						</div>
						<div>{{ shiftExchange.shift_b.shift_type.group.display_name }}</div>
						<div>{{ shiftExchange.shift_b.shift_type.name }}</div>
						<div>
							{{ formatRange(
								[shiftExchange.shift_b.start, shiftExchange.shift_b.end],
								'toZonedDateTime' in shiftExchange.shift_b.start
									? dateOnlyFormatOptions
									: dateTimeFormatOptions,
							) }}
						</div>
					</template>
					<template v-if="'transfer_to_user' in shiftExchange">
						<div class="flex items-center justify-between gap-1">
							<span>{{ shiftExchange.user_b_approval.user?.display_name }}</span>
							<ShiftExchangeApprovedStatus
								v-if="approvalType !== 'admin'"
								:approved="shiftExchange.user_b_approval.approved" />
						</div>
					</template>
				</div>
			</div>

			<div class="flex flex-col items-center">
				<div
					v-if="approvalType !== 'users'"
					class="flex items-center gap-1">
					<span>{{ t(APP_ID, "Admin approval") }}:</span>
					<ShiftExchangeApprovedStatus :approved="shiftExchange.admin_approval.approved" />
				</div>

				<div
					v-if="shiftExchange.comment"
					class="flex items-start gap-1 max-w-full">
					<span>{{ t(APP_ID, "Comment") }}:</span>
					<pre class="overflow-auto">{{ shiftExchange.comment }}</pre>
				</div>
			</div>
		</div>

		<DelayBox
			v-if="delayBoxVisible"
			@done="_remove"
			@undone="delayBoxVisible = false" />

		<EditShiftExchangeDialog
			v-if="editDialogMounted"
			:shift-exchange="shiftExchange"
			:editor="editor!"
			@close="editDialogMounted = false" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { inject, ref } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
// @ts-expect-error package has no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error package has no types
import Pencil from 'vue-material-design-icons/Pencil.vue'
import DelayBox from './DelayBox.vue'
import EditShiftExchangeDialog from './EditShiftExchangeDialog.vue'
import ShiftExchangeApprovedStatus from './ShiftExchangeApprovedStatus.vue'
import { APP_ID } from '../appId.ts'
import { formatRange } from '../date.ts'
import {
	type ExchangeEditor,
	type ExchangeParticipant,
	type ShiftExchange,
	type ShiftExchangeType,

	exchangeApprovalTypeIK,
	exchangeTypeTranslations,
	relationsIK,
	removeIK,
} from '../models/shiftExchange.ts'
import { authUser } from '../user.ts'

const { shiftExchange } = defineProps<{ shiftExchange: ShiftExchange }>()

const exchangeType: ShiftExchangeType = 'shift_b' in shiftExchange ? 'regular' : 'transfer'

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

const userAId = shiftExchange.user_a_approval.user?.id

const userBId = shiftExchange.user_b_approval.user?.id

/** Contains always shiftA's group ID, and potentially shiftB's group ID */
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

/** Is `true` if the logged-in user is a shift admin for shiftA's and (if applicable) shiftB's group */
const isGroupShiftAdmin = groupIds.every((groupId) => {
	const relation = relations.find((relation) => relation.group.id === groupId)
	return relation?.users.some((user) => user.id === authUser.id)
})

const renderEditButton = !!(participant && !shiftExchange.done)

const renderEditAsAdminButton = isGroupShiftAdmin && !shiftExchange.done

const renderDeleteButton = !!(isGroupShiftAdmin || (participant && !shiftExchange.done))

const renderActions = renderEditButton || renderEditAsAdminButton || renderDeleteButton

/**
 * Remove shift exchange
 */
async function _remove(): Promise<void> {
	delayBoxVisible.value = false
	await remove(shiftExchange.id)
}
</script>
