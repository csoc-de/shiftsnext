<template>
	<input :id="id" class="float-left size-0 border-none p-0">
</template>

<script setup lang="ts">
import { useId } from 'vue'

const id = useId()
</script>
