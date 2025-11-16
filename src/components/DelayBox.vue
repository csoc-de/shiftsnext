<template>
	<Teleport to="#delay-boxes-wrapper">
		<div class="flex items-center gap-2 rounded-nc-container bg-nc-dark p-2 shadow-lg">
			<div class="h-[4px] flex-1 bg-nc-loading-light">
				<div
					class="h-full rounded-nc-pill bg-nc-loading-dark transition-[width] ease-linear"
					:style="{ transitionDuration, width }" />
			</div>
			<NcButton variant="primary" size="small" @click.stop="onCancel">
				{{
					t(APP_ID, "Cancel")
				}}
			</NcButton>
		</div>
	</Teleport>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { ref } from 'vue'
import NcButton from '@nextcloud/vue/components/NcButton'
import { APP_ID } from '../utils/appId.ts'

const { delay = 2000 } = defineProps<{ delay?: number }>()

const emit = defineEmits<{
	finished: []
	canceled: []
}>()

const transitionDuration = `${delay}ms`

const width = ref('0%')

setTimeout(() => (width.value = '100%'), 10)

const timeout = setTimeout(() => emit('finished'), delay)

/**
 * Cancel the action
 */
function onCancel() {
	clearTimeout(timeout)
	emit('canceled')
}
</script>
