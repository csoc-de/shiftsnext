import { defineStore } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import { computed, ref, watch } from 'vue'
import { putDefaultGroups, putHiddenUsers } from '../db/config.ts'
import {
	type IsoWeekDateWithDay,
	type IsoWeekDateWithoutDay,

	getIsoWeekDate,
	parseIsoWeekDate,
	userTimeZone,
} from '../utils/date.ts'
import { getInitialDefaultGroups, getInitialHiddenUserIds } from '../utils/initialState.ts'
import { logger } from '../utils/logger.ts'

export type ShiftsDisplayMode = 'team-day' | 'personal-week'

const displayModeStorageKey = 'shiftsnext.shiftsDisplayMode'
const selectedDayStorageKey = 'shiftsnext.selectedIsoWeekDateWithDay'
const hideWeekendsStorageKey = 'shiftsnext.hideWeekends'
const hideOpenShiftsStorageKey = 'shiftsnext.hideOpenShifts'

/**
 * Safely reads a localStorage value.
 *
 * @param key The key to read
 */
function getLocalStorageItem(key: string): string | null {
	if (typeof window === 'undefined') {
		return null
	}
	try {
		return window.localStorage.getItem(key)
	} catch (error) {
		logger.warn(error instanceof Error ? error : String(error))
		return null
	}
}

/**
 * Safely stores a localStorage value.
 *
 * @param key The key to write
 * @param value The value to write
 */
function setLocalStorageItem(key: string, value: string): void {
	if (typeof window === 'undefined') {
		return
	}
	try {
		window.localStorage.setItem(key, value)
	} catch (error) {
		logger.warn(error instanceof Error ? error : String(error))
	}
}

