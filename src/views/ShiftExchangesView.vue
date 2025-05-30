<template>
	<HeaderNavigation :title="t(APP_ID, 'Shift exchanges')" :loading="loading">
		<template #right>
			<NcButton :aria-label="t(APP_ID, 'Request shift exchange')"
				@click="createDialogMounted = true">
				<template #icon>
					<Plus :size="20" />
				</template>
			</NcButton>
		</template>
	</HeaderNavigation>
	<PaddedContainer v-if="!loading">
		<div class="flex justify-center">
			<div class="grid w-full max-w-[1000px] grid-cols-1 gap-9 md:grid-cols-2">
				<div>
					<h2 class="mb-5 border-b-4 border-solid border-nc-warning pb-4 text-center text-xl font-bold">
						{{ t(APP_ID, "Open") }}
					</h2>
					<div class="flex flex-col gap-4">
						<ShiftExchangeBox v-for="shiftExchange in pendingShiftExchanges"
							:key="shiftExchange.id"
							:shift-exchange="shiftExchange" />
					</div>
				</div>
				<div>
					<h2 class="mb-5 border-b-4 border-solid border-nc-success pb-4 text-center text-xl font-bold">
						{{ t(APP_ID, "Done") }}
					</h2>
					<div class="flex flex-col gap-4">
						<ShiftExchangeBox v-for="shiftExchange in doneShiftExchanges"
							:key="shiftExchange.id"
							:shift-exchange="shiftExchange" />
					</div>
				</div>
			</div>
		</div>

		<CreateShiftExchangeDialog v-if="createDialogMounted"
			@close="createDialogMounted = false" />
	</PaddedContainer>
</template>

<script setup lang="ts">
import { loadState } from '@nextcloud/initial-state'
import { t } from '@nextcloud/l10n'
import NcButton from '@nextcloud/vue/components/NcButton'
import { provide, ref } from 'vue'
// @ts-expect-error no types
import Plus from 'vue-material-design-icons/Plus.vue'
import { APP_ID } from '../appId'
import CreateShiftExchangeDialog from '../components/CreateShiftExchangeDialog.vue'
import HeaderNavigation from '../components/HeaderNavigation.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftExchangeBox from '../components/ShiftExchangeBox.vue'
import {
	deleteShiftExchange,
	getShiftExchanges,
	postShiftExchange,
	putShiftExchange,
} from '../db/shiftExchange'
import type { ExchangeApprovalType } from '../models/config'
import type { GroupShiftAdminRelationsByGroup } from '../models/groupShiftAdminRelation'
import {
	createIK,
	exchangeApprovalTypeIK,
	relationsIK,
	removeIK,
	updateIK,
	type ShiftExchange,
	type ShiftExchangePostRequest,
	type ShiftExchangePutRequest,
} from '../models/shiftExchange'
import { compare } from '../sort'

const loading = ref(true)

const exchangeApprovalType = loadState<ExchangeApprovalType>(
	APP_ID,
	'exchange_approval_type',
	'all',
)
provide(exchangeApprovalTypeIK, exchangeApprovalType)

const relations = loadState<GroupShiftAdminRelationsByGroup[]>(
	APP_ID,
	'group_shift_admin_relations_by_group',
	[],
)
provide(relationsIK, relations)

const pendingShiftExchanges = ref<ShiftExchange[]>([])
const doneShiftExchanges = ref<ShiftExchange[]>([])

const createDialogMounted = ref(false)

;(async () => {
	try {
		const shiftExchanges = await getShiftExchanges()
		for (const shiftExchange of shiftExchanges) {
			if (shiftExchange.done) {
				doneShiftExchanges.value.push(shiftExchange)
			} else {
				pendingShiftExchanges.value.push(shiftExchange)
			}
		}
	} finally {
		loading.value = false
	}
})()

/**
 * Create shift exchange
 * @param payload The shift exchange
 */
async function create(payload: ShiftExchangePostRequest): Promise<void> {
	const createdShiftExchange = await postShiftExchange(payload)
	pendingShiftExchanges.value.unshift(createdShiftExchange)
	createDialogMounted.value = false
}
provide(createIK, create)

/**
 * Update shift exchange
 * @param id The shift exchange id
 * @param payload The shift exchange
 */
async function update(
	id: number,
	payload: ShiftExchangePutRequest,
): Promise<void> {
	const updatedShiftExchange = await putShiftExchange(id, payload)
	if (updatedShiftExchange.done) {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(
			({ id }) => id !== updatedShiftExchange.id,
		)
		doneShiftExchanges.value.push(updatedShiftExchange)
		doneShiftExchanges.value.sort((a, b) => compare(b.id, a.id))
	} else {
		const index = pendingShiftExchanges.value.findIndex(
			({ id }) => id === updatedShiftExchange.id,
		)
		pendingShiftExchanges.value[index] = updatedShiftExchange
	}
}
provide(updateIK, update)

/**
 * Remove shift exchange
 * @param id The shift exchange id
 */
async function remove(id: number): Promise<void> {
	const deletedShiftExchange = await deleteShiftExchange(id)
	if (deletedShiftExchange.done) {
		doneShiftExchanges.value = doneShiftExchanges.value.filter(
			({ id }) => id !== deletedShiftExchange.id,
		)
	} else {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(
			({ id }) => id !== deletedShiftExchange.id,
		)
	}
}
provide(removeIK, remove)
</script>
