<template>
	<ContentHeader :title="t(APP_ID, 'Shifts')" :loading="loading">
		<div class="font-semibold mt-1">
			{{ dateLabel }}
		</div>
		<template #right>
			<div class="flex gap-1">
				<NcButton
					v-if="multiStepAction.type"
					:aria-label="t(APP_ID, 'Cancel')"
					variant="error"
					@click="resetMultiStepAction()">
					<template #icon>
						<Cancel :size="24" />
					</template>
				</NcButton>
				<NcButton
					v-if="isShiftAdmin"
					:disabled="synchronizing"
					:aria-label="t(APP_ID, 'Synchronize with calendar app')"
					variant="secondary"
					@click="synchronizeByGroups()">
					<template #icon>
						<CalendarSync :size="24" />
					</template>
				</NcButton>
			</div>
		</template>
	</ContentHeader>
	<div class="min-[835px]:hidden px-4 pb-2 flex flex-col gap-2">
		<div class="flex gap-1">
			<NcButton
				class="flex-1"
				:variant="shiftsDisplayMode === 'team-day' ? 'primary' : 'secondary'"
				@click="shiftsDisplayMode = 'team-day'">
				{{ t(APP_ID, "Team day") }}
			</NcButton>
			<NcButton
				class="flex-1"
				:variant="shiftsDisplayMode === 'personal-week' ? 'primary' : 'secondary'"
				@click="shiftsDisplayMode = 'personal-week'">
				{{ t(APP_ID, "My week") }}
			</NcButton>
		</div>
		<div class="flex gap-1">
			<NcButton
				:aria-label="t(APP_ID, activeDisplayMode === 'team-day' ? 'Previous day' : 'Previous week')"
				@click="decreaseMobileDateSelection()">
				<template #icon>
					<ChevronLeft :size="20" />
				</template>
			</NcButton>
			<NcButton
				:aria-label="t(APP_ID, activeDisplayMode === 'team-day' ? 'Next day' : 'Next week')"
				@click="increaseMobileDateSelection()">
				<template #icon>
					<ChevronRight :size="20" />
				</template>
			</NcButton>
			<NcButton
				class="flex-1"
				@click="resetIsoWeekDate">
				{{ t(APP_ID, "Today") }}
			</NcButton>
		</div>
		<div v-if="activeDisplayMode === 'team-day'" class="grid gap-1" :style="{ gridTemplateColumns: `repeat(${mobileDayOptions.length}, minmax(0, 1fr))` }">
			<NcButton
				v-for="dayOption in mobileDayOptions"
				:key="dayOption.value"
				class="min-w-0"
				:variant="selectedIsoWeekDateWithDay === dayOption.value ? 'primary' : 'secondary'"
				@click="setSelectedIsoWeekDateWithDay(dayOption.value)">
				{{ dayOption.label }}
			</NcButton>
		</div>
	</div>
	<PaddedContainer v-if="!loading" class="!overflow-hidden">
		<div class="max-[834px]:hidden overflow-auto h-full">
			<table class="h-fit w-full border-spacing-0">
				<thead class="sticky z-[2] top-0 bg-nc-main">
					<tr class="group">
						<th
							v-for="({ type, data }, columnIndex) in headerRow"
							:key="columnIndex"
							class="border border-solid border-nc-maxcontrast p-1 text-center group-hover:bg-nc-dark"
							:class="{
								'!bg-nc-primary-element text-nc-primary-element': columnIndexOfWeek === columnIndex,
								'!bg-nc-primary-element-light text-nc-primary-element-light': columnIndexOfToday === columnIndex,
								'border-l-0': columnIndex,
								'sticky left-0 z-[1] bg-nc-main': !columnIndex,
							}">
							<template v-if="type === 'string'">
								{{ data }}
							</template>
							<template v-else-if="type === 'week'">
								{{
									t(APP_ID, "Weekly shifts")
								}}
							</template>
							<template v-else>
								{{ formatDate(data, dayCellFormatOptions) }}
							</template>
						</th>
					</tr>
				</thead>
				<tbody class="h-full whitespace-pre-wrap">
					<tr v-if="!hideOpenShifts" class="group">
						<td
							v-for="({ type, data }, columnIndex) in shiftTypesRow"
							:key="columnIndex"
							class="border-r border-b border-solid border-nc-maxcontrast p-1 h-full group-hover:bg-nc-dark"
							:class="{
								'text-center': type === 'string',
								'border-l sticky left-0 z-[1] bg-nc-main': !columnIndex,
							}">
							<template v-if="type === 'string'">
								{{ data }}
							</template>
							<div v-else class="flex size-full flex-col gap-1">
								<template v-for="shiftTypeWrapper in data" :key="shiftTypeWrapper.shiftType.id">
									<ShiftTypePill
										v-if="
											shiftTypeWrapper.amount > 0
												&& shiftTypeWrapper.shiftType.active
										"
										:shiftTypeWrapper="shiftTypeWrapper"
										:columnIndex="columnIndex" />
								</template>
							</div>
						</td>
					</tr>
					<tr
						v-for="(shiftsRow, rowIndex) in shiftsRows"
						:key="rowIndex"
						class="group">
						<td
							v-for="({ type, data }, columnIndex) in shiftsRow"
							:key="columnIndex"
							class="border-r border-b border-solid border-nc-maxcontrast p-1 h-full group-hover:bg-nc-dark"
							:class="{
								'text-center': type === 'user',
								'!bg-nc-darker': shiftCellStatesMulti[rowIndex]?.[columnIndex] === 'disabled',
								'bg-nc-primary-element-light text-nc-primary-element-light':
									type === 'user' && data.id === authUser.id,
								'border-l sticky left-0 z-[1] bg-nc-main': !columnIndex,
							}"
							:title="getShiftCellBlockersTitle(shiftsRow[0].data.id, columnIndex)"
							@click="
								shiftCellStatesMulti[rowIndex]?.[columnIndex] === 'enabled'
									&& onShiftCellClick(shiftsRow[0].data.id)
							">
							<template v-if="type === 'user'">
								{{ data.display_name }}
							</template>
							<div
								v-else
								class="flex size-full flex-col gap-1">
								<div
									v-if="showAbsenceBlockers && getShiftCellBlockersTitle(shiftsRow[0].data.id, columnIndex)"
									class="text-xs font-semibold">
									{{ getShiftCellBlockedLabel(shiftsRow[0].data.id, columnIndex) }}
								</div>
								<ShiftPill
									v-for="shift in data"
									:key="shift.id"
									:shift="shift"
									:columnIndex="columnIndex" />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="min-[835px]:hidden overflow-auto h-full flex flex-col gap-2">
			<template v-if="headerRow && shiftTypesRow">
				<div
					v-if="!hideOpenShifts"
					class="rounded p-2"
					:class="{
						'border border-solid border-nc-maxcontrast': activeDisplayMode === 'team-week',
					}">
					<div class="font-semibold mb-2">
						{{ t(APP_ID, 'Open shifts') }}
					</div>
					<div
						v-for="({ type, data }, index) in shiftTypesRow.slice(1)"
						:key="index"
						class="rounded p-2 mb-2 last:mb-0"
						:class="{
							'border border-solid border-nc-maxcontrast': activeDisplayMode === 'team-week',
						}">
						<div
							v-if="activeDisplayMode !== 'team-day'"
							class="text-sm font-semibold mb-1">
							{{ getHeaderCellLabel(headerRow[index + 1]) }}
						</div>
						<div
							v-if="type === 'shift-types'"
							class="flex flex-col gap-1">
							<template v-for="shiftTypeWrapper in data" :key="shiftTypeWrapper.shiftType.id">
								<ShiftTypePill
									v-if="shiftTypeWrapper.amount > 0 && shiftTypeWrapper.shiftType.active"
									:shiftTypeWrapper="shiftTypeWrapper"
									:columnIndex="index + 1" />
							</template>
						</div>
					</div>
				</div>
				<div
					v-for="(shiftsRow, rowIndex) in shiftsRows"
					:key="rowIndex"
					class="rounded p-2"
					:class="{
						'border border-solid border-nc-maxcontrast': activeDisplayMode === 'team-week',
						'bg-nc-primary-element-light text-nc-primary-element-light':
							activeDisplayMode !== 'personal-week' && shiftsRow[0].data.id === authUser.id,
					}">
					<div v-if="activeDisplayMode !== 'personal-week'" class="font-semibold mb-2">
						{{ shiftsRow[0].data.display_name }}
					</div>
					<div
						v-for="({ type, data }, index) in shiftsRow.slice(1)"
						:key="index"
						class="rounded p-2 mb-2 last:mb-0"
						:class="{
							'border border-solid border-nc-maxcontrast': activeDisplayMode === 'team-week',
							'!bg-nc-darker': shiftCellStatesMulti[rowIndex]?.[index + 1] === 'disabled',
						}"
						:title="getShiftCellBlockersTitle(shiftsRow[0].data.id, index + 1)"
						@click="
							shiftCellStatesMulti[rowIndex]?.[index + 1] === 'enabled'
								&& onShiftCellClick(shiftsRow[0].data.id)
						">
						<div
							v-if="activeDisplayMode !== 'team-day'"
							class="text-sm font-semibold mb-1">
							{{ getHeaderCellLabel(headerRow[index + 1]) }}
						</div>
						<div
							v-if="type === 'shifts'"
							class="flex flex-col gap-1">
							<div
								v-if="showAbsenceBlockers && getShiftCellBlockersTitle(shiftsRow[0].data.id, index + 1)"
								class="text-xs font-semibold">
								{{ getShiftCellBlockedLabel(shiftsRow[0].data.id, index + 1) }}
							</div>
							<ShiftPill
								v-for="shift in data"
								:key="shift.id"
								:shift="shift"
								:columnIndex="index + 1" />
						</div>
					</div>
				</div>
			</template>
		</div>
	</PaddedContainer>
