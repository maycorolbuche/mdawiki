import { createRouter, createWebHistory } from 'vue-router'
import WikiLayout from '@/layouts/WikiLayout.vue'
const community = () => import('@/views/CommunityView.vue')
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: WikiLayout,
      children: [
        {
          path: '',
          name: 'home',
          component: () => import('@/views/HomeView.vue'),
          meta: { title: 'Página inicial' },
        },
        {
          path: 'especialidades',
          name: 'specialties',
          component: () => import('@/views/SpecialtyListView.vue'),
          meta: { title: 'Especialidades' },
        },
        {
          path: 'especialidades/:slug',
          name: 'specialty',
          component: () => import('@/views/SpecialtyArticleView.vue'),
          props: true,
          meta: { title: 'Especialidade' },
        },
        {
          path: 'desbravadores',
          name: 'pathfinders',
          component: community,
          meta: { title: 'Desbravadores' },
        },
        {
          path: 'aventureiros',
          name: 'adventurers',
          component: community,
          meta: { title: 'Aventureiros' },
        },
        {
          path: 'comunidade/mudancas-recentes',
          name: 'recent-changes',
          component: community,
          meta: { title: 'Mudanças recentes' },
        },
        {
          path: 'comunidade/colaboradores',
          name: 'contributors',
          component: community,
          meta: { title: 'Colaboradores' },
        },
        { path: 'ajuda', name: 'help', component: community, meta: { title: 'Ajuda' } },
        {
          path: ':pathMatch(.*)*',
          name: 'not-found',
          component: () => import('@/views/NotFoundView.vue'),
          meta: { title: 'Página não encontrada' },
        },
      ],
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.hash) return { el: to.hash, top: 145, behavior: 'smooth' }
    return { top: 0 }
  },
})
router.afterEach((to) => {
  document.title = `${to.meta.title || 'Conhecimento compartilhado'} · MDAWiki`
})
export default router
