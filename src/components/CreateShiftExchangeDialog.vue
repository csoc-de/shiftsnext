<template>
	<NcDialog :can-close="false"
		:name="t(APP_ID, 'Create shift exchange')"
		size="normal"
		content-classes="mb-2">
		<form id="shift-exchange-form" @submit.prevent="onSubmit">
			<div class="flex justify-center">
				<NcCheckboxRadioSwitch v-model="exchangeType"
					:button-variant="true"
					value="regular"
					name="exchange-type"
					type="radio"
					button-variant-grouped="horizontal">
					{{ t(APP_ID, "Regular") }}
				</NcCheckboxRadioSwitch>
				<NcCheckboxRadioSwitch v-model="exchangeType"
					:button-variant="true"
					value="transfer"
					name="exchange-type"
					type="radio"
					button-variant-grouped="horizontal">
					{{ t(APP_ID, "Transfer") }}
				</NcCheckboxRadioSwitch>
			</div>
			<div class="grid grid-cols-2 gap-4">
				<CustomFieldset class="col-span-2 md:col-span-1">
					<template #legend>
						A
					</template>
					<div class="flex flex-col gap-2">
						<InputGroup>
							<label for="user-a"> {{ t(APP_ID, "User") }}</label>
							<NcSelect v-model="userAOption"
								input-id="user-a"
								class="w-full"
								:user-select="true"
								:options="userAOptions"
								:loading="userAOptionsLoading"
								@update:model-value="loadShiftsA()" />
						</InputGroup>
						<InputGroup>
							<label for="date-a">{{ t(APP_ID, "Date") }}</label>
							<NcDateTimePickerNative id="date-a"
								v-model="dateA"
								class="w-full"
								type="date"
								:hide-label="true"
								@change="loadShiftsA()" />
						</InputGroup>
						<InputGroup>
							<label for="shift-a"> {{ t(APP_ID, "Shift") }}</label>
							<NcSelect v-model="shiftAOption"
								input-id="shift-a"
								:disabled="shiftASelectDisabled"
								class="w-full"
								:options="shiftAOptions"
								:loading="shiftAOptionsLoading"
								@update:model-value="loadUsersB" />
						</InputGroup>
					</div>
				</CustomFieldset>

				<CustomFieldset v-if="shiftAOption" class="col-span-2 md:col-span-1">
					<template #legend>
						B
					</template>
					<div class="flex flex-col gap-2">
						<template v-if="exchangeType === 'regular'">
							<InputGroup>
								<label for="user-b"> {{ t(APP_ID, "User") }}</label>
								<NcSelect v-model="userBOption"
									input-id="user-b"
									class="w-full"
									:user-select="true"
									:options="userBOptions"
									:loading="userBOptionsLoading"
									@update:model-value="loadShiftsB()" />
							</InputGroup>
							<InputGroup>
								<label for="date-b">{{ t(APP_ID, "Date") }}</label>
								<NcDateTimePickerNative id="date-b"
									v-model="dateB"
									class="w-full"
									type="date"
									:hide-label="true"
									@change="loadShiftsB()" />
							</InputGroup>
							<InputGroup>
								<label for="shift-b"> {{ t(APP_ID, "Shift") }}</label>
								<NcSelect v-model="shiftBOption"
									input-id="shift-b"
									:disabled="shiftBSelectDisabled"
									class="w-full"
									:options="shiftBOptions"
									:loading="shiftBOptionsLoading" />
							</InputGroup>
						</template>
						<template v-else>
							<InputGroup>
								<label for="user-b"> {{ t(APP_ID, "User") }}</label>
								<NcSelect v-model="userBOption"
									input-id="user-b"
									class="w-full"
									:user-select="true"
									:options="userBOptions" />
							</InputGroup>
						</template>
					</div>
				</CustomFieldset>
				<div class="col-span-2 mt-2">
					<NcTextArea v-model="comment"
						resize="vertical"
						:label="t(APP_ID, 'Comment')"
						placeholder="" />
				</div>
			</div>
		</form>

		<template #actions>
			<NcButton :disabled="saving" @click="emit('close')">
				{{ t(APP_ID, "Cancel") }}
			</NcButton>
			<NcButton :disabled="!saveable || saving"
				:native-type="ButtonNativeType.Submit"
				:type="ButtonType.Primary"
				form="shift-exchange-form">
				{{ t(APP_ID, "Save") }}
			</NcButton>
		</template>
	</NcDialog>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcButton, {
	ButtonNativeType,
	ButtonType,
} from '@nextcloud/vue/dist/Components/NcButton.js'
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import NcDateTimePickerNative from '@nextcloud/vue/dist/Components/NcDateTimePickerNative.js'
import NcDialog from '@nextcloud/vue/dist/Components/NcDialog.js'
import NcSelect from '@nextcloud/vue/dist/Components/NcSelect.js'
import NcTextArea from '@nextcloud/vue/dist/Components/NcTextArea.js'
import { computed, inject, ref, watch } from 'vue'
import { APP_ID } from '../appId'
import { getIsoCalendarDate } from '../date'
import { getShifts } from '../db/shift'
import { getUsers } from '../db/user'
import type {
	NcSelectShiftOption,
	NcSelectUserOption,
} from '../models/nextcloudVue'
import type { Shift } from '../models/shift'
import {
	createIK,
	type ShiftExchangePostRequest,
	type ShiftExchangePostRequestBase,
	type ShiftExchangeType,
} from '../models/shiftExchange'
import { getNcSelectShiftOption, getNcSelectUserOption } from '../nextcloudVue'
import { compare } from '../sort'
import { authUser } from '../user'
import CustomFieldset from './CustomFieldset.vue'
import InputGroup from './InputGroup.vue'