</template>

<script lang="ts">
import { createContext } from '../utils/createContext.ts'
import { authUser } from '../utils/user.ts'

export interface ShiftsContext {
	setMultiStepAction: (action: MultiStepAction) => void
	resetMultiStepAction: () => void
	multiStepAction: Ref<MultiStepAction>
	onShiftDeletionAttempt: (shift: Shift, columnIndex: number) => Promise<Shift>
	deletionShifts: Ref<Shift[]>
	addDeletionShift: (shift: Shift) => void
	removeDeletionShift: (shift: Shift) => void
}

export const [injectShiftsContext, provideShiftsContext]
	= createContext<ShiftsContext>('Shifts')
</script>

<script setup lang="ts">
import type { AbsenceBlocker } from '../models/calendarSync.ts'
import type { Shift, ShiftPostPayload } from '../models/shift.ts'
import type { User } from '../models/user.ts'

import { t } from '@nextcloud/l10n'
import { onKeyStroke, useWindowSize, watchImmediate } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import {
	type Ref,

	computed,
	ref,
} from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
// @ts-expect-error package has no types
import CalendarSync from 'vue-material-design-icons/CalendarSync.vue'
// @ts-expect-error package has no types
import Cancel from 'vue-material-design-icons/Cancel.vue'
// @ts-expect-error package has no types
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
// @ts-expect-error package has no types
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import ContentHeader from '../components/ContentHeader.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftPill from '../components/ShiftPill.vue'
import ShiftTypePill from '../components/ShiftTypePill.vue'
import {
	getAbsenceBlockers,
	postSynchronizeByGroups,
	postSynchronizeByShifts,
} from '../db/calendarSync.ts'
import { deleteShift, getShifts, patchShift, postShift } from '../db/shift.ts'
import { getShiftTypes } from '../db/shiftType.ts'
import { getUsers } from '../db/user.ts'
import { ShiftsRowNotFoundError, ShiftTypeWrapperNotFoundError } from '../models/error.ts'
import {
	type HeaderRow,
	type MultiStepAction,
	type ShiftCellStateConfig,
	type ShiftsDataCell,
	type ShiftsRow,
	type ShiftTypesDataCell,
	type ShiftTypesRow,
	type ShiftTypeWrapper,
	type StringCell,
	type UndefinedMultiStepAction,
	type UserCell,
	type WeekCell,
	type ZonedDateTimeDataCell,
} from '../models/shiftsTable.ts'
import {
	type ShiftType,
	type ShortDay,
	type ShortDayToAmountMap,

	SHORT_DAYS,
	shortDayToIsoDayNumberMap,
} from '../models/shiftType.ts'
import { useUserSettingsStore } from '../stores/userSettings.ts'
import { APP_ID } from '../utils/appId.ts'
import { rotate } from '../utils/array.ts'
import {
	type IsoCalendarDate,
	type IsoWeekDate,
	type IsoWeekDateWithDay,
	type IsoWeekDateWithoutDay,

	formatDate,
	formatRange,
	getIsoCalendarDate,
	getIsoWeekDate,
	getZonedDateTimeForDayOfWeek,
	parseIsoWeekDate,
	userTimeZone,
} from '../utils/date.ts'
import { isMember } from '../utils/groupUserRelation.ts'
import {
	getInitialGroups,
	getInitialIsShiftAdmin,
	getInitialShowAbsenceBlockers,
} from '../utils/initialState.ts'
import { logger } from '../utils/logger.ts'
import { compareShifts, compareShiftTypes } from '../utils/sort.ts'

