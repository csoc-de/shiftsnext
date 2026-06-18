<template>
	<th>
		<div class="flex size-full flex-col gap-1 p-2">
			<template v-for="(shiftTypeWrapper, i) in shiftTypeWrappers" :key="i">
				<ShiftTypePill
					v-if="
						shiftTypeWrapper.amount && shiftTypeWrapper.shiftType.active
					"
					:shift-type-wrapper="shiftTypeWrapper"
					:column-index="columnIndex" />
			</template>
		</div>
	</th>
</template>

<script setup lang="ts">
import ShiftTypePill from './ShiftTypePill.vue'
import {
	type ShiftTypeWrapper,
} from '../models/shiftsTable.ts'

defineProps<{
	columnIndex: number
	shiftTypeWrappers: ShiftTypeWrapper[]
}>()

</script>
