<template>
	<h2>{{ t(APP_ID, "App config") }}</h2>
	<form @submit.prevent="save">
		<div class="flex flex-wrap gap-4">
			<CustomFieldset>
				<template #legend>
					<span class="text-lg">{{ t(APP_ID, "Calendar") }}</span>
				</template>
				<div class="flex flex-wrap gap-4">
					<InputGroup>
						<label for="common-calendar">
							{{ t(APP_ID, "Common calendar") }}</label>
						<NcSelect v-model="commonCalendarOption"
							input-id="common-calendar"
							:options="commonCalendarOptions"
							class="min-w-64"
							@update:model-value="form.common_calendar_id = commonCalendarOption?.id" />
					</InputGroup>
					<InputGroup>
						<label for="absence-calendar">
							{{ t(APP_ID, "Absence calendar") }}</label>
						<NcSelect v-model="absenceCalendarOption"
							input-id="absence-calendar"
							:options="absenceCalendarOptions"
							class="min-w-64"
							@update:model-value="form.absence_calendar_id = absenceCalendarOption?.id" />
					</InputGroup>
					<InputGroup>
						<label for="sync-to-personal-calendar">
							{{ t(APP_ID, "Personal calendar") }}</label>
						<NcCheckboxRadioSwitch id="sync-to-personal-calendar"
							v-model="form.sync_to_personal_calendar"
							type="checkbox">
							{{ t(APP_ID, "Synchronize") }}
						</NcCheckboxRadioSwitch>
					</InputGroup>
				</div>
			</CustomFieldset>

			<CustomFieldset>
				<template #legend>
					<span class="text-lg">{{ t(APP_ID, "Shift exchanges") }}</span>
				</template>
				<InputGroup>
					<label for="exchange-approval-type">
						{{ t(APP_ID, "Required approvals") }}</label>
					<NcSelect v-model="approvalTypeOption"
						input-id="exchange-approval-type"
						:options="approvalTypeOptions"
						class="min-w-64"
						@update:model-value="form.exchange_approval_type = approvalTypeOption?.id" />
				</InputGroup>
			</CustomFieldset>
		</div>
		<NcButton class="mt-4"
			:native-type="ButtonNativeType.Submit"
			:disabled="!saveable || saving">
			{{ t(APP_ID, "Save") }}
		</NcButton>
	</form>
</template>

<script setup lang="ts">
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import NcButton, {
	ButtonNativeType,
} from '@nextcloud/vue/dist/Components/NcButton.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import { computed, ref, watch } from 'vue'
import { APP_ID } from '../appId'
import { putAppConfig } from '../db/config'
import type { Calendar, ExchangeApprovalType } from '../models/config'
import { getNcSelectCalendarOption, getNcSelectExchangeApprovalTypeOption } from '../nextcloudVue'
import { showSavedToast } from '../toast'
import CustomFieldset from './CustomFieldset.vue'
import InputGroup from './InputGroup.vue'

const saving = ref(false)

const calendars = loadState<Calendar[]>(APP_ID, 'calendars', [])
const commonCalendarOptions = calendars.map(getNcSelectCalendarOption)
const absenceCalendarOptions = computed(() =>
	commonCalendarOptions.filter(({ id }) => id !== commonCalendarOption.value?.id),
)

const approvalTypes = loadState<ExchangeApprovalType[]>(
	APP_ID,
	'exchange_approval_types',
	[],
)
const approvalTypeOptions
	= approvalTypes.map(getNcSelectExchangeApprovalTypeOption)

const initialCommonCalendar = loadState<Calendar | null>(
	APP_ID,
	'common_calendar',
	null,
)
const commonCalendarOption = ref(
	initialCommonCalendar ? getNcSelectCalendarOption(initialCommonCalendar) : undefined,
)

const initialAbsenceCalendar = loadState<Calendar | null>(
	APP_ID,
	'absence_calendar',
	null,
)
const absenceCalendarOption = ref(
	initialAbsenceCalendar ? getNcSelectCalendarOption(initialAbsenceCalendar) : undefined,
)

const syncToPersonalCalendar = ref(
	loadState<boolean>(APP_ID, 'sync_to_personal_calendar', true),
)

const initialApprovalType = loadState<ExchangeApprovalType>(
	APP_ID,
	'exchange_approval_type',
	'all',
)
const approvalTypeOption
	= ref(getNcSelectExchangeApprovalTypeOption(initialApprovalType))

watch(commonCalendarOption, () => {
	if (commonCalendarOption.value?.id === absenceCalendarOption.value?.id) {
		absenceCalendarOption.value = undefined
	}
})

const form = ref({
	common_calendar_id: commonCalendarOption.value?.id,
	absence_calendar_id: absenceCalendarOption.value?.id,
	sync_to_personal_calendar: syncToPersonalCalendar.value,
	exchange_approval_type: 'all',
})

const saveable = computed(
	() => !!(commonCalendarOption.value && absenceCalendarOption.value),
)

/**
 * Save app config
 */
async function save() {
	try {
		saving.value = true
		// @ts-expect-error checked by computed property saveable
		await putAppConfig({ values: form.value })
		showSavedToast()
	} finally {
		saving.value = false
	}
}
</script>