const store = useUserSettingsStore()

const {
	selectedGroups,
	selectedGroupIds,
	hiddenUserIds,
	showWeeklyShifts,
	shiftsDisplayMode,
	selectedIsoWeekDateWithDay,
	hideWeekends,
	hideOpenShifts,
	currentIsoWeekDateWithDay,
	isoWeekDate,
} = storeToRefs(store)

const {
	updateNow,
	resetIsoWeekDate,
	setSelectedIsoWeekDateWithDay,
	decreaseSelectedDay,
	increaseSelectedDay,
} = store
const { width } = useWindowSize()

updateNow()

const loading = ref(true)
const synchronizing = ref(false)

const groups = ref(getInitialGroups())
const showAbsenceBlockers = getInitialShowAbsenceBlockers()
const absenceBlockersByUserAndDate = ref<Record<string, Record<string, AbsenceBlocker[]>>>({})

const isShiftAdmin = getInitialIsShiftAdmin()

const columnIndexOfWeek = computed(() => showWeeklyShifts.value ? 1 : -1)
let columnIndexOfToday = -1
const isMobileViewport = computed(() => width.value <= 834)
const activeDisplayMode = computed(() => isMobileViewport.value ? shiftsDisplayMode.value : 'team-week')
const dateLabel = computed(() => {
	if (activeDisplayMode.value === 'team-day') {
		return formatDate(parseIsoWeekDate(selectedIsoWeekDateWithDay.value), dayCellFormatOptions)
	}
	return isoWeekDate.value
})
const mobileDayOptions = computed(() => {
	const startDay = 1
	const endDay = hideWeekends.value ? 5 : 7
	const options: Array<{ value: IsoWeekDateWithDay, label: string }> = []
	for (let day = startDay; day <= endDay; day++) {
		const value = `${isoWeekDate.value}-${day}` as IsoWeekDateWithDay
		options.push({
			value,
			label: formatDate(parseIsoWeekDate(value), {
				weekday: 'short',
				day: 'numeric',
			}),
		})
	}
	return options
})

/**
 * Returns a new {@link UndefinedMultiStepAction}
 */
function getUndefinedMultiStepAction(): UndefinedMultiStepAction {
	return {
		type: undefined,
		columnIndex: -1,
	}
}

/**
 * Decreases day or week in mobile header controls.
 */
function decreaseMobileDateSelection(): void {
	if (activeDisplayMode.value === 'team-day') {
		decreaseSelectedDay()
		return
	}
	const startOfWeek = parseIsoWeekDate(`${isoWeekDate.value}-1`)
	isoWeekDate.value = getIsoWeekDate(
		startOfWeek.subtract({ weeks: 1 }),
		false,
	) as IsoWeekDateWithoutDay
}

/**
 * Increases day or week in mobile header controls.
 */
function increaseMobileDateSelection(): void {
	if (activeDisplayMode.value === 'team-day') {
		increaseSelectedDay()
		return
	}
	const startOfWeek = parseIsoWeekDate(`${isoWeekDate.value}-1`)
	isoWeekDate.value = getIsoWeekDate(
		startOfWeek.add({ weeks: 1 }),
		false,
	) as IsoWeekDateWithoutDay
}

const multiStepAction = ref<MultiStepAction>(getUndefinedMultiStepAction())

