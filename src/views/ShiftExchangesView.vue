<template>
	<ContentHeader :title="t(APP_ID, 'Exchanges')" :loading="loading">
		<template #right>
			<NcButton @click="createDialogMounted = true">
				{{ t(APP_ID, "New") }}
			</NcButton>
		</template>
	</ContentHeader>
	<PaddedContainer v-if="!loading">
		<div class="flex justify-center">
			<div class="grid w-full max-w-[1000px] grid-cols-1 gap-9 md:grid-cols-2">
				<div>
					<h4 class="mt-0 mb-5 border-b-4 border-solid border-nc-warning pb-4 text-center">
						{{ t(APP_ID, "Pending") }}
					</h4>
					<div class="flex flex-col gap-4 my-1">
						<ShiftExchangeCard
							v-for="shiftExchange in pendingShiftExchanges"
							:key="shiftExchange.id"
							:shiftExchange="shiftExchange" />
					</div>
				</div>
				<div>
					<h4 class="mt-0 mb-5 border-b-4 border-solid border-nc-success pb-4 text-center">
						{{ t(APP_ID, "Done") }}
					</h4>
					<div class="flex flex-col gap-4 my-1">
						<ShiftExchangeCard
							v-for="shiftExchange in doneShiftExchanges"
							:key="shiftExchange.id"
							:shiftExchange="shiftExchange" />
					</div>
				</div>
			</div>
		</div>

		<CreateShiftExchangeDialog
			v-if="createDialogMounted"
			@close="createDialogMounted = false" />
	</PaddedContainer>
</template>

<script lang="ts">
import { createContext } from '../utils/createContext.ts'

export interface ShiftExchangesContext {
	create: (payload: ShiftExchangePostPayload) => Promise<ShiftExchange>
	update: (id: number, payload: ShiftExchangePutPayload) => Promise<ShiftExchange>
	remove: (id: number) => Promise<ShiftExchange>
}

export const [injectShiftExchangesContext, provideShiftExchangesContext]
	= createContext<ShiftExchangesContext>('ShiftExchanges')
</script>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import ContentHeader from '../components/ContentHeader.vue'
import CreateShiftExchangeDialog from '../components/CreateShiftExchangeDialog.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftExchangeCard from '../components/ShiftExchangeCard.vue'
import {
	deleteShiftExchange,
	getShiftExchanges,
	postShiftExchange,
	putShiftExchange,
} from '../db/shiftExchange.ts'
import {
	type ShiftExchange,
	type ShiftExchangePostPayload,
	type ShiftExchangePutPayload,
} from '../models/shiftExchange.ts'
import { APP_ID } from '../utils/appId.ts'
import { compare } from '../utils/sort.ts'

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
 *
 * @return The created shift exchange on success
 */
async function create(payload: ShiftExchangePostPayload): Promise<ShiftExchange> {
	const createdShiftExchange = await postShiftExchange(payload)
	if (createdShiftExchange.done) {
		doneShiftExchanges.value.unshift(createdShiftExchange)
	} else {
		pendingShiftExchanges.value.unshift(createdShiftExchange)
	}
	createDialogMounted.value = false
	return createdShiftExchange
}

/**
 * Update shift exchange
 *
 * @param id The shift exchange id
 * @param payload The shift exchange
 *
 * @return The updated shift exchange on success
 */
async function update(
	id: number,
	payload: ShiftExchangePutPayload,
): Promise<ShiftExchange> {
	const updatedShiftExchange = await putShiftExchange(id, payload)
	if (updatedShiftExchange.done) {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(({ id }) => id !== updatedShiftExchange.id)
		doneShiftExchanges.value.push(updatedShiftExchange)
		doneShiftExchanges.value.sort((a, b) => compare(b.id, a.id))
	} else {
		const index = pendingShiftExchanges.value.findIndex(({ id }) => id === updatedShiftExchange.id)
		pendingShiftExchanges.value[index] = updatedShiftExchange
	}
	return updatedShiftExchange
}

/**
 * Remove shift exchange
 *
 * @param id The shift exchange id
 *
 * @return The deleted shift exchange on success
 */
async function remove(id: number): Promise<ShiftExchange> {
	const deletedShiftExchange = await deleteShiftExchange(id)
	if (deletedShiftExchange.done) {
		doneShiftExchanges.value = doneShiftExchanges.value.filter(({ id }) => id !== deletedShiftExchange.id)
	} else {
		pendingShiftExchanges.value = pendingShiftExchanges.value.filter(({ id }) => id !== deletedShiftExchange.id)
	}
	return deletedShiftExchange
}

provideShiftExchangesContext({ create, update, remove })
</script>
