import { createRouter, createWebHistory } from 'vue-router'
import ArticleView from '../views/ArticleView.vue'

export default createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'home', component: ArticleView, props: { example: 'emergencias' } },
    { path: '/exemplos/acampamento', name: 'example-camping', component: ArticleView, props: { example: 'acampamento' } },
    { path: '/exemplos/natureza', name: 'example-nature', component: ArticleView, props: { example: 'natureza' } },
  ],
})