const createOrUpdateRequestPending = ref(false)

// These are non-reactive because the reactive data is stored inside
// `headerRow`, `shiftTypesRow` and `shiftsRows`
let users: User[] = []
let shifts: Shift[] = []
let shiftTypes: ShiftType[] = []

const headerRow = ref<HeaderRow>()
const shiftTypesRow = ref<ShiftTypesRow>()
const shiftsRows = ref<ShiftsRow[]>([])

const multiStepActionGroupId = computed(() => {
	if (!multiStepAction.value.type) {
		return undefined
	}
	return multiStepAction.value.type === 'creation'
		? multiStepAction.value.shiftTypeWrapper.shiftType.group.id
		: multiStepAction.value.shift.shift_type.group.id
})

const shiftCellStatesMulti = computed<ShiftCellStateConfig[][]>(() => {
	return shiftsRows.value.map((columns) => {
		return columns.map((column, columnIndex) => {
			if (column.type === 'user') {
				return 'neutral' satisfies ShiftCellStateConfig
			}
			const isColumnFocused = multiStepAction.value.columnIndex === columnIndex
			const actionGroupId = multiStepActionGroupId.value
			const isActionPending = multiStepAction.value.type !== undefined
			let isValidDropTarget = false
			if (isColumnFocused && actionGroupId !== undefined) {
				isValidDropTarget = isMember(actionGroupId, columns[0].data.id)
			}
			return (
				!createOrUpdateRequestPending.value
				&& (!isActionPending || isValidDropTarget)
					? 'enabled'
					: 'disabled'
			) satisfies ShiftCellStateConfig
		})
	})
})

watchImmediate(
	[
		isoWeekDate,
		selectedGroups,
		hiddenUserIds,
		showWeeklyShifts,
		activeDisplayMode,
		selectedIsoWeekDateWithDay,
		hideWeekends,
		hideOpenShifts,
	],
	initialize,
)

/**
 * Initializes the view
 */
function initialize(): void {
	resetMultiStepAction()
	loading.value = true
	const promises: Promise<unknown>[] = []

	const groupIds = selectedGroupIds.value

	const shiftTypesPromise = getShiftTypes({ group_ids: groupIds })
	shiftTypesPromise.then((_shiftTypes) => (shiftTypes = _shiftTypes.sort(compareShiftTypes)))
	promises.push(shiftTypesPromise)

	const usersPromise = getUsers({ group_ids: groupIds })
	usersPromise.then((_users) => {
		users = activeDisplayMode.value === 'personal-week'
			? [authUser]
			: _users.filter(({ id }) => !hiddenUserIds.value.includes(id))
	})
	promises.push(usersPromise)

	const shiftsFilters = activeDisplayMode.value === 'team-day'
		? {
				group_ids: groupIds,
				calendar_date: getIsoCalendarDate(parseIsoWeekDate(selectedIsoWeekDateWithDay.value)) as IsoCalendarDate,
			}
		: activeDisplayMode.value === 'personal-week'
			? {
					group_ids: groupIds,
					week_date: isoWeekDate.value,
					user_id: authUser.id,
				}
			: {
					group_ids: groupIds,
					week_date: isoWeekDate.value,
				}
	const shiftsPromise = getShifts(shiftsFilters)
	shiftsPromise.then((_shifts) => (shifts = _shifts))
	promises.push(shiftsPromise)

	Promise.all(promises)
		.then(async () => {
			try {
				await fetchAbsenceBlockers()
			} catch (error) {
				// Keep the shifts table usable even if blocker endpoint is unavailable
				logger.warn(error instanceof Error ? error : String(error))
			}
			buildTable()
		})
		.finally(() => (loading.value = false))
}

/**
 *
 */
async function fetchAbsenceBlockers(): Promise<void> {
	absenceBlockersByUserAndDate.value = {}
	if (!showAbsenceBlockers || users.length === 0) {
		return
	}
	const blockers = await getAbsenceBlockers(
		isoWeekDate.value,
		users.map(({ id }) => id),
	)
	const mapped: Record<string, Record<string, AbsenceBlocker[]>> = {}
	for (const blocker of blockers) {
		mapped[blocker.user_id] ??= {}
		const blockersByDate = mapped[blocker.user_id]!
		for (const blockerDate of getBlockerIsoWeekDates(blocker)) {
			blockersByDate[blockerDate] ??= []
			blockersByDate[blockerDate].push(blocker)
		}
	}
	absenceBlockersByUserAndDate.value = mapped
}

/**
 * Returns all ISO week dates affected by a blocker.
 *
 * All-day blockers from CalDAV usually have an exclusive DTEND, so we subtract
 * 1ns from positive ranges to include the last covered day correctly.
 *
 * @param blocker The blocker to evaluate
 */
function getBlockerIsoWeekDates(blocker: AbsenceBlocker): IsoWeekDate[] {
	const start = Temporal.Instant.from(blocker.start).toZonedDateTimeISO(userTimeZone)
	const rawEnd = Temporal.Instant.from(blocker.end).toZonedDateTimeISO(userTimeZone)
	const safeEnd = Temporal.ZonedDateTime.compare(rawEnd, start) > 0
		? rawEnd.subtract({ nanoseconds: 1 })
		: start
	const startDay = start.with({
		hour: 0,
		minute: 0,
		second: 0,
		millisecond: 0,
		microsecond: 0,
		nanosecond: 0,
	})
	const endDay = safeEnd.with({
		hour: 0,
		minute: 0,
		second: 0,
		millisecond: 0,
		microsecond: 0,
		nanosecond: 0,
	})
	const dates: IsoWeekDate[] = []
	let cursor = startDay
	while (Temporal.ZonedDateTime.compare(cursor, endDay) <= 0) {
		dates.push(getIsoWeekDate(cursor, true))
		cursor = cursor.add({ days: 1 })
	}
	return dates.length > 0 ? dates : [getIsoWeekDate(start, true)]
}

