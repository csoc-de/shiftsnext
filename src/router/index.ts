import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import { createRouter, createWebHistory } from 'vue-router'
import { APP_ID } from '../appId'
import type { Group } from '../models/group'

declare module 'vue-router' {
	interface RouteMeta {
		requiresShiftAdmin: boolean
	}
}

const shiftAdminGroups = loadState<Group[]>(APP_ID, 'shift_admin_groups', [])

const isShiftAdmin = shiftAdminGroups.length > 0

const router = createRouter({
	history: createWebHistory(generateUrl(`/apps/${APP_ID}`)),
	routes: [
		{
			path: '/shifts',
			name: 'shifts',
			component: () => import('../views/ShiftsView.vue'),
		},
		{
			path: '/shift-types',
			name: 'shift-types',
			component: () => import('../views/ShiftTypesView.vue'),
			meta: { requiresShiftAdmin: true },
		},
		{
			path: '/shift-exchanges',
			name: 'shift-exchanges',
			component: () => import('../views/ShiftExchangesView.vue'),
		},
	],
})

router.beforeEach((to) => {
	if (to.meta.requiresShiftAdmin && !isShiftAdmin) {
		return {
			path: '/shifts',
		}
	}
})

export default router
