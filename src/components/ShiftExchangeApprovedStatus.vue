<template>
	<NcButton
		v-if="isButton"
		size="small"
		:title>
		<template #icon>
			<component :is="iconComponent" :size="16" :class="colorClass" />
		</template>
	</NcButton>
	<div
		v-else
		class="size-nc-clickable-area-small flex items-center justify-center"
		:title>
		<component :is="iconComponent" :size="16" :class="colorClass" />
	</div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import {
	type Approved,

	approvedColorClasses,
	approvedIconComponents,
	approvedTranslations,
} from '../models/shiftExchange.ts'

const { approved, isButton = false } = defineProps<{
	approved: Approved
	isButton?: boolean
}>()

const title = computed(() => approvedTranslations[`${approved}`])
const iconComponent = computed(() => approvedIconComponents[`${approved}`])
const colorClass = computed(() => approvedColorClasses[`${approved}`])
</script>