const create = inject(createIK)!

const emit = defineEmits<{ close: [] }>()

const saving = ref(false)

const exchangeType = ref<ShiftExchangeType>('regular')

watch(exchangeType, async (type) => {
	if (type === 'regular') {
		loadShiftsB()
	}
})

const userAOptions = ref<NcSelectUserOption[]>([])
const userAOptionsLoading = ref(true)
const userAOption = ref<NcSelectUserOption>()
const dateA = ref<Date>()
const shiftASelectDisabled = computed(() => !userAOption.value || !dateA.value)
const shiftAOptions = ref<NcSelectShiftOption[]>()
const shiftAOptionsLoading = ref(false)
const shiftAOption = ref<NcSelectShiftOption>()

const userBOptions = ref<NcSelectUserOption[]>([])
const userBOptionsLoading = ref(true)
const userBOption = ref<NcSelectUserOption>()
const dateB = ref<Date>()
const shiftBSelectDisabled = computed(() => !userBOption.value || !dateB.value)
const shiftBOptions = ref<NcSelectShiftOption[]>()
const shiftBOptionsLoading = ref(false)
const shiftBOption = ref<NcSelectShiftOption>()

/**
 * Clear shift options and selected shift
 *
 * @param target The target to clear
 */
function clearShiftSelect(target: 'A' | 'B') {
	if (target === 'A') {
		shiftAOptions.value = []
		shiftAOption.value = undefined
	} else {
		shiftBOptions.value = []
		shiftBOption.value = undefined
	}
}

watch(shiftASelectDisabled, (disabled) => {
	if (disabled) {
		clearShiftSelect('A')
	}
})

watch(shiftBSelectDisabled, (disabled) => {
	if (disabled) {
		clearShiftSelect('B')
	}
})

watch(shiftAOption, () => {
	userBOptions.value = []
	userBOption.value = undefined
	shiftBOptions.value = []
	shiftBOption.value = undefined
})

const comment = ref('')

loadUsersA()

/**
 * Load users for A
 */
async function loadUsersA(): Promise<void> {
	userAOptionsLoading.value = true
	try {
		const users = await getUsers({
			restricted: true,
		})
		const includesAuthUser = !!users.find(({ id }) => id === authUser.id)
		if (!includesAuthUser) {
			users.push(authUser)
			users.sort((a, b) => compare(a.display_name, b.display_name))
		}
		userAOptions.value = users.map(getNcSelectUserOption)
	} finally {
		userAOptionsLoading.value = false
	}
}

let shiftsA: Shift[] = []

/**
 * Load shifts for user A
 */
async function loadShiftsA(): Promise<void> {
	clearShiftSelect('A')
	if (!userAOption.value) {
		return
	}
	if (!dateA.value) {
		return
	}
	shiftAOptionsLoading.value = true
	try {
		shiftsA = await getShifts({
			calendar_date: getIsoCalendarDate(dateA.value),
			user_id: userAOption.value.id,
		})
		shiftAOptions.value = shiftsA.map(getNcSelectShiftOption)
	} finally {
		shiftAOptionsLoading.value = false
	}
}

/**
 * Load users for B
 */
async function loadUsersB(): Promise<void> {
	if (!shiftAOption.value) {
		return
	}
	userBOptionsLoading.value = true
	try {
		const users = await getUsers({
			group_ids: [shiftAOption.value.shift_type.group.id],
		})
		userBOptions.value = users
			.filter(({ id }) => id !== userAOption.value?.id)
			.map(getNcSelectUserOption)
	} finally {
		userBOptionsLoading.value = false
	}
}

/**
 * Load shifts for user B
 */
async function loadShiftsB(): Promise<void> {
	clearShiftSelect('B')
	if (!userBOption.value) {
		return
	}
	if (!dateB.value) {
		return
	}
	shiftBOptionsLoading.value = true
	try {
		const shifts = await getShifts({
			calendar_date: getIsoCalendarDate(dateB.value),
			user_id: userBOption.value.id,
		})
		shiftBOptions.value = shifts.map(getNcSelectShiftOption)
	} finally {
		shiftBOptionsLoading.value = false
	}
}

const saveable = computed(() => {
	if (!shiftAOption.value) {
		return false
	}
	switch (exchangeType.value) {
	case 'regular':
		if (!shiftBOption.value) {
			return false
		}
		break
	case 'transfer':
		if (!userBOption.value) {
			return false
		}
		break
	}
	return true
})

/**
 * Handle the form submission
 */
async function onSubmit() {
	try {
		saving.value = true
		const base: ShiftExchangePostRequestBase = {
			comment: comment.value,
			shift_a_id: shiftAOption.value!.id,
		}
		let payload: ShiftExchangePostRequest
		if (exchangeType.value === 'regular') {
			payload = {
				...base,
				shift_b_id: shiftBOption.value!.id,
			}
		} else {
			payload = {
				...base,
				transfer_to_user_id: userBOption.value!.id,
			}
		}
		await create(payload)
		emit('close')
	} finally {
		saving.value = false
	}
}
</script>
