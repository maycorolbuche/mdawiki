import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import PageView from '../views/PageView.vue'
import AboutView from '../views/AboutView.vue'

export default createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: HomeView },
    { path: '/p/sobre', name: 'about', component: AboutView },
    { path: '/:pathMatch(.*)*', name: 'page', component: PageView },
  ],
})
