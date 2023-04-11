import {createRouter, createWebHistory} from 'vue-router'
import PassengerView from '../views/PassengerView.vue'
import CheckoutView from '../views/CheckoutView.vue'
import ItemView from '../views/ItemView.vue'
import MapView from '../views/MapView.vue'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/',
            name: 'Map',
            component: MapView
        },
        {
            path: '/items/:itemId',
            name: 'Item',
            component: ItemView
        },
        {
            path: '/passengers',
            name: 'Passengers',
            component: PassengerView
        },
        {
            path: '/checkout',
            name: 'Checkout',
            component: CheckoutView
        }
    ]
})

export default router
