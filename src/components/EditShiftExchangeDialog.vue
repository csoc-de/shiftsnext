<template>
	<NcDialog
		no-close
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
					hide-label>
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
					v-model.trim="formValues.comment"
					resize="vertical"
					label-outside />
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
	type ShiftExchangePutPayload,

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

type FormValues = {
	approved: boolean | null
	comment: string
}
const formValues = ref<FormValues>({
	approved: null,
	comment: '',
})

const approvedString = computed<`${Approved}`>({
	get() {
		return String(formValues.value.approved) as `${Approved}`
	},
	set(value) {
		formValues.value.approved = value === 'null' ? null : value === 'true'
	},
})

fillForm()

/**
 * Fill the form with the shift exchange data
 */
function fillForm() {
	switch (editor) {
		case 'userA':
			formValues.value.approved = shiftExchange.user_a_approval.approved
			break
		case 'userB':
			formValues.value.approved = shiftExchange.user_b_approval.approved
			break
		case 'admin':
			formValues.value.approved = shiftExchange.admin_approval.approved
			break
	}
	formValues.value.comment = shiftExchange.comment
}

/**
 * Handle the form submission
 */
async function onSubmit() {
	const approved = formValues.value.approved
	const approveds: Approveds = editor === 'admin'
		? { admin: approved }
		: { user: approved }
	const payload: ShiftExchangePutPayload = {
		approveds,
		comment: formValues.value.comment,
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
