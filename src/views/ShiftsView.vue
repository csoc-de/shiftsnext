<template>
	<HeaderNavigation :title="t(APP_ID, 'Shifts')" :loading="loading">
		<div class="flex flex-wrap gap-2">
			<HeaderNavigationInputGroup>
				<label for="iso-week-date">{{ t(APP_ID, "Year and week") }}</label>
				<IsoWeekDateInput
					ref="isoWeekDateInput"
					v-model="isoWeekDate"
					input-id="iso-week-date" />
			</HeaderNavigationInputGroup>
			<HeaderNavigationInputGroup>
				<NcButton
					:aria-label="t(APP_ID, 'Previous week')"
					@click="isoWeekDateInput?.decrease()">
					<template #icon>
						<ChevronLeft :size="20" />
					</template>
				</NcButton>
				<NcButton
					:aria-label="t(APP_ID, 'Next week')"
					@click="isoWeekDateInput?.increase()">
					<template #icon>
						<ChevronRight :size="20" />
					</template>
				</NcButton>
				<NcButton @click="isoWeekDate = getIsoWeekDate(today, false)">
					{{ t(APP_ID, "Today") }}
				</NcButton>
			</HeaderNavigationInputGroup>
			<HeaderNavigationInputGroup>
				<label for="groups">{{ t(APP_ID, "Groups") }}</label>
				<NcSelect
					v-model="selectedGroups"
					input-id="groups"
					:options="groups"
					label="display_name"
					label-outside
					keep-open
					multiple
					class="min-w-48" />
			</HeaderNavigationInputGroup>
		</div>
		<template #right>
			<div class="flex flex-wrap gap-2">
				<NcButton
					v-if="shiftAdminGroups.length"
					:disabled="synchronizing"
					variant="primary"
					@click="synchronizeByGroups()">
					{{ t(APP_ID, "Synchronize") }}
				</NcButton>
				<NcButton
					v-if="multiStepAction.type"
					variant="error"
					@click="resetMultiStepAction()">
					{{ t(APP_ID, "Cancel") }}
				</NcButton>
			</div>
		</template>
	</HeaderNavigation>
	<PaddedContainer v-if="!loading" class="overflow-auto">
		<table class="h-fit w-full border-collapse border border-solid border-neutral-500">
			<caption class="text-xl">
				{{ isoWeekDate }}
			</caption>
			<thead>
				<tr class="h-12">
					<th
						v-for="({ type, data }, columnIndex) in headerRow"
						:key="columnIndex"
						class="border border-solid border-neutral-500 p-2 text-center"
						:class="{
							[cellBgColor]:
								multiStepAction.type
								&& multiStepAction.columnIndex === columnIndex,
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
			<tbody class="h-full">
				<tr class="h-12">
					<td
						v-for="({ type, data }, columnIndex) in shiftTypesRow"
						:key="columnIndex"
						class="border border-solid border-neutral-500 h-full"
						:class="{
							[cellBgColor]:
								multiStepAction.type
								&& multiStepAction.columnIndex === columnIndex,
							'p-2 text-center': type === 'string',
						}">
						<template v-if="type === 'string'">
							{{ data }}
						</template>
						<div v-else class="flex size-full flex-col gap-1 p-2">
							<template v-for="(shiftTypeWrapper, i) in data" :key="i">
								<ShiftTypePill
									v-if="
										shiftTypeWrapper.amount && shiftTypeWrapper.shiftType.active
									"
									:shift-type-wrapper="shiftTypeWrapper"
									:column-index="columnIndex" />
							</template>
						</div>
					</td>
				</tr>
				<tr
					v-for="(shiftsRow, rowIndex) in shiftsRows"
					:key="rowIndex"
					class="h-12">
					<td
						v-for="({ type, data }, columnIndex) in shiftsRow"
						:key="columnIndex"
						class="border border-solid border-neutral-500 h-full"
						:class="{
							[cellBgColor]:
								multiStepAction.type
								&& multiStepAction.columnIndex === columnIndex,
							'p-2 text-center': type === 'user',
						}">
						<template v-if="type === 'user'">
							{{ data.display_name }}
						</template>
						<div
							v-else
							class="flex size-full flex-col gap-1 p-2"
							:class="{
								'pointer-events-none':
									multiStepAction.type
									&& multiStepAction.columnIndex !== columnIndex,
							}"
							@click="onShiftCellClick(shiftsRow[0].data.id)">
							<ShiftPill
								v-for="(shift, i) in data"
								:key="i"
								:shift="shift"
								:column-index="columnIndex" />
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</PaddedContainer>
</template>

<script setup lang="ts">
import type { Group } from '../models/group.ts'
import type { Shift, ShiftRequest } from '../models/shift.ts'
import type { User } from '../models/user.ts'

