import { getCurrentUser } from '@nextcloud/auth'
import type { User } from './models/user'

const user = getCurrentUser()

if (!user) {
	throw new Error('No authenticated user found')
}

const { uid, displayName } = user

/** The logged-in user */
export const authUser: User = {
	id: uid,
	display_name: displayName ?? uid,
}
