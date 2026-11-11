<template>
	<NcSettingsSection :name="t(APP_ID, 'App config')">
		<form @submit.prevent="save">
			<div class="flex flex-wrap gap-4">
				<CustomFieldset>
					<template #legend>
						{{ t(APP_ID, "Calendar") }}
					</template>
					<div class="flex flex-wrap gap-4 items-end">
						<NcSelect
							v-model="commonCalendarOption"
							:inputLabel="t(APP_ID, 'Common calendar')"
							:options="commonCalendarOptions"
							required
							class="min-w-64" />
						<NcSelect
							v-model="absenceCalendarOption"
							:inputLabel="t(APP_ID, 'Absence calendar')"
							:options="absenceCalendarOptions"
							required
							class="min-w-64" />
						<NcCheckboxRadioSwitch
							v-model="syncToPersonalCalendar"
							type="checkbox">
							{{ t(APP_ID, "Synchronize personal calendar") }}
						</NcCheckboxRadioSwitch>
						<NcCheckboxRadioSwitch
							v-model="ignoreAbsenceForByWeekShifts"
							type="checkbox">
							{{ t(APP_ID, "Ignore absence check for weekly shifts") }}
						</NcCheckboxRadioSwitch>
					</div>
				</CustomFieldset>

				<CustomFieldset>
					<template #legend>
						{{ t(APP_ID, "Shift exchanges") }}
					</template>
					<NcSelect
						v-model="approvalTypeOption"
						:inputLabel="t(APP_ID, 'Required approvals')"
						:options="approvalTypeOptions"
						:clearable="false"
						class="min-w-64" />
				</CustomFieldset>
			</div>
			<NcButton
				class="mt-4"
				type="submit"
				:disabled="saving">
				{{ t(APP_ID, "Save") }}
			</NcButton>
		</form>
	</NcSettingsSection>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { whenever } from '@vueuse/core'
import { computed, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSettingsSection from '@nextcloud/vue/components/NcSettingsSection'
import CustomFieldset from './CustomFieldset.vue'
import { putAppConfig } from '../db/config.ts'
import { EXCHANGE_APPROVAL_TYPES } from '../models/config.ts'
import { APP_ID } from '../utils/appId.ts'
import { getInitialAbsenceCalendar, getInitialApprovalType, getInitialCalendars, getInitialCommonCalendar, getInitialIgnoreAbsenceForByWeekShifts, getInitialSyncToPersonalCalendar } from '../utils/initialState.ts'
import { getNcSelectCalendarOption, getNcSelectExchangeApprovalTypeOption } from '../utils/nextcloudVue.ts'
import { showSavedToast } from '../utils/toast.ts'

const saving = ref(false)

const calendars = getInitialCalendars()
const commonCalendarOptions = calendars.map(getNcSelectCalendarOption)

const approvalTypeOptions
	= EXCHANGE_APPROVAL_TYPES.map(getNcSelectExchangeApprovalTypeOption)

const initialCommonCalendar = getInitialCommonCalendar()
const commonCalendarOption = ref(initialCommonCalendar ? getNcSelectCalendarOption(initialCommonCalendar) : undefined)

const absenceCalendarOptions = computed(() => commonCalendarOptions.filter(({ id }) => id !== commonCalendarOption.value?.id))

const initialAbsenceCalendar = getInitialAbsenceCalendar()
const absenceCalendarOption = ref(initialAbsenceCalendar ? getNcSelectCalendarOption(initialAbsenceCalendar) : undefined)

const syncToPersonalCalendar = ref(getInitialSyncToPersonalCalendar())

const ignoreAbsenceForByWeekShifts = ref(getInitialIgnoreAbsenceForByWeekShifts())

const initialApprovalType = getInitialApprovalType()
const approvalTypeOption
	= ref(getNcSelectExchangeApprovalTypeOption(initialApprovalType))

whenever(
	() => commonCalendarOption.value?.id === absenceCalendarOption.value?.id,
	() => {
		absenceCalendarOption.value = undefined
	},
)

/**
 * Save app config
 */
async function save() {
	try {
		saving.value = true
		await putAppConfig({ values: {
			common_calendar_id: commonCalendarOption.value!.id,
			absence_calendar_id: absenceCalendarOption.value!.id,
			sync_to_personal_calendar: syncToPersonalCalendar.value,
			ignore_absence_for_by_week_shifts: ignoreAbsenceForByWeekShifts.value,
			exchange_approval_type: approvalTypeOption.value.id,
		} })
		showSavedToast()
	} finally {
		saving.value = false
	}
}
</script>
