<template>
	<th>{{ formattedDate }}</th>
</template>

<script setup lang="ts">
import type { Temporal } from 'temporal-polyfill'

import { computed } from 'vue'
import {
	ZDT_CELL_FORMAT_OPTIONS,
} from '../models/shiftsTable.ts'
import { formatDate } from '../utils/date.ts'

const { zdt } = defineProps<{
	zdt: Temporal.ZonedDateTime
}>()

const formattedDate = computed(() => formatDate(zdt, ZDT_CELL_FORMAT_OPTIONS))
</script>