/**
 * Builds the table
 */
function buildTable(): void {
	setupHeaderRow()
	setupShiftTypesRow()
	setupShiftsRows()
	placeWeeklyByWeekShiftTypes()
	placeWeeklyByDayShiftTypes()
	placeShifts()
}

/**
 * Sets up the header row
 */
function setupHeaderRow(): void {
	columnIndexOfToday = -1
	const zdtsOfWeek: Temporal.ZonedDateTime[] = []
	const dayColumnOffset = showWeeklyShifts.value ? 1 : 0
	const daysOfWeek = getVisibleDaysOfWeek()
	for (const dayOfWeek of daysOfWeek) {
		const isoWeekDateForDayOfWeek: IsoWeekDateWithDay = activeDisplayMode.value === 'team-day'
			? selectedIsoWeekDateWithDay.value
			: `${isoWeekDate.value}-${dayOfWeek}`
		if (isoWeekDateForDayOfWeek === currentIsoWeekDateWithDay.value) {
			columnIndexOfToday = zdtsOfWeek.length + dayColumnOffset + 1
		}
		zdtsOfWeek.push(parseIsoWeekDate(isoWeekDateForDayOfWeek))
	}
	const userHeaderCell: StringCell = {
		type: 'string',
		data: t(APP_ID, 'User'),
	}
	const weekHeaderCell: WeekCell = {
		type: 'week',
		data: isoWeekDate.value,
	}
	const dayHeaderCells: ZonedDateTimeDataCell[] = zdtsOfWeek.map((zdt): ZonedDateTimeDataCell => ({ type: 'zoned-date-time', data: zdt }))
	headerRow.value = showWeeklyShifts.value
		? [userHeaderCell, weekHeaderCell, ...dayHeaderCells]
		: [userHeaderCell, ...dayHeaderCells]
}

/**
 * Sets up the shift types row
 */
function setupShiftTypesRow(): void {
	if (!headerRow.value) {
		throw new Error('setupHeaderRow needs to be called before')
	}
	const shiftTypesCell1: StringCell = {
		type: 'string',
		data: t(APP_ID, 'Open shifts'),
	}

	shiftTypesRow.value = [shiftTypesCell1]
	const numberOfFixedShiftTypesCells = shiftTypesRow.value.length

	const shiftTypesDataCells: ShiftTypesDataCell[] = []
	const numberOfShiftTypesDataCells
		= headerRow.value.length - numberOfFixedShiftTypesCells

	for (let i = 0; i < numberOfShiftTypesDataCells; i++) {
		shiftTypesDataCells.push({ type: 'shift-types', data: [] })
	}

	shiftTypesRow.value.push(...shiftTypesDataCells)
}

/**
 * Sets up the shifts rows
 */
function setupShiftsRows(): void {
	if (!headerRow.value) {
		throw new Error('setupHeaderRow needs to be called before')
	}
	shiftsRows.value = []
	for (const user of users) {
		const userCell: UserCell = { type: 'user', data: user }

		const shiftsCells: ShiftsRow = [userCell]
		const numberOfFixedShiftsCells = shiftsCells.length

		const shiftsDataCells: ShiftsDataCell[] = []
		const numberOfShiftsDataCells
			= headerRow.value.length - numberOfFixedShiftsCells

		for (let i = 0; i < numberOfShiftsDataCells; i++) {
			const dayHeaderCell = headerRow.value[numberOfFixedShiftsCells + i]
			const dayIsoWeekDate = dayHeaderCell?.type === 'zoned-date-time'
				? getIsoWeekDate(dayHeaderCell.data, true)
				: undefined
			const blockers = dayIsoWeekDate
				? absenceBlockersByUserAndDate.value[user.id]?.[dayIsoWeekDate] ?? []
				: []
			shiftsDataCells.push({ type: 'shifts', data: [], blockers })
		}

		shiftsCells.push(...shiftsDataCells)

		shiftsRows.value.push(shiftsCells)
	}
}

/**
 * Place weekly by week shift types into the table
 */
function placeWeeklyByWeekShiftTypes() {
	if (!shiftTypesRow.value) {
		throw new Error('setupShiftTypesRow needs to be called before')
	}
	if (!showWeeklyShifts.value) {
		return
	}
	for (const shiftType of shiftTypes) {
		const { repetition: { interval, weekly_type: weeklyType, config } }
			= shiftType
		if (weeklyType === 'by_day') {
			continue
		}

		let intervalZdt = parseIsoWeekDate(`${config.reference}-1`)
		let intervalIsoWeekDate = getIsoWeekDate(intervalZdt, false)
		while (intervalIsoWeekDate <= isoWeekDate.value) {
			if (intervalIsoWeekDate !== isoWeekDate.value) {
				intervalZdt = intervalZdt.add(Temporal.Duration.from({ weeks: interval }))
				intervalIsoWeekDate = getIsoWeekDate(intervalZdt, false)
				continue
			}
			const { amount } = config
			const columnIndex = getColumnIndex(intervalIsoWeekDate)
			const cell = shiftTypesRow.value[columnIndex]
			if (cell?.type !== 'shift-types') {
				continue
			}
			const shiftTypeWrapper: ShiftTypeWrapper = {
				shiftType,
				weeklyType: 'by_week',
				shiftStart: intervalZdt.toPlainDate(),
				shiftEnd: intervalZdt.add({ days: 6 }).toPlainDate(),
				amount,
			}
			cell.data.push(shiftTypeWrapper)
			break
		}
	}
}