import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import { onKeyStroke } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { Temporal } from 'temporal-polyfill'
import { provide, ref, useTemplateRef, watch } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
// @ts-expect-error package has no types
import ChevronLeft from 'vue-material-design-icons/ChevronLeft.vue'
// @ts-expect-error package has no types
import ChevronRight from 'vue-material-design-icons/ChevronRight.vue'
import HeaderNavigation from '../components/HeaderNavigation.vue'
import HeaderNavigationInputGroup from '../components/HeaderNavigationInputGroup.vue'
import IsoWeekDateInput from '../components/IsoWeekDateInput.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftPill from '../components/ShiftPill.vue'
import ShiftTypePill from '../components/ShiftTypePill.vue'
import { APP_ID } from '../appId.ts'
import { rotate } from '../array.ts'
import {
	type IsoWeekDate,

	formatDate,
	getIsoWeekDate,
	getZonedDateTimeForDayOfWeek,
	localTimeZone,
	parseIsoWeekDate,
} from '../date.ts'
import { postSynchronizeByGroups } from '../db/calendarSync.ts'
import { deleteShift, getShifts, patchShift, postShift } from '../db/shift.ts'
import { getShiftTypes } from '../db/shiftType.ts'
import { getUsers } from '../db/user.ts'
import { logger } from '../logger.ts'
import { RecoverableError } from '../models/error.ts'
import {
	type HeaderRow,
	type MultiStepAction,
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

	deletionShiftIK,
	multiStepActionIK,
	onShiftDeletionAttemptIK,
	resetDeletionShiftIK,
	resetMultiStepActionIK,
	setDeletionShiftIK,
	setMultiStepActionIK,
} from '../models/shiftsTable.ts'
import {
	type ShiftType,
	type ShortDay,
	type ShortDayToAmountMap,

	SHORT_DAYS,
	shortDayToIsoDayNumberMap,
} from '../models/shiftType.ts'
import { compareShifts, compareShiftTypes } from '../sort.ts'
import { useSelectedGroups } from '../stores/selectedGroups.ts'

const isoWeekDateInput = useTemplateRef('isoWeekDateInput')

const today = Temporal.Now.zonedDateTimeISO(localTimeZone)

const isoWeekDate = ref(getIsoWeekDate(today, false))

const loading = ref(true)
const synchronizing = ref(false)

const groups = ref(loadState<Group[]>(APP_ID, 'groups', []))
const { selectedGroups, selectedGroupIds } = storeToRefs(useSelectedGroups())

const shiftAdminGroups = loadState<Group[]>(APP_ID, 'shift_admin_groups', [])

const undefinedMultiStepAction: UndefinedMultiStepAction = {
	type: undefined,
	columnIndex: -1,
}

const multiStepAction = ref<MultiStepAction>(undefinedMultiStepAction)

// These are non-reactive because the reactive data is stored inside
// `headerRow`, `shiftTypesRow` and `shiftsRows`
let users: User[] = []
let shifts: Shift[] = []
let shiftTypes: ShiftType[] = []

const headerRow = ref<HeaderRow>()
const shiftTypesRow = ref<ShiftTypesRow>()
const shiftsRows = ref<ShiftsRow[]>([])

watch([isoWeekDate, selectedGroups], initialize, { immediate: true })

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
	usersPromise.then((_users) => (users = _users))
	promises.push(usersPromise)

	const shiftsPromise = getShifts({
		group_ids: groupIds,
		week_date: isoWeekDate.value,
	})
	shiftsPromise.then((_shifts) => (shifts = _shifts))
	promises.push(shiftsPromise)

	Promise.all(promises)
		.then(() => buildTable())
		.finally(() => (loading.value = false))
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
	const zdtsOfWeek: Temporal.ZonedDateTime[] = []
	for (let dayOfWeek = 1; dayOfWeek <= 7; dayOfWeek++) {
		zdtsOfWeek.push(parseIsoWeekDate(`${isoWeekDate.value}-${dayOfWeek}`))
	}
	const analystCell: StringCell = {
		type: 'string',
		data: t(APP_ID, 'Analyst'),
	}
	const weekCell: WeekCell = {
		type: 'week',
		data: isoWeekDate.value,
	}
	const dayCells: ZonedDateTimeDataCell[] = zdtsOfWeek.map((zdt): ZonedDateTimeDataCell => ({ type: 'zoned-date-time', data: zdt }))
	headerRow.value = [analystCell, weekCell, ...dayCells]
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
			shiftsDataCells.push({ type: 'shifts', data: [] })
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

		let intervalZdt = reference.withTimeZone(localTimeZone)
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
		const { start, user, shift_type: { id: shiftTypeId } } = shift
		const isoWeekDate = getIsoWeekDate(
			start,
			shift.shift_type.repetition.weekly_type === 'by_day',
		)
		const row = getShiftsRow(user.id)
		const columnIndex = getColumnIndex(isoWeekDate)
		const cell = row[columnIndex]
		if (cell?.type !== 'shifts') {
			continue
		}
		cell.data.push(shift)
		try {
			const shiftTypeWrapper = getShiftTypeWrapper(shiftTypeId, columnIndex)
			shiftTypeWrapper.amount--
		} catch (error) {
			if (!(error instanceof RecoverableError)) {
				throw error
			}
			logger.warn(error)
		}
	}
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
		throw new Error(`Couldn't find shifts row for userId "${userId}"`)
	}
	return row
}

