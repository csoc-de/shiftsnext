import { generateUrl } from '@nextcloud/router'
import { createRouter, createWebHistory } from 'vue-router'
import ShiftExchangesView from '../views/ShiftExchangesView.vue'
import ShiftsView from '../views/ShiftsView.vue'
import ShiftTypesView from '../views/ShiftTypesView.vue'
import { APP_ID } from '../utils/appId.ts'
import { getInitialIsShiftAdmin } from '../utils/initialState.ts'

declare module 'vue-router' {
	interface RouteMeta {
		requiresShiftAdmin: boolean
	}
}

const isShiftAdmin = getInitialIsShiftAdmin()

const router = createRouter({
	history: createWebHistory(generateUrl(`/apps/${APP_ID}`)),
	routes: [
		{
			path: '/shifts',
			name: 'shifts',
			component: ShiftsView,
		},
		{
			path: '/shift-types',
			name: 'shift-types',
			component: ShiftTypesView,
			meta: { requiresShiftAdmin: true },
		},
		{
			path: '/shift-exchanges',
			name: 'shift-exchanges',
			component: ShiftExchangesView,
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
