<template>
	<div class="p-4 w-full max-w-xs sm:max-w-md grid grid-cols-2 gap-x-2 gap-y-3 sm:grid-cols-4">
		<NcTextField
			v-for="property in PROPERTIES"
			:key="property"
			v-model.trim="properties[property]"
			:label="t(APP_ID, upperFirst(property))"
			type="number"
			min="0"
			required />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'
import { ref, watchEffect } from 'vue'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import { APP_ID } from '../appId.ts'
import { upperFirst } from '../string.ts'

const model = defineModel<Temporal.Duration>({ required: true })

if (model.value.sign === -1) {
	model.value = model.value.negated()
}

const properties = ref<Temporal.DurationLike>({
	days: model.value.days,
	hours: model.value.hours,
	minutes: model.value.minutes,
	seconds: model.value.seconds,
})

watchEffect(() => {
	model.value = Temporal.Duration.from(properties.value)
})

const PROPERTIES = ['days', 'hours', 'minutes', 'seconds'] as const
</script>
