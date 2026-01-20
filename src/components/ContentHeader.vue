<template>
	<div
		class="sticky top-0 z-[20] flex w-full flex-wrap items-center justify-start gap-[calc(2*var(--app-navigation-padding))] py-[var(--app-navigation-padding)] pe-[var(--app-navigation-padding)] ps-[calc(var(--default-clickable-area)+2*var(--app-navigation-padding))]"
		role="toolbar">
		<h3 class="max-w-64 truncate">
			{{ title }}
		</h3>

		<div>
			<slot />
		</div>

		<NcLoadingIcon v-show="loading" class="ms-8" />

		<div class="ms-auto flex items-center justify-center">
			<slot name="right" />
		</div>
	</div>
</template>

<script setup lang="ts">
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon'

defineProps<{
	title: string
	loading: boolean
}>()
</script>

<style scoped lang="scss">
:deep() {
	h1, h2, h3, h4, h5, h6 {
		line-height: initial;
		margin: 0;
	}
}
</style>
