<template>
	<div
		class="rounded-nc-container border border-solid border-nc-maxcontrast hover:bg-nc-hover"
		:class="{ 'outline outline-2 outline-offset-2 outline-nc-error': deleting }">
		<div
			class="flex items-center justify-between mx-2">
			<ShiftExchangeApprovedStatus
				class="!size-nc-clickable-area"
				:approved="shiftExchange.approved" />
			<div>{{ exchangeTypeTranslations[exchangeType] }}</div>
			<div class="size-nc-clickable-area">
				<NcActions
					:inline="2"
					class="float-right">
					<NcActionButton
						v-if="renderEditButton"
						closeAfterClick
						@click="openDialog()">
						<template #icon>
							<Pencil :size="20" />
						</template>
						{{ t(APP_ID, "Edit") }}
					</NcActionButton>
					<NcActionButton
						v-if="renderDeleteButton"
						closeAfterClick
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
		</div>

		<div class="flex flex-col gap-2 px-3 pb-3">
			<div class="grid grid-cols-2 gap-2">
				<div class="border-r border-y border-solid border-nc-maxcontrast py-1 px-2">
					<ShiftExchangeApprovedStatus
						v-if="approvalType !== 'admin'"
						class="float-right"
						:approved="shiftExchange.user_a_approval.approved"
						:isButton="renderEditUserAApprovalButton"
						@click="renderEditUserAApprovalButton && openDialog('user_a')" />
					<div>{{ shiftExchange.user_a_approval.user?.display_name }}</div>
					<div>{{ shiftExchange.shift_a.shift_type.group.display_name }}</div>
					<div>{{ shiftExchange.shift_a.shift_type.name }}</div>
					<div>
						{{ formatRange(
							shiftExchange.shift_a.start,
							shiftExchange.shift_a.end,
							'toZonedDateTime' in shiftExchange.shift_a.start
								? dateOnlyFormatOptions
								: dateTimeFormatOptions,
						) }}
					</div>
				</div>

				<div class="border-l border-y border-solid border-nc-maxcontrast py-1 px-2">
					<template v-if="'shift_b' in shiftExchange">
						<ShiftExchangeApprovedStatus
							v-if="approvalType !== 'admin'"
							class="float-right"
							:approved="shiftExchange.user_b_approval.approved"
							:isButton="renderEditUserBApprovalButton"
							@click="renderEditUserBApprovalButton && openDialog('user_b')" />
						<div>{{ shiftExchange.user_b_approval.user?.display_name }}</div>
						<div>{{ shiftExchange.shift_b.shift_type.group.display_name }}</div>
						<div>{{ shiftExchange.shift_b.shift_type.name }}</div>
						<div>
							{{ formatRange(
								shiftExchange.shift_b.start,
								shiftExchange.shift_b.end,
								'toZonedDateTime' in shiftExchange.shift_b.start
									? dateOnlyFormatOptions
									: dateTimeFormatOptions,
							) }}
						</div>
					</template>
					<template v-if="'transfer_to_user' in shiftExchange">
						<ShiftExchangeApprovedStatus
							v-if="approvalType !== 'admin'"
							class="float-right"
							:approved="shiftExchange.user_b_approval.approved"
							:isButton="renderEditUserBApprovalButton"
							@click="renderEditUserBApprovalButton && openDialog('user_b')" />
						<div>{{ shiftExchange.user_b_approval.user?.display_name }}</div>
					</template>
				</div>
			</div>

			<div class="flex flex-col items-center">
				<div
					v-if="approvalType !== 'users'"
					class="flex items-center gap-1">
					<span>{{ t(APP_ID, "Admin approval") }}:</span>
					<ShiftExchangeApprovedStatus
						:approved="shiftExchange.admin_approval.approved"
						:isButton="renderEditAdminApprovalButton"
						@click="renderEditAdminApprovalButton && openDialog('admin')" />
					<span v-if="shiftExchange.admin_approval.user">
						{{ shiftExchange.admin_approval.user.display_name }}
					</span>
				</div>

				<div
					v-if="shiftExchange.comment"
					class="flex items-start gap-1 max-w-full">
					<span>{{ t(APP_ID, "Comment") }}:</span>
					<div class="whitespace-pre-wrap break-words">
						{{ shiftExchange.comment }}
					</div>
				</div>
			</div>
		</div>

		<DelayBox
			v-if="delayBoxVisible"
			@finished="_remove"
			@canceled="() => {
				deleting = false
				delayBoxVisible = false
			}" />

		<EditShiftExchangeDialog
			v-if="editDialogMounted"
			:shiftExchange="shiftExchange"
			:discriminator="discriminator"
			@close="closeDialog()" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { ref } from 'vue'
