<template>
	<div class="w-full flex items-center justify-center">
		<p class="text-2xl text-nc-error">
			{{ t(APP_ID, 'Make sure to set the required config in the admin settings before using the app') }}
		</p>
	</div>
</template>

<script setup lang="ts">
import { t } from '@nextcloud/l10n'
import { APP_ID } from '../appId'
</script>
