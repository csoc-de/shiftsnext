<template>
	<NcDialog
		no-close
		:name="t(APP_ID, 'Create shift exchange')"
		size="normal">
		<form id="shift-exchange-form" @submit.prevent="onSubmit">
			<div class="flex justify-center">
				<NcCheckboxRadioSwitch
					v-for="(type, index) in EXCHANGE_TYPES"
					:key="index"
					v-model="exchangeType"
					:value="type"
					button-variant
					name="exchange-type"
					type="radio"
					button-variant-grouped="horizontal">
					{{ exchangeTypeTranslations[type] }}
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
							<NcSelectUsers
								v-model="userAOption"
								required
								input-id="user-a"
								:disabled="!isShiftAdmin"
								class="w-full"
								:options="userAOptions"
								:loading="userAOptionsLoading"
								@update:model-value="loadShiftsA()" />
						</InputGroup>
						<InputGroup>
							<label for="date-a">{{ t(APP_ID, "Date") }}</label>
							<NcDateTimePickerNative
								id="date-a"
								v-model="dateA"
								required
								class="w-full"
								type="date"
								hide-label
								@change="loadShiftsA()" />
						</InputGroup>
						<InputGroup>
							<label for="shift-a"> {{ t(APP_ID, "Shift") }}</label>
							<NcSelect
								v-model="shiftAOption"
								required
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
								<NcSelectUsers
									v-model="userBOption"
									required
									input-id="user-b"
									class="w-full"
									:options="userBOptions"
									:loading="userBOptionsLoading"
									@update:model-value="loadShiftsB()" />
							</InputGroup>
							<InputGroup>
								<label for="date-b">{{ t(APP_ID, "Date") }}</label>
								<NcDateTimePickerNative
									id="date-b"
									v-model="dateB"
									required
									class="w-full"
									type="date"
									hide-label
									@change="loadShiftsB()" />
							</InputGroup>
							<InputGroup>
								<label for="shift-b"> {{ t(APP_ID, "Shift") }}</label>
								<NcSelect
									v-model="shiftBOption"
									required
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
								<NcSelectUsers
									v-model="userBOption"
									required
									input-id="user-b"
									class="w-full"
									:options="userBOptions"
									:loading="userBOptionsLoading" />
							</InputGroup>
						</template>
					</div>
				</CustomFieldset>
				<InputGroup class="col-span-2">
					<label for="comment">{{ t(APP_ID, "Comment") }}</label>
					<NcTextArea
						id="comment"
						v-model.trim="comment"
						resize="vertical"
						label-outside />
				</InputGroup>
			</div>
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
import type {
	NcSelectShiftOption,
	NcSelectUsersOption,
} from '../models/nextcloudVue.ts'

import { t } from '@nextcloud/l10n'
import { whenever } from '@vueuse/core'
import { computed, inject, ref, watch } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import NcDateTimePickerNative from '@nextcloud/vue/components/NcDateTimePickerNative'
import NcDialog from '@nextcloud/vue/components/NcDialog'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcSelectUsers from '@nextcloud/vue/components/NcSelectUsers'
import NcTextArea from '@nextcloud/vue/components/NcTextArea'
import CustomFieldset from './CustomFieldset.vue'
import InputGroup from './InputGroup.vue'
import { postSynchronizeByShifts } from '../db/calendarSync.ts'
import { getShifts } from '../db/shift.ts'
import { getUsers } from '../db/user.ts'
import {
	type ShiftExchangePostRequest,
	type ShiftExchangePostRequestBase,
	type ShiftExchangeType,

	createIK,
	EXCHANGE_TYPES,
	exchangeTypeTranslations,
} from '../models/shiftExchange.ts'
import { APP_ID } from '../utils/appId.ts'
import { getIsoCalendarDate } from '../utils/date.ts'
import { getInitialIsShiftAdmin } from '../utils/initialState.ts'
import { getNcSelectShiftOption, getNcSelectUsersOption } from '../utils/nextcloudVue.ts'
import { compare } from '../utils/sort.ts'
import { authUser } from '../utils/user.ts'

const emit = defineEmits<{ close: [] }>()

const create = inject(createIK)!

const isShiftAdmin = getInitialIsShiftAdmin()

const saving = ref(false)

const exchangeType = ref<ShiftExchangeType>('regular')

whenever(() => exchangeType.value === 'regular', loadShiftsB)

const userAOptions = ref<NcSelectUsersOption[]>([getNcSelectUsersOption(authUser)])
const userAOptionsLoading = ref(false)
const userAOption = ref<NcSelectUsersOption>(userAOptions.value[0]!)
const dateA = ref<Date>()
const shiftASelectDisabled = computed(() => !userAOption.value || !dateA.value)
const shiftAOptions = ref<NcSelectShiftOption[]>()
const shiftAOptionsLoading = ref(false)
const shiftAOption = ref<NcSelectShiftOption>()

const userBOptions = ref<NcSelectUsersOption[]>([])
const userBOptionsLoading = ref(true)
const userBOption = ref<NcSelectUsersOption>()
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

whenever(shiftASelectDisabled, () => clearShiftSelect('A'))

whenever(shiftBSelectDisabled, () => clearShiftSelect('B'))

watch(shiftAOption, () => {
	userBOptions.value = []
	userBOption.value = undefined
	shiftBOptions.value = []
	shiftBOption.value = undefined
})

const comment = ref('')

void (isShiftAdmin && loadUsersA())

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
		userAOptions.value = users.map(getNcSelectUsersOption)
	} finally {
		userAOptionsLoading.value = false
	}
}

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
		const shifts = await getShifts({
			calendar_date: getIsoCalendarDate(dateA.value),
			user_id: userAOption.value.id,
		})
		shiftAOptions.value = shifts.map(getNcSelectShiftOption)
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
			.map(getNcSelectUsersOption)
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
		const createdShiftExchange = await create(payload)
		emit('close')
		if (!createdShiftExchange.approved) {
			return
		}
		const shiftIds = [createdShiftExchange.shift_a.id]
		if ('shift_b' in createdShiftExchange) {
			shiftIds.push(createdShiftExchange.shift_b.id)
		}
		postSynchronizeByShifts({ shift_ids: shiftIds })
	} finally {
		saving.value = false
	}
}
</script>