import NcActionButton from '@nextcloud/vue/components/NcActionButton'
import NcActions from '@nextcloud/vue/components/NcActions'
// @ts-expect-error package has no types
import Delete from 'vue-material-design-icons/Delete.vue'
// @ts-expect-error package has no types
import Pencil from 'vue-material-design-icons/Pencil.vue'
import { injectShiftExchangesContext } from '../views/ShiftExchangesView.vue'
import DelayBox from './DelayBox.vue'
import EditShiftExchangeDialog from './EditShiftExchangeDialog.vue'
import ShiftExchangeApprovedStatus from './ShiftExchangeApprovedStatus.vue'
import {
	type ApprovalDiscriminator,
	type ShiftExchange,
	type ShiftExchangeType,

	exchangeTypeTranslations,
} from '../models/shiftExchange.ts'
import { APP_ID } from '../utils/appId.ts'
import { formatRange } from '../utils/date.ts'
import { isShiftAdminAll } from '../utils/groupShiftAdmin.ts'
import { getInitialApprovalType } from '../utils/initialState.ts'
import { authUser } from '../utils/user.ts'

const { shiftExchange } = defineProps<{ shiftExchange: ShiftExchange }>()

const exchangeType: ShiftExchangeType = 'shift_b' in shiftExchange ? 'regular' : 'transfer'

const approvalType = getInitialApprovalType()

const dateOnlyFormatOptions: Intl.DateTimeFormatOptions = {
	dateStyle: 'short',
}

const dateTimeFormatOptions: Intl.DateTimeFormatOptions = {
	dateStyle: 'short',
	timeStyle: 'short',
}

const { remove } = injectShiftExchangesContext()

const delayBoxVisible = ref(false)
const editDialogMounted = ref(false)

const deleting = ref(false)

const discriminator = ref<ApprovalDiscriminator>()

const isUserA = authUser.id === shiftExchange.user_a_approval.user?.id
const isUserB = authUser.id === shiftExchange.user_b_approval.user?.id

/** Contains always shiftA's group ID, and potentially shiftB's group ID */
const groupIds = [shiftExchange.shift_a.shift_type.group.id]
if ('shift_b' in shiftExchange) {
	groupIds.push(shiftExchange.shift_b.shift_type.group.id)
}

/** Is `true` if the logged-in user is a shift admin for shiftA's and (if applicable) shiftB's group */
const isGroupShiftAdmin = isShiftAdminAll(groupIds)

const renderEditUserAApprovalButton = !shiftExchange.done && isUserA
const renderEditUserBApprovalButton = !shiftExchange.done && isUserB
const renderEditAdminApprovalButton = !shiftExchange.done && isGroupShiftAdmin
const renderEditButton = !shiftExchange.done && (isUserA || isUserB || isGroupShiftAdmin)
const renderDeleteButton = (!shiftExchange.done && (isUserA || isUserB)) || isGroupShiftAdmin

/**
 * Opens the edit dialog
 *
 * @param newDiscriminator Determines the editable approval in the edit dialog.
 * If `undefined`, the dialog only allows to edit the comment as well as the
 * user ID of the admin approval.
 */
function openDialog(newDiscriminator?: ApprovalDiscriminator) {
	discriminator.value = newDiscriminator
	editDialogMounted.value = true
}

/**
 * Closes the edit dialog
 */
function closeDialog() {
	editDialogMounted.value = false
}

/**
 * Remove shift exchange
 */
async function _remove(): Promise<void> {
	delayBoxVisible.value = false
	try {
		await remove(shiftExchange.id)
	} finally {
		deleting.value = false
	}
}
</script>
