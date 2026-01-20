<template>
	<div class="w-full flex flex-col items-center justify-center text-nc-plain gap-3 font-semibold">
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="wipMessage" />
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="calendarAppMessage" />
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="configMessage" />
		<ul class="list-disc">
			<li v-for="missingConfig in missingConfigs" :key="missingConfig">
				{{ t(APP_ID, missingConfig) }}
			</li>
		</ul>
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="howToMessage" />
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { generateUrl } from '@nextcloud/router'
import { APP_ID } from '../utils/appId.ts'
import { ADMIN_SETTINGS_PATH } from '../utils/url.ts'

defineProps<{
	missingConfigs: string[]
}>()

const settingsUrl = generateUrl(ADMIN_SETTINGS_PATH)

const wipMessage = t(
	APP_ID,
	'{strongStart}Note:{strongEnd} This app is work in progress.',
	{ strongStart: '<strong>', strongEnd: '</strong>' },
	{ escape: false },
)

const calendarAppMessage = t(
	APP_ID,
	'Make sure you have the {linkStart}calendar app{linkEnd} installed.',
	{ linkStart: '<a href="https://apps.nextcloud.com/apps/calendar" target="_blank" class="underline text-inherit">', linkEnd: '</a>' },
	{ escape: false, sanitize: false },
)

const configMessage = t(
	APP_ID,
	'The following configuration settings need to be set in the {linkStart}administration settings{linkEnd} before you can start using the app:',
	{ linkStart: `<a href="${settingsUrl}" class="underline text-inherit">`, linkEnd: '</a>' },
	{ escape: false },
)

const howToMessage = t(
	APP_ID,
	'{linkStart}Click here{linkEnd} to make yourself familiar with the app.',
	{ linkStart: '<a href="https://github.com/csoc-de/shiftsnext/blob/main/README.md" target="_blank" class="underline text-inherit">', linkEnd: '</a>' },
	{ escape: false, sanitize: false },
)
</script>
