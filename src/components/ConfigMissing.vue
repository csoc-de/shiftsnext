<template>
	<div class="w-full flex flex-col items-center justify-center text-nc-plain gap-3 font-semibold">
		<div>
			{{ t(APP_ID, 'The following configuration settings need to be set in the administration settings before you can start using the app') }}:
		</div>
		<ul class="list-disc">
			<li v-for="(missingConfig, index) in missingConfigs" :key="index">
				{{ t(APP_ID, missingConfig) }}
			</li>
		</ul>
		<div><strong>Note: </strong>This app is work in progress.</div>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { APP_ID } from '../appId.ts'

defineProps<{
	missingConfigs: string[]
}>()
</script>
