<template>
	<div>
		<NcTextField
			v-for="property in PROPERTIES"
			:key="property"
			v-model.trim="durationLike[property]"
			:label="t(APP_ID, upperFirst(property))"
			type="number"
			min="0"
			required
			:disabled />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { Temporal } from 'temporal-polyfill'
import { nextTick, ref, watch } from 'vue'
import NcTextField from '@nextcloud/vue/components/NcTextField'
import { APP_ID } from '../utils/appId.ts'
import { upperFirst } from '../utils/string.ts'

const duration = defineModel<Temporal.Duration>({ required: true })

defineProps<{
	disabled?: boolean
}>()

const PROPERTIES = ['days', 'hours', 'minutes', 'seconds'] as const

const durationLike = ref<Temporal.DurationLike>({})

const { pause: pauseDurationLikeWatcher, resume: resumeDurationLikeWatcher }
	= watch(
		() => durationLike.value,
		durationLikeWatcherCallback,
		{ deep: true },
	)

const { pause: pauseDurationWatcher, resume: resumeDurationWatcher }
	= watch(
		() => duration.value,
		durationWatcherCallback,
		{ deep: true, immediate: true },
	)

/**
 * Callback for the `durationLike` watcher
 *
 * Updates `duration` with the properties of `durationLike` when `durationLike`
 * changes. The callback always uses the absolute value of `newValue` to update
 * `duration` so any negative properties are negated.
 *
 * @param newValue The new duration-like
 */
async function durationLikeWatcherCallback(newValue: Temporal.DurationLike) {
	pauseDurationWatcher()
	duration.value = Temporal.Duration.from(newValue).abs()
	await nextTick()
	resumeDurationWatcher()
}

/**
 * Callback for the `duration` watcher
 *
 * Updates `durationLike` with the properties of `duration` when `duration`
 * changes. The callback always uses the absolute value of `newValue` to update
 * `durationLike` so any negative properties are negated.
 *
 * @param newValue The new duration
 */
async function durationWatcherCallback(newValue: Temporal.Duration) {
	const absoluteValue = newValue.abs()
	pauseDurationLikeWatcher()
	for (const property of PROPERTIES) {
		durationLike.value[property] = absoluteValue[property]
	}
	await nextTick()
	resumeDurationLikeWatcher()
}

</script>
