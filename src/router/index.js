import { createRouter, createWebHistory } from 'vue-router'
import WikiLayout from '@/layouts/WikiLayout.vue'
import { validAnchor, validSlug } from '@/utils/wikiRoutes'
const page = () => import('@/views/WikiPageView.vue')
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: WikiLayout,
      children: [
        { path: '', name: 'home', component: page, props: { slug: '' } },
        { path: 'busca', name: 'search', component: () => import('@/views/SearchView.vue') },
        {
          path: ':slug',
          name: 'wiki-page',
          component: page,
          props: true,
          beforeEnter: (to) =>
            validSlug(to.params.slug) || {
              name: 'not-found',
              params: { pathMatch: ['endereco-invalido'] },
            },
        },
        {
          path: ':pathMatch(.*)*',
          name: 'not-found',
          component: () => import('@/views/NotFoundView.vue'),
        },
      ],
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash && validAnchor(to.hash.slice(1)))
      return { el: `[id="${to.hash.slice(1)}"]`, top: 145 }
    if (to.path === from.path) return false
    return { top: 0 }
  },
})
export default router