/**
 * Place weekly by day shift types into the table
 */
function placeWeeklyByDayShiftTypes() {
	if (!shiftTypesRow.value) {
		throw new Error('setupShiftTypesRow needs to be called before')
	}
	for (const shiftType of shiftTypes) {
		const { repetition: { interval, weekly_type: weeklyType, config } }
			= shiftType
		if (weeklyType === 'by_week') {
			continue
		}

		const {
			reference,
			short_day_to_amount_map: shortDayToAmountMap,
			duration,
		} = config

		let intervalZdt = reference.withTimeZone(userTimeZone)
		let intervalIsoWeekDate = getIsoWeekDate(intervalZdt, false)
		while (intervalIsoWeekDate <= isoWeekDate.value) {
			if (intervalIsoWeekDate !== isoWeekDate.value) {
				intervalZdt = intervalZdt.add(Temporal.Duration.from({ weeks: interval }))
				intervalIsoWeekDate = getIsoWeekDate(intervalZdt, false)
				continue
			}
			/** Monday on array index 0, sunday on 6 */
			const orderedShortDays = rotate(SHORT_DAYS, 1, 0)
			/** Monday data first, Sunday data last */
			const orderedShortDayToAmountMap = Object.fromEntries(orderedShortDays.map((shortDay) => [shortDay, shortDayToAmountMap[shortDay]])) as ShortDayToAmountMap

			for (const SD of Object.keys(orderedShortDayToAmountMap)) {
				const shortDay = SD as ShortDay
				const isoDayNumber = shortDayToIsoDayNumberMap[shortDay]
				const zdtOfLocalWeekDay = getZonedDateTimeForDayOfWeek(
					isoDayNumber,
					intervalZdt,
				)
				/**
				 * This is -1, if we are still before the reference, meaning
				 * the earliest possible occurence of this shift type has to be
				 * on one of the later days of the week
				 */
				const comparisonResult = Temporal.ZonedDateTime.compare(
					zdtOfLocalWeekDay,
					reference,
				)

				if (comparisonResult === -1) {
					continue
				}
				const zdtOfLocalWeekDayInReferenceTimeZone
					= zdtOfLocalWeekDay.withTimeZone(reference)
				const dayOfWeekInReferenceTimeZone
					= zdtOfLocalWeekDayInReferenceTimeZone.dayOfWeek
				const shortDayIndexInReferenceTimeZone
					= dayOfWeekInReferenceTimeZone - 1
				const shortDayInReferenceTimeZone
					= orderedShortDays[shortDayIndexInReferenceTimeZone]
				if (shortDayInReferenceTimeZone === undefined) {
					logger.fatal(
						'orderedShortDays accessed with invalid index',
						{
							shiftType,
							intervalZdt,
							orderedShortDays,
							orderedShortDayToAmountMap,
							shortDay,
							isoDayNumber,
							zdtOfLocalWeekDay,
							zdtOfLocalWeekDayInReferenceTimeZone,
							dayOfWeekInReferenceTimeZone,
							shortDayIndexInReferenceTimeZone,
							shortDayInReferenceTimeZone,
						},
					)
					continue
				}
				const amountInReferenceTimeZone
					= orderedShortDayToAmountMap[shortDayInReferenceTimeZone]

				const isoWeekDate = getIsoWeekDate(zdtOfLocalWeekDay, true)
				const columnIndex = getColumnIndex(isoWeekDate)
				const cell = shiftTypesRow.value[columnIndex]
				if (cell?.type !== 'shift-types') {
					continue
				}
				const shiftTypeWrapper: ShiftTypeWrapper = {
					shiftType,
					weeklyType: 'by_day',
					shiftStart: zdtOfLocalWeekDay,
					shiftEnd: zdtOfLocalWeekDay.add(duration),
					amount: amountInReferenceTimeZone,
				}
				cell.data.push(shiftTypeWrapper)
			}
			break
		}
	}
}

/**
 * Places the shifts
 */
function placeShifts(): void {
	for (const shift of shifts) {
		if (
			!showWeeklyShifts.value
			&& shift.shift_type.repetition.weekly_type === 'by_week'
		) {
			continue
		}
		const { start, shift_type: { id: shiftTypeId } } = shift
		const isoWeekDate = getIsoWeekDate(
			start,
			shift.shift_type.repetition.weekly_type === 'by_day',
		)
		const columnIndex = getColumnIndex(isoWeekDate)
		if (columnIndex < 0) {
			continue
		}
		try {
			placeShift(shift, columnIndex, false)
		} catch (error) {
			if (!(error instanceof ShiftsRowNotFoundError)) {
				continue
			}
			logger.warn(error)
		}
		try {
			const shiftTypeWrapper = getShiftTypeWrapper(shiftTypeId, columnIndex)
			shiftTypeWrapper.amount--
		} catch (error) {
			if (!(error instanceof ShiftTypeWrapperNotFoundError)) {
				throw error
			}
			logger.warn(error)
		}
	}
}

/**
 * Returns visible day numbers for the current display mode.
 */
function getVisibleDaysOfWeek(): number[] {
	if (activeDisplayMode.value === 'team-day') {
		return [parseIsoWeekDate(selectedIsoWeekDateWithDay.value).dayOfWeek]
	}
	return hideWeekends.value ? [1, 2, 3, 4, 5] : [1, 2, 3, 4, 5, 6, 7]
}

