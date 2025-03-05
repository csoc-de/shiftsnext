<template>
	<div class="sticky top-0 z-[20] flex w-full flex-wrap items-center justify-start gap-[calc(2*var(--app-navigation-padding))] py-nc-app-navigation-padding pe-nc-app-navigation-padding ps-[calc(44px+2*var(--app-navigation-padding))]"
		role="toolbar">
		<h1 class="h-nc-default-clickable-area max-w-64 overflow-hidden text-ellipsis whitespace-nowrap text-2xl font-bold">
			{{ title }}
		</h1>

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
import NcLoadingIcon from '@nextcloud/vue/dist/Components/NcLoadingIcon.js'

defineProps<{
  title: string
  loading: boolean
}>()
</script>
