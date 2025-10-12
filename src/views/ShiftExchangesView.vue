<template>
	<HeaderNavigation :title="t(APP_ID, 'Exchanges')" :loading="loading">
		<template #right>
			<NcButton @click="createDialogMounted = true">
				{{ t(APP_ID, "New") }}
			</NcButton>
		</template>
	</HeaderNavigation>
	<PaddedContainer v-if="!loading">
		<div class="flex justify-center">
			<div class="grid w-full max-w-[1000px] grid-cols-1 gap-9 md:grid-cols-2">
				<div>
					<h3 class="mt-0 mb-5 border-b-4 border-solid border-nc-warning pb-4 text-center">
						{{ t(APP_ID, "Open") }}
					</h3>
					<div class="flex flex-col gap-4">
						<ShiftExchangeCard
							v-for="shiftExchange in pendingShiftExchanges"
							:key="shiftExchange.id"
							:shift-exchange="shiftExchange" />
					</div>
				</div>
				<div>
					<h3 class="mt-0 mb-5 border-b-4 border-solid border-nc-success pb-4 text-center">
						{{ t(APP_ID, "Done") }}
					</h3>
					<div class="flex flex-col gap-4">
						<ShiftExchangeCard
							v-for="shiftExchange in doneShiftExchanges"
							:key="shiftExchange.id"
							:shift-exchange="shiftExchange" />
					</div>
				</div>
			</div>
		</div>

		<CreateShiftExchangeDialog
			v-if="createDialogMounted"
			@close="createDialogMounted = false" />
	</PaddedContainer>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { provide, ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import CreateShiftExchangeDialog from '../components/CreateShiftExchangeDialog.vue'
import HeaderNavigation from '../components/HeaderNavigation.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftExchangeCard from '../components/ShiftExchangeCard.vue'
import { APP_ID } from '../appId.ts'
import {
	deleteShiftExchange,
	getShiftExchanges,
	postShiftExchange,
	putShiftExchange,
} from '../db/shiftExchange.ts'
import {
	type ShiftExchange,
	type ShiftExchangePostRequest,
	type ShiftExchangePutRequest,

	createIK,
	removeIK,
	updateIK,
} from '../models/shiftExchange.ts'
import { compare } from '../sort.ts'

const loading = ref(true)

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
 *
 * @param payload The shift exchange
 */
async function create(payload: ShiftExchangePostRequest): Promise<void> {
	const createdShiftExchange = await postShiftExchange(payload)
	if (createdShiftExchange.done) {
		doneShiftExchanges.value.unshift(createdShiftExchange)
	} else {
		pendingShiftExchanges.value.unshift(createdShiftExchange)
	}
	createDialogMounted.value = false
}
provide(createIK, create)

/**
 * Update shift exchange
 *
 * @param id The shift exchange id
 * @param payload The shift exchange
 */
async function update(
	id: number,
	payload: ShiftExchangePutRequest,
): Promise<void> {
	const updatedShiftExchange = await putShiftExchange(id, payload)
	if (updatedShiftExchange.done) {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(({ id }) => id !== updatedShiftExchange.id)
		doneShiftExchanges.value.push(updatedShiftExchange)
		doneShiftExchanges.value.sort((a, b) => compare(b.id, a.id))
	} else {
		const index = pendingShiftExchanges.value.findIndex(({ id }) => id === updatedShiftExchange.id)
		pendingShiftExchanges.value[index] = updatedShiftExchange
	}
}
provide(updateIK, update)

/**
 * Remove shift exchange
 *
 * @param id The shift exchange id
 */
async function remove(id: number): Promise<void> {
	const deletedShiftExchange = await deleteShiftExchange(id)
	if (deletedShiftExchange.done) {
		doneShiftExchanges.value = doneShiftExchanges.value.filter(({ id }) => id !== deletedShiftExchange.id)
	} else {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(({ id }) => id !== deletedShiftExchange.id)
	}
}
provide(removeIK, remove)
</script>