/**
 * Returns the column index for the given `isoWeekDate`
 *
 * @param isoWeekDate The ISO week date
 */
function getColumnIndex(isoWeekDate: IsoWeekDate): number {
	if (!headerRow.value) {
		throw new Error('setupHeaderRow needs to be called before')
	}
	return headerRow.value.findIndex((cell) => {
		if (cell.type === 'string') {
			return false
		}
		const isoWeekDateOfCell: IsoWeekDate = cell.type === 'zoned-date-time'
			? getIsoWeekDate(cell.data, true)
			: cell.data
		return isoWeekDate === isoWeekDateOfCell
	})
}

/**
 * Returns the shifts row for the given `userId`
 *
 * @param userId The user ID
 */
function getShiftsRow(userId: string): ShiftsRow {
	if (shiftsRows.value.length === 0) {
		throw new Error('setupShiftsRows needs to be called before')
	}
	const row = shiftsRows.value.find(([{ data }]) => data.id === userId)
	if (!row) {
		throw new ShiftsRowNotFoundError(`Couldn't find shifts row for userId "${userId}"`)
	}
	return row
}

const dayCellFormatOptions: Intl.DateTimeFormatOptions = {
	month: 'short',
	weekday: 'short',
	day: 'numeric',
}

/**
 * Formats a header cell label for both table and mobile list layouts.
 *
 * @param cell The header cell to format
 */
function getHeaderCellLabel(cell: HeaderRow[number] | undefined): string {
	if (!cell) {
		return ''
	}
	if (cell.type === 'string') {
		return cell.data
	}
	if (cell.type === 'week') {
		return t(APP_ID, 'Weekly shifts')
	}
	return formatDate(cell.data, dayCellFormatOptions)
}

/**
 * Returns the shift type wrapper for the given `shiftTypeId` and `columnIndex`
 *
 * @param shiftTypeId The shift type ID
 * @param columnIndex The column index
 */
function getShiftTypeWrapper(
	shiftTypeId: number,
	columnIndex: number,
): ShiftTypeWrapper {
	if (!shiftTypesRow.value) {
		throw new Error('setupShiftTypesRow needs to be called before')
	}
	if (shiftTypesRow.value[columnIndex]?.type !== 'shift-types') {
		throw new Error(`Column ${columnIndex} does not contain a shift types data cell`)
	}
	const wrapper = shiftTypesRow.value[columnIndex].data.find(({ shiftType: { id } }) => id === shiftTypeId)
	if (!wrapper) {
		throw new ShiftTypeWrapperNotFoundError(`Couldn't find shift type wrapper with shift type ID ${shiftTypeId} in column ${columnIndex}`)
	}
	return wrapper
}

/**
 * Handler for click on a shift cell
 *
 * @param userId The user ID
 */
async function onShiftCellClick(userId: string): Promise<void> {
	let affectedShift: Shift | void = undefined
	createOrUpdateRequestPending.value = true
	try {
		switch (multiStepAction.value.type) {
			case 'creation':
				affectedShift = await onShiftCreationAttempt(userId)
				break
			case 'motion':
				affectedShift = await onShiftMotionAttempt(userId)
				break
		}
		if (!affectedShift) {
			return
		}
		postSynchronizeByShifts({ shift_ids: [affectedShift.id] })
	} finally {
		createOrUpdateRequestPending.value = false
	}
}

/**
 * Handler for shift creation attempt
 *
 * @param userId The user ID
 *
 * @return The created shift on success or `void` if the shift was not created
 * due to certain conditions not met
 */
async function onShiftCreationAttempt(userId: string): Promise<void | Shift> {
	if (multiStepAction.value.type !== 'creation') {
		return
	}
	const shiftTypeWrapper = multiStepAction.value.shiftTypeWrapper
	if (shiftTypeWrapper.amount < 1) {
		throw new Error(`Attempted to create shift from shift type ${shiftTypeWrapper.shiftType.name} but amount is already 0`)
	}
	const columnIndex = multiStepAction.value.columnIndex
	const column = headerRow.value?.[columnIndex]
	if (!column?.type || column?.type === 'string') {
		return
	}

	const payload: ShiftPostPayload = {
		user_id: userId,
		shift_type_id: shiftTypeWrapper.shiftType.id,
		...shiftTypeWrapper.weeklyType === 'by_day'
			? {
					start: shiftTypeWrapper.shiftStart,
					end: shiftTypeWrapper.shiftEnd,
				}
			: {
					start: shiftTypeWrapper.shiftStart,
					end: shiftTypeWrapper.shiftEnd,
				},
	}

	const createdShift = await postShift(payload)
	placeShift(createdShift, columnIndex)
	shiftTypeWrapper.amount--
	resetMultiStepAction()
	return createdShift
}

/**
 * Handler for shift motion attempt
 *
 * @param userId The user ID
 *
 * @return The updated shift on success or `void` if the shift was not updated
 * due to certain conditions not met
 */
async function onShiftMotionAttempt(userId: string): Promise<void | Shift> {
	if (multiStepAction.value.type !== 'motion') {
		return
	}
	const movedShift = multiStepAction.value.shift
	if (movedShift.user.id === userId) {
		return
	}
	const updatedShift = await patchShift(movedShift.id, { user_id: userId })
	const columnIndex = multiStepAction.value.columnIndex
	extractShift(movedShift, columnIndex)
	placeShift(updatedShift, columnIndex)
	resetMultiStepAction()
	return updatedShift
}

const deletionShifts = ref<Shift[]>([])

/**
 * Add `shift` to the deletion shifts
 *
 * @param shift The shift to add to the deletion shifts
 */
