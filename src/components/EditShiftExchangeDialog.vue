<template>
	<NcDialog
		noClose
		:name="t(APP_ID, 'Edit shift exchange')">
		<form
			id="shift-exchange-form"
			class="flex flex-col gap-2"
			@submit.prevent="onSubmit">
			<InputGroup v-if="discriminator">
				<div>{{ approvalLabel }}</div>
				<NcRadioGroup
					v-model="approvedString"
					:label="approvalLabel"
					hideLabel>
					<NcRadioGroupButton
						v-for="option in APPROVED_OPTIONS"
						:key="`${option}`"
						:value="`${option}`"
						:label="approvedTranslations[`${option}`]" />
				</NcRadioGroup>
			</InputGroup>
			<InputGroup>
				<label for="comment">{{ t(APP_ID, "Comment") }}</label>
				<NcTextArea
					id="comment"
					v-model.trim="comment"
					resize="vertical"
					labelOutside />
			</InputGroup>
			<InputGroup v-if="!discriminator && approvalType !== 'users'">
				<label for="shift-admin"> {{ t(APP_ID, "Require admin approval from") }}</label>
				<NcSelectUsers
					v-model="shiftAdminOption"
					inputId="shift-admin"
					class="w-full"
					:options="shiftAdminOptions" />
			</InputGroup>
		</form>
		<template #actions>
			<NcButton :disabled="saving" @click="emit('close')">
				{{ t(APP_ID, "Cancel") }}
			</NcButton>
			<NcButton
				:disabled="saving"
				type="submit"
				variant="primary"
				form="shift-exchange-form">
				{{ t(APP_ID, "Save") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script setup lang="ts">
import type { NcSelectUsersOption } from '../models/nextcloudVue.ts'

import { t } from '@nextcloud/l10n'
import { computed, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcRadioGroup from '@nextcloud/vue/components/NcRadioGroup'
import NcRadioGroupButton from '@nextcloud/vue/components/NcRadioGroupButton'
import NcSelectUsers from '@nextcloud/vue/components/NcSelectUsers'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import { injectShiftExchangesContext } from '../views/ShiftExchangesView.vue'
import InputGroup from './InputGroup.vue'
import { postSynchronizeByShifts } from '../db/calendarSync.ts'
import {
	type ApprovalDiscriminator,
	type Approved,
	type ShiftExchange,
	type ShiftExchangePatchPayload,

	APPROVED_OPTIONS,
	approvedTranslations,
} from '../models/shiftExchange.ts'
import { APP_ID } from '../utils/appId.ts'
import { getShiftAdmins } from '../utils/groupShiftAdmin.ts'
import { getInitialApprovalType } from '../utils/initialState.ts'
import { getNcSelectUsersOption } from '../utils/nextcloudVue.ts'

const { shiftExchange, discriminator = undefined } = defineProps<{
	shiftExchange: ShiftExchange
	/**
	 * Determines the editable approval.
	 *
	 * If not `undefined`, the corresponding `*_approval.approved` status as
	 * well as the comment can be updated.
	 *
	 * If `undefined`, only the comment as well as the user ID of the admin
	 * approval can be edited.
	 */
	discriminator?: ApprovalDiscriminator
}>()

const emit = defineEmits<{ close: [] }>()

const { update } = injectShiftExchangesContext()

const approvalType = getInitialApprovalType()

const saving = ref(false)

const approvalLabel = discriminator === 'admin'
	? t(APP_ID, 'Admin approval')
	: t(APP_ID, 'Participant approval')

const shiftAdminOptions = ref<NcSelectUsersOption[]>([])
const shiftAdminOption = ref<NcSelectUsersOption>()

if (approvalType !== 'users') {
	shiftAdminOptions.value
		= getShiftAdmins([shiftExchange.shift_a.shift_type.group.id])
			.map(getNcSelectUsersOption)
	shiftAdminOption.value
		= shiftAdminOptions.value
			.find(({ id }) => id === shiftExchange.admin_approval.user?.id)
}

const approved = ref<Approved>(null)
const comment = ref('')

const approvedString = computed<`${Approved}`>({
	get() {
		return String(approved.value) as `${Approved}`
	},
	set(value) {
		approved.value = value === 'null' ? null : value === 'true'
	},
})

if (discriminator) {
	approved.value = shiftExchange[`${discriminator}_approval`].approved
}

comment.value = shiftExchange.comment

const previousApproved = approved.value
const previousAdminApprovalUserId = shiftExchange.admin_approval.user?.id

/**
 * Handle the form submission
 */
async function onSubmit() {
	const payload: ShiftExchangePatchPayload = { comment: comment.value }
	if (discriminator && previousApproved !== approved.value) {
		payload[`${discriminator}_approval`] = { approved: approved.value }
	}
	if (
		!discriminator
		&& approvalType !== 'users'
		&& previousAdminApprovalUserId !== shiftAdminOption.value?.id
	) {
		payload.admin_approval = { user_id: shiftAdminOption.value?.id ?? null }
	}
	try {
		saving.value = true
		const updatedShiftExchange = await update(shiftExchange.id, payload)
		emit('close')
		if (!updatedShiftExchange.approved) {
			return
		}
		const shiftIds = [updatedShiftExchange.shift_a.id]
		if ('shift_b' in updatedShiftExchange) {
			shiftIds.push(updatedShiftExchange.shift_b.id)
		}
		postSynchronizeByShifts({ shift_ids: shiftIds })
	} finally {
		saving.value = false
	}
}
</script>
