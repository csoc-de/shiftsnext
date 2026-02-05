<template>
	<NcDialog
		noClose
		:name="t(APP_ID, 'Edit shift exchange')">
		<form
			id="shift-exchange-form"
			class="flex flex-col gap-2"
			@submit.prevent="onSubmit">
			<InputGroup>
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
import { t } from '@nextcloud/l10n'
import { computed, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcRadioGroup from '@nextcloud/vue/components/NcRadioGroup'
import NcRadioGroupButton from '@nextcloud/vue/components/NcRadioGroupButton'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import { injectShiftExchangesContext } from '../views/ShiftExchangesView.vue'
import InputGroup from './InputGroup.vue'
import { postSynchronizeByShifts } from '../db/calendarSync.ts'
import {
	type Approved,
	type Approveds,
	type ExchangeEditor,
	type ShiftExchange,
	type ShiftExchangePatchPayload,

	APPROVED_OPTIONS,
	approvedTranslations,
} from '../models/shiftExchange.ts'
import { APP_ID } from '../utils/appId.ts'

const { shiftExchange, editor } = defineProps<{
	shiftExchange: ShiftExchange
	editor: ExchangeEditor
}>()

const emit = defineEmits<{ close: [] }>()

const { update } = injectShiftExchangesContext()

const saving = ref(false)

const approvalLabel = editor === 'admin'
	? t(APP_ID, 'Admin approval')
	: t(APP_ID, 'Participant approval')

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

switch (editor) {
	case 'userA':
		approved.value = shiftExchange.user_a_approval.approved
		break
	case 'userB':
		approved.value = shiftExchange.user_b_approval.approved
		break
	case 'admin':
		approved.value = shiftExchange.admin_approval.approved
		break
}
comment.value = shiftExchange.comment

/**
 * Handle the form submission
 */
async function onSubmit() {
	const approveds: Approveds = editor === 'admin'
		? { admin: approved.value }
		: { user: approved.value }
	const payload: ShiftExchangePatchPayload = {
		approveds,
		comment: comment.value,
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
