<template>
	<div
		class="flex gap-1"
		:class="[inline ? 'items-center' : 'flex-col']">
		<slot />
	</div>
</template>

<script setup lang="ts">
const { inline = false } = defineProps<{ inline?: boolean }>()
</script>