export const useUserSettingsStore = defineStore('user-settings', () => {
	const selectedGroups = ref(getInitialDefaultGroups())

	const selectedGroupIds = computed(() => selectedGroups.value.map(({ id }) => id))
	const hiddenUserIds = ref(getInitialHiddenUserIds())
	const showWeeklyShifts = ref(false)

	watch(selectedGroupIds, async (groupIds) => {
		try {
			await putDefaultGroups({ group_ids: groupIds })
		} catch (error) {
			logger.warn(error instanceof Error ? error : String(error))
		}
	})

	watch(hiddenUserIds, async (userIds) => {
		try {
			await putHiddenUsers({ user_ids: userIds })
		} catch (error) {
			logger.warn(error instanceof Error ? error : String(error))
		}
	}, { deep: true })

	const now = ref(Temporal.Now.zonedDateTimeISO(userTimeZone))
	const currentIsoWeekDateWithDay = computed(() => getIsoWeekDate(now.value, true))
	const currentIsoWeekDateWithoutDay = computed(() => getIsoWeekDate(now.value, false))

	const storedDisplayMode = getLocalStorageItem(displayModeStorageKey)
	const shiftsDisplayMode = ref<ShiftsDisplayMode>(storedDisplayMode === 'personal-week' ? 'personal-week' : 'team-day')
	const storedHideWeekends = getLocalStorageItem(hideWeekendsStorageKey)
	const hideWeekends = ref(storedHideWeekends === null ? true : storedHideWeekends === 'true')
	const hideOpenShifts = ref(getLocalStorageItem(hideOpenShiftsStorageKey) === 'true')

	const isoWeekDate = ref<IsoWeekDateWithoutDay>(currentIsoWeekDateWithoutDay.value)
	const storedSelectedDay = getLocalStorageItem(selectedDayStorageKey)
	let defaultSelectedIsoWeekDateWithDay = currentIsoWeekDateWithDay.value
	if (storedSelectedDay) {
		try {
			parseIsoWeekDate(storedSelectedDay as IsoWeekDateWithDay)
			defaultSelectedIsoWeekDateWithDay = storedSelectedDay as IsoWeekDateWithDay
		} catch {
			// Ignore invalid storage values and fall back to today.
		}
	}
	const selectedIsoWeekDateWithDay = ref<IsoWeekDateWithDay>(defaultSelectedIsoWeekDateWithDay)

	/**
	 * Updates the `now` variable to the current date + time
	 */
	function updateNow() {
		now.value = Temporal.Now.zonedDateTimeISO(userTimeZone)
	}

	/**
	 * Resets the `isoWeekDate` variable back to the current ISO week after
	 * calling {@link updateNow} first
	 */
	function resetIsoWeekDate() {
		updateNow()
		isoWeekDate.value = currentIsoWeekDateWithoutDay.value
		selectedIsoWeekDateWithDay.value = currentIsoWeekDateWithDay.value
	}

	/**
	 * Sets the selected ISO week date with day.
	 *
	 * @param isoWeekDateWithDay The selected day
	 */
	function setSelectedIsoWeekDateWithDay(isoWeekDateWithDay: IsoWeekDateWithDay): void {
		selectedIsoWeekDateWithDay.value = isoWeekDateWithDay
	}

	/**
	 * Navigates the selected day by one step.
	 *
	 * @param step Day increment (-1 or +1)
	 */
	function changeSelectedDay(step: 1 | -1): void {
		let next = parseIsoWeekDate(selectedIsoWeekDateWithDay.value).add({ days: step })
		if (hideWeekends.value) {
			while (next.dayOfWeek === 6 || next.dayOfWeek === 7) {
				next = next.add({ days: step })
			}
		}
		selectedIsoWeekDateWithDay.value = getIsoWeekDate(next, true)
	}

	/**
	 * Navigates to the previous selected day.
	 */
	function decreaseSelectedDay(): void {
		changeSelectedDay(-1)
	}

	/**
	 * Navigates to the next selected day.
	 */
	function increaseSelectedDay(): void {
		changeSelectedDay(1)
	}

	watch(shiftsDisplayMode, (mode) => {
		setLocalStorageItem(displayModeStorageKey, mode)
	})

	watch(hideWeekends, (value) => {
		setLocalStorageItem(hideWeekendsStorageKey, String(value))
		if (!value) {
			return
		}
		const selectedDay = parseIsoWeekDate(selectedIsoWeekDateWithDay.value)
		if (selectedDay.dayOfWeek === 6 || selectedDay.dayOfWeek === 7) {
			selectedIsoWeekDateWithDay.value = `${isoWeekDate.value}-5` as IsoWeekDateWithDay
		}
	})

	watch(hideOpenShifts, (value) => {
		setLocalStorageItem(hideOpenShiftsStorageKey, String(value))
	})

	watch(selectedIsoWeekDateWithDay, (isoWeekDateWithDay) => {
		setLocalStorageItem(selectedDayStorageKey, isoWeekDateWithDay)
		const week = isoWeekDateWithDay.slice(0, -2) as IsoWeekDateWithoutDay
		if (isoWeekDate.value !== week) {
			isoWeekDate.value = week
		}
	})

	watch(isoWeekDate, (week) => {
		const currentDay = parseIsoWeekDate(selectedIsoWeekDateWithDay.value).dayOfWeek
		const nextIsoWeekDateWithDay = `${week}-${currentDay}` as IsoWeekDateWithDay
		if (selectedIsoWeekDateWithDay.value !== nextIsoWeekDateWithDay) {
			selectedIsoWeekDateWithDay.value = nextIsoWeekDateWithDay
		}
	})

	return {
		selectedGroups,
		selectedGroupIds,
		hiddenUserIds,
		showWeeklyShifts,
		shiftsDisplayMode,
		hideWeekends,
		hideOpenShifts,
		now,
		updateNow,
		currentIsoWeekDateWithDay,
		currentIsoWeekDateWithoutDay,
		isoWeekDate,
		selectedIsoWeekDateWithDay,
		setSelectedIsoWeekDateWithDay,
		decreaseSelectedDay,
		increaseSelectedDay,
		resetIsoWeekDate,
	}
})
