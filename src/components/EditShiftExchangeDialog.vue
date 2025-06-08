<template>
	<NcDialog
		no-close
		:name="t(APP_ID, 'Edit shift exchange')"
		size="normal"
		content-classes="mb-2">
		<form
			id="shift-exchange-form"
			class="flex flex-col items-center gap-3"
			@submit.prevent="onSubmit">
			<span>{{ editor === 'admin' ? t(APP_ID, 'Admin approval') : t(APP_ID, 'User approval') }}</span>
			<div class="flex -mt-3">
				<NcCheckboxRadioSwitch
					v-model="approvedString"
					value="true"
					button-variant
					button-variant-grouped="horizontal"
					name="approved_by_user"
					type="radio">
					{{ t(APP_ID, "Approved") }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-model="approvedString"
					value="null"
					button-variant
					button-variant-grouped="horizontal"
					name="approved_by_user"
					type="radio">
					{{ t(APP_ID, "Pending") }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch
					v-model="approvedString"
					value="false"
					button-variant
					button-variant-grouped="horizontal"
					name="approved_by_user"
					type="radio">
					{{ t(APP_ID, "Rejected") }}
				</NcCheckboxRadioSwitch>
			</div>
			<NcTextArea
				v-model="formValues.comment"
				resize="vertical"
				:label="t(APP_ID, 'Comment')"
				placeholder="" />
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
import type { StringifiedNullableBoolean } from '../models/misc.ts'

import { t } from '@nextcloud/l10n'
import { computed, inject, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import { APP_ID } from '../appId.ts'
import {
	type Approveds,
	type ExchangeEditor,
	type ShiftExchange,
	type ShiftExchangePutRequest,

	updateIK,
} from '../models/shiftExchange.ts'

const { shiftExchange, editor } = defineProps<{
	shiftExchange: ShiftExchange
	editor: ExchangeEditor
}>()

const emit = defineEmits<{ close: [] }>()

const update = inject(updateIK)!

const saving = ref(false)

type FormValues = {
	approved: boolean | null
	comment: string
}
const formValues = ref<FormValues>({
	approved: null,
	comment: '',
})

const approvedString = computed<StringifiedNullableBoolean>({
	get() {
		return String(formValues.value.approved) as StringifiedNullableBoolean
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
	const payload: ShiftExchangePutRequest = {
		approveds,
		comment: formValues.value.comment,
	}
	try {
		saving.value = true
		await update(shiftExchange.id, payload)
		emit('close')
	} finally {
		saving.value = false
	}
}
</script>
