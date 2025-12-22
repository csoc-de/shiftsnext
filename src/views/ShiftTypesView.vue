<template>
	<HeaderNavigation :title="t(APP_ID, 'Types')" :loading="loading">
		<template #right>
			<NcButton @click="createDialogMounted = true">
				{{ t(APP_ID, "New") }}
			</NcButton>
		</template>
	</HeaderNavigation>
	<PaddedContainer v-if="!loading">
		<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 my-1">
			<ShiftTypeCard
				v-for="shiftType in shiftTypes"
				:key="shiftType.id"
				:shift-type="shiftType" />
		</div>
		<ShiftTypeDialog
			v-if="createDialogMounted"
			@close="createDialogMounted = false" />
	</PaddedContainer>
</template>

<script lang="ts">
import { createContext } from '../utils/createContext.ts'

export interface ShiftTypesContext {
	create: (payload: ShiftTypePostPayload) => Promise<void>
	update: (id: number, payload: ShiftTypePutPayload) => Promise<void>
	remove: (id: number) => Promise<void>
}

export const [injectShiftTypesContext, provideShiftTypesContext]
	= createContext<ShiftTypesContext>('ShiftTypes')
</script>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import HeaderNavigation from '../components/HeaderNavigation.vue'
import PaddedContainer from '../components/PaddedContainer.vue'
import ShiftTypeCard from '../components/ShiftTypeCard.vue'
import ShiftTypeDialog from '../components/ShiftTypeDialog.vue'
import {
	deleteShiftType,
	getShiftTypes,
	postShiftType,
	putShiftType,
} from '../db/shiftType.ts'
import {
	type ShiftType,
	type ShiftTypePostPayload,
	type ShiftTypePutPayload,
} from '../models/shiftType.ts'
import { APP_ID } from '../utils/appId.ts'

const loading = ref(true)

const shiftTypes = ref<ShiftType[]>([])

const createDialogMounted = ref(false)

;(async () => {
	try {
		shiftTypes.value = await getShiftTypes({ restricted: true })
	} finally {
		loading.value = false
	}
})()

/**
 * Create shift type
 *
 * @param payload The shift type
 */
async function create(payload: ShiftTypePostPayload): Promise<void> {
	const createdShiftType = await postShiftType(payload)
	shiftTypes.value.unshift(createdShiftType)
	createDialogMounted.value = false
}

/**
 * Update shift type
 *
 * @param id The shift type id
 * @param payload The shift type
 */
async function update(id: number, payload: ShiftTypePutPayload): Promise<void> {
	const updatedShiftType = await putShiftType(id, payload)
	const index = shiftTypes.value.findIndex(({ id }) => id === updatedShiftType.id)
	shiftTypes.value[index] = updatedShiftType
}

/**
 * Remove shift type
 *
 * @param id The shift type id
 */
async function remove(id: number): Promise<void> {
	const deletedShiftType = await deleteShiftType(id)
	shiftTypes.value = shiftTypes.value.filter(({ id }) => id !== deletedShiftType.id)
}

provideShiftTypesContext({ create, update, remove })
</script>
