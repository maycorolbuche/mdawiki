import { createApp, createSSRApp } from 'vue'
import { createRouter, createMemoryHistory, createWebHistory } from 'vue-router'
import App from './App.vue'
import ArticleView from './components/ArticleView.vue'
import HomeView from './components/HomeView.vue'
import type { Snapshot } from './types/page'

export function createApplication(snapshot: Snapshot, server = false, hydrate = true) {
  const router = createRouter({
    history: server ? createMemoryHistory(snapshot.basePath) : createWebHistory(snapshot.basePath),
    routes: [
      { path: '/', component: HomeView },
      { path: '/:slug(.*)', component: ArticleView },
    ],
    scrollBehavior(to, from, saved) {
      if (saved) return saved
      if (to.hash) return { el: to.hash, top: 80 }
      if (to.path !== from.path) return { top: 0 }
    },
  })
  const app = server || hydrate ? createSSRApp(App) : createApp(App)
  app.provide('snapshot', snapshot)
  app.use(router)
  return { app, router }
}
