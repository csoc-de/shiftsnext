import type { Shift } from '../models/shift.ts'
import type { ShiftTypeWrapper } from '../models/shiftsTable.ts'
import type { ShiftType } from '../models/shiftType.ts'
import type { User } from '../models/user.ts'

import { computedAsync } from '@vueuse/core'
import { defineStore } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import {
	type MaybeRefOrGetter,

	computed, ref, toValue,
} from 'vue'
import { getShifts } from '../db/shift.ts'
import { getShiftTypes } from '../db/shiftType.ts'
import { getUsers } from '../db/user.ts'
import {
	type IsoWeekDateWithDay,
	type IsoWeekDateWithoutDay,

	getIsoWeekDate, localTimeZone,
	parseIsoWeekDate,
} from '../utils/date.ts'
import { compareShiftTypes } from '../utils/sort.ts'
import { useUserSettings } from './userSettings.ts'

export const useShiftsViewStore = defineStore('shifts-view', () => {
	const nowZdt = ref(Temporal.Now.zonedDateTimeISO(localTimeZone))

	const nowIsoWeekDateWithDay
		= computed(() => getIsoWeekDate(nowZdt.value, true))

	const nowIsoWeekDateWithoutDay
		= computed(() => getIsoWeekDate(nowZdt.value, false))

	const nowColumnIndex = ref(-1)

	const isoWeekDate = ref(nowIsoWeekDateWithoutDay.value)

	const focusedColumnIndex = ref<number>()

	const focusedShift = ref<Shift>()

	const focusedShiftTypeWrapper = ref<ShiftTypeWrapper>()

	/**
	 * Recalculates `nowZdt`
	 */
	function updateNowZdt() {
		nowZdt.value = Temporal.Now.zonedDateTimeISO(localTimeZone)
	}

	/**
	 * Sets `isoWeekDate`
	 *
	 * @param value The new ISO week date (without day)
	 */
	function setIsoWeekDate(value: MaybeRefOrGetter<IsoWeekDateWithoutDay>) {
		isoWeekDate.value = toValue(value)
	}

	const usersLoading = ref(false)
	const users = computedAsync<User[]>(
		async () => getUsers({
			group_ids: useUserSettings().selectedGroupIds,
		}),
		[],
		{ lazy: true, evaluating: usersLoading },
	)

	const shiftTypesLoading = ref(false)
	const shiftTypes = computedAsync<ShiftType[]>(
		async () => getShiftTypes({
			group_ids: useUserSettings().selectedGroupIds,
		}).then((shiftTypes) => shiftTypes.sort(compareShiftTypes)),
		[],
		{ lazy: true, evaluating: shiftTypesLoading },
	)

	const shiftsLoading = ref(false)
	const shifts = computedAsync<Shift[]>(
		async () => getShifts({
			group_ids: useUserSettings().selectedGroupIds,
			week_date: isoWeekDate.value,
		}),
		[],
		{ lazy: true, evaluating: shiftsLoading },
	)

	const loading = computed(() => {
		return usersLoading.value
			|| shiftTypesLoading.value
			|| shiftsLoading.value
	})

	const shiftTypeWrappers = computed<ShiftTypeWrapper[]>(() => {
		return []
	})

	const headerZdts = computed<Temporal.ZonedDateTime[]>(() => {
		return Array.from({ length: 7 }, (_, i) => {
			const dayOfWeek = i + 1
			const isoWeekDateForDayOfWeek: IsoWeekDateWithDay
				= `${isoWeekDate.value}-${dayOfWeek}`
			if (isoWeekDateForDayOfWeek === nowIsoWeekDateWithDay.value) {
				nowColumnIndex.value = dayOfWeek + 1
			}
			return parseIsoWeekDate(isoWeekDateForDayOfWeek)
		})
	})

	return {
		nowZdt,
		nowIsoWeekDateWithDay,
		nowIsoWeekDateWithoutDay,
		updateNowZdt,
		isoWeekDate,
		setIsoWeekDate,
		focusedColumnIndex,
		focusedShift,
		focusedShiftTypeWrapper,
	}
})