function addDeletionShift(shift: Shift) {
	deletionShifts.value.push(shift)
}

/**
 * Remove `shift` from the deletion shifts
 *
 * @param shift The shift to remove from the deletion shifts
 */
function removeDeletionShift(shift: Shift) {
	deletionShifts.value = deletionShifts.value.filter(({ id }) => id !== shift.id)
}

/**
 * Handler for shift deletion attempt
 *
 * @param shift The shift to delete
 * @param columnIndex The column index
 *
 * @return The deleted shift on success
 */
async function onShiftDeletionAttempt(shift: Shift, columnIndex: number): Promise<Shift> {
	const shiftTypeWrapper = getShiftTypeWrapper(
		shift.shift_type.id,
		columnIndex,
	)
	try {
		const deletedShift = await deleteShift(shift.id)
		extractShift(shift, columnIndex)
		shiftTypeWrapper.amount++
		return deletedShift
	} finally {
		removeDeletionShift(shift)
	}
}

/**
 * Returns the shifts data cell for the given `userId` and `columnIndex`
 *
 * @param userId The user ID
 * @param columnIndex The column index
 */
function getShiftsDataCell(userId: string, columnIndex: number): ShiftsDataCell {
	const row = getShiftsRow(userId)
	const cell = row[columnIndex]
	if (cell?.type !== 'shifts') {
		throw new Error(`Column ${columnIndex} does not contain a shifts data cell`)
	}
	return cell
}

/**
 * Extracts the shift from the shifts data cell identified by `shift.user_id` and `columnIndex`
 *
 * @param shift The shift to extract
 * @param columnIndex The column index
 */
function extractShift(shift: Shift, columnIndex: number): void {
	const cell = getShiftsDataCell(shift.user.id, columnIndex)
	cell.data = cell.data.filter(({ id }) => id !== shift.id)
}

/**
 * Places the shift into the shifts data cell identified by `shift.user_id` and `columnIndex`
 *
 * @param shift The shift to place
 * @param columnIndex The column index
 * @param sort Whether to sort the shifts in the cell after placing the shift. Defaults to `true`.
 */
function placeShift(shift: Shift, columnIndex: number, sort: boolean = true): void {
	const cell = getShiftsDataCell(shift.user.id, columnIndex)
	cell.data.push(shift)
	if (sort) {
		cell.data.sort(compareShifts)
	}
}

onKeyStroke(
	'Escape',
	() => {
		resetMultiStepAction()
	},
	{ dedupe: true },
)

/**
 * Sets the multi-step action
 *
 * @param newAction The new multi-step action
 */
function setMultiStepAction(newAction: MultiStepAction): void {
	multiStepAction.value = newAction
}

/**
 * Resets the multi-step action
 */
function resetMultiStepAction(): void {
	multiStepAction.value = getUndefinedMultiStepAction()
}

/**
 * Syncs the calendar by groups
 */
async function synchronizeByGroups() {
	let groupIds = selectedGroupIds.value
	if (groupIds.length === 0) {
		groupIds = groups.value.map(({ id }) => id)
	}
	try {
		synchronizing.value = true
		await postSynchronizeByGroups({ group_ids: groupIds })
	} finally {
		synchronizing.value = false
	}
}

/**
 * Returns absence blockers for a specific user and table cell
 *
 * @param userId The user ID used in the shifts row
 * @param columnIndex The table column index
 */
function getShiftCellBlockers(userId: string, columnIndex: number): AbsenceBlocker[] {
	const row = getShiftsRow(userId)
	const cell = row[columnIndex]
	if (cell?.type !== 'shifts') {
		return []
	}
	return cell.blockers ?? []
}

/**
 * Formats a blocker range for the tooltip label
 *
 * @param blocker The blocker to format
 */
function formatBlocker(blocker: AbsenceBlocker): string {
	const title = blocker.title.trim()
	if (blocker.all_day) {
		return title
			? `${title} (${t(APP_ID, 'all day')})`
			: t(APP_ID, 'all day')
	}
	const start = Temporal.Instant.from(blocker.start).toZonedDateTimeISO(userTimeZone)
	const end = Temporal.Instant.from(blocker.end).toZonedDateTimeISO(userTimeZone)
	const range = formatRange(start, end, { hour: '2-digit', minute: '2-digit' })
	return title
		? `${title} (${range})`
		: range
}

/**
 * Returns the tooltip text for blockers in a shifts cell
 *
 * @param userId The user ID used in the shifts row
 * @param columnIndex The table column index
 */
function getShiftCellBlockersTitle(userId: string, columnIndex: number): string {
	const blockers = getShiftCellBlockers(userId, columnIndex)
	if (blockers.length === 0) {
		return ''
	}
	return t(APP_ID, '⚠ blocked: {ranges}', {
		ranges: blockers.map(formatBlocker).join(', '),
	})
}

/**
 * Returns the visible blocked label for a shifts cell.
 *
 * @param userId The user ID used in the shifts row
 * @param columnIndex The table column index
 */
function getShiftCellBlockedLabel(userId: string, columnIndex: number): string {
	const blockers = getShiftCellBlockers(userId, columnIndex)
	const titles = [...new Set(blockers
		.map(({ title }) => title.trim())
		.filter((title) => title !== ''))].join(', ')
	return titles
		? t(APP_ID, '⚠ blocked: {titles}', { titles })
		: t(APP_ID, '⚠ blocked')
}

provideShiftsContext({
	setMultiStepAction,
	resetMultiStepAction,
	multiStepAction,
	onShiftDeletionAttempt,
	deletionShifts,
	addDeletionShift,
	removeDeletionShift,
})
</script>
