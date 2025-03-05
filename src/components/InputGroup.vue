<template>
	<div class="flex gap-1"
		:class="{
			'flex-col': !inline,
			'items-center': inline,
		}">
		<slot />
	</div>
</template>

<script setup lang="ts">
const { inline = false } = defineProps<{ inline?: boolean }>()
</script>
