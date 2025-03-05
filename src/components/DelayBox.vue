<template>
	<Teleport to="#delay-boxes-wrapper">
		<div class="flex items-center gap-2 rounded-nc-large bg-nc-primary-light p-2 shadow-lg">
			<div class="h-[4px] flex-1 bg-nc-main-background">
				<div class="h-full rounded-full bg-nc-primary transition-[width] ease-linear"
					:style="{ transitionDuration, width }" />
			</div>
			<NcButton :type="ButtonType.Primary" size="small" @click.stop="onUndo">
				{{
					t(APP_ID, "Undo")
				}}
			</NcButton>
		</div>
	</Teleport>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import NcButton, {
	ButtonType,
} from '@nextcloud/vue/dist/Components/NcButton.js'
import { ref } from 'vue'
import { APP_ID } from '../appId'

const { delay = 2000 } = defineProps<{ delay?: number }>()

const transitionDuration = `${delay}ms`

const width = ref('0%')

setTimeout(() => (width.value = '100%'), 10)

const emit = defineEmits<{
	done: []
	undone: []
}>()

const timeout = setTimeout(() => emit('done'), delay)

/**
 * Undo the action
 */
function onUndo() {
	clearTimeout(timeout)
	emit('undone')
}
</script>