const dayCellFormatOptions: Intl.DateTimeFormatOptions = {
	month: 'short',
	weekday: 'long',
	day: '2-digit',
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
		throw new RecoverableError(`Couldn't find shift type wrapper with shift type ID ${shiftTypeId} in column ${columnIndex}`)
	}
	return wrapper
}

/**
 * Handler for click on a shift cell
 *
 * @param userId The user ID
 */
function onShiftCellClick(userId: string): void {
	switch (multiStepAction.value.type) {
		case 'creation':
			onShiftCreationAttempt(userId)
			break
		case 'motion':
			onShiftMotionAttempt(userId)
			break
	}
}

/**
 * Handler for shift creation attempt
 *
 * @param userId The user ID
 */
async function onShiftCreationAttempt(userId: string): Promise<void> {
	if (multiStepAction.value.type !== 'creation') {
		return
	}
	const shiftTypeWrapper = multiStepAction.value.shiftTypeWrapper
	const columnIndex = multiStepAction.value.columnIndex
	const column = headerRow.value?.[columnIndex]
	if (!column?.type || column?.type === 'string') {
		return
	}

	const payload: ShiftRequest = {
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
	placeShift(createdShift)
	shiftTypeWrapper.amount--
	resetMultiStepAction()
}

/**
 * Handler for shift motion attempt
 *
 * @param userId The user ID
 */
async function onShiftMotionAttempt(userId: string): Promise<void> {
	if (multiStepAction.value.type !== 'motion') {
		return
	}
	const movedShift = multiStepAction.value.shift
	if (movedShift.user.id === userId) {
		return
	}
	const updatedShift = await patchShift(movedShift.id, { user_id: userId })
	extractShift(movedShift)
	placeShift(updatedShift)
	resetMultiStepAction()
}

const deletionShift = ref<Shift>()
provide(deletionShiftIK, deletionShift)

/**
 * Sets the deletion shift
 *
 * @param shift The shift to delete
 */
function setDeletionShift(shift: Shift) {
	deletionShift.value = shift
}
provide(setDeletionShiftIK, setDeletionShift)

/**
 * Resets the deletion shift
 */
function resetDeletionShift() {
	deletionShift.value = undefined
}
provide(resetDeletionShiftIK, resetDeletionShift)

/**
 * Handler for shift deletion attempt
 *
 * @param shift The shift to delete
 * @param columnIndex The column index
 */
async function onShiftDeletionAttempt(shift: Shift, columnIndex: number) {
	const shiftTypeWrapper = getShiftTypeWrapper(
		shift.shift_type.id,
		columnIndex,
	)
	try {
		await deleteShift(shift.id)
		extractShift(shift, columnIndex)
		shiftTypeWrapper.amount++
	} finally {
		resetDeletionShift()
	}
}
provide(onShiftDeletionAttemptIK, onShiftDeletionAttempt)

/**
 * Returns the shifts data cell for the given `userId` and `columnIndex`
 *
 * @param userId The user ID
 * @param columnIndex The column index
 */
function getShiftsDataCell(
	userId: string,
	columnIndex?: number,
): ShiftsDataCell {
	const row = getShiftsRow(userId)
	columnIndex ??= multiStepAction.value.columnIndex
	const cell = row[columnIndex]
	if (cell?.type !== 'shifts') {
		throw new Error(`Column ${columnIndex} does not contain a shifts data cell`)
	}
	return cell
}

/**
 * Extracts the shift from the shifts data cell
 *
 * @param shift The shift to extract
 * @param columnIndex The column index
 */
function extractShift(shift: Shift, columnIndex?: number): void {
	const cell = getShiftsDataCell(shift.user.id, columnIndex)
	cell.data = cell.data.filter(({ id }) => id !== shift.id)
}

/**
 * Places the shift
 *
 * @param shift The shift to place
 * @param columnIndex The column index
 */
function placeShift(shift: Shift, columnIndex?: number): void {
	const cell = getShiftsDataCell(shift.user.id, columnIndex)
	cell.data.push(shift)
	cell.data.sort(compareShifts)
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
	multiStepAction.value = undefinedMultiStepAction
}

provide(multiStepActionIK, multiStepAction)
provide(setMultiStepActionIK, setMultiStepAction)
provide(resetMultiStepActionIK, resetMultiStepAction)

const cellBgColor = 'bg-neutral-200 dark:bg-neutral-800'

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
</script>

<style scoped lang="scss">
tbody tr:not(.group-header):hover,
tbody tr:not(.group-header):focus,
tbody tr:not(.group-header):active {
  background-color: initial;
}
</style>
