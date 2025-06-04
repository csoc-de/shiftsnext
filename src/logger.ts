import { getLoggerBuilder } from '@nextcloud/logger'
import { APP_ID } from './appId.ts'

const logger = getLoggerBuilder().setApp(APP_ID).setLogLevel(0).detectUser().build()

export { logger }
