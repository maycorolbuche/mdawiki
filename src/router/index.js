import { createRouter, createWebHistory } from 'vue-router'
import PageView from '../views/PageView.vue'

export default createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [{ path: '/:pathMatch(.*)*', name: 'page', component: PageView }],
})
