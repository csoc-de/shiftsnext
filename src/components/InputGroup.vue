<template>
	<div
		class="flex"
		:class="[inline ? 'items-center gap-x-1' : 'flex-col']">
		<slot />
	</div>
</template>

<script setup lang="ts">
const { inline = false } = defineProps<{ inline?: boolean }>()
</script>
