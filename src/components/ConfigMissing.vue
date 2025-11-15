<template>
	<div class="w-full flex flex-col items-center justify-center text-nc-plain gap-3 font-semibold">
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="configMessage" />
		<ul class="list-disc">
			<li v-for="(missingConfig, index) in missingConfigs" :key="index">
				{{ t(APP_ID, missingConfig) }}
			</li>
		</ul>
		<div><strong>Note: </strong>This app is work in progress.</div>
		<div>Click <a href="https://github.com/csoc-de/shiftsnext/blob/main/README.md" target="_blank" class="underline">here</a> to make yourself familiar with the app.</div>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { APP_ID } from '../appId.ts'
import { ADMIN_SETTINGS_PATH } from '../url.ts'

defineProps<{
	missingConfigs: string[]
}>()

const settingsUrl = generateUrl(ADMIN_SETTINGS_PATH)

const configMessage = t(
	APP_ID,
	'The following configuration settings need to be set in the {linkStart}administration settings{linkEnd} before you can start using the app:',
	{ linkStart: `<a href="${settingsUrl}" class="underline">`, linkEnd: '</a>' },
	{ escape: false },
)
</script>
