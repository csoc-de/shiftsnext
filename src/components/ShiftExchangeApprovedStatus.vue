<template>
	<ClockOutline
		v-if="approved === null"
		:title="t(APP_ID, 'Approval pending')"
		:size="18"
		class="text-yellow-500" />
	<CheckCircleOutline
		v-else-if="approved"
		:title="t(APP_ID, 'Approved')"
		:size="18"
		class="text-green-500" />
	<CloseCircleOutline
		v-else
		:title="t(APP_ID, 'Rejected')"
		:size="18"
		class="text-red-500" />
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
// @ts-expect-error package has no types
import CheckCircleOutline from 'vue-material-design-icons/CheckCircleOutline.vue'
// @ts-expect-error package has no types
import ClockOutline from 'vue-material-design-icons/ClockOutline.vue'
// @ts-expect-error package has no types
import CloseCircleOutline from 'vue-material-design-icons/CloseCircleOutline.vue'
import { APP_ID } from '../appId.ts'

defineProps<{ approved: boolean | null }>()
</script>
