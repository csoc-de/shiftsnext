<template>
	<div class="w-full flex items-center justify-center">
		<h2 class="m-0 text-nc-plain">
			{{ t(APP_ID, 'Make sure to set the required config in the admin settings before using the app') }}
		</h2>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { APP_ID } from '../appId.ts'
</script>
