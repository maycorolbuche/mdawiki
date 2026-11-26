import { createApp, createSSRApp } from 'vue'
import { createRouter, createMemoryHistory, createWebHistory } from 'vue-router'
import App from './App.vue'
import ArticleView from './components/ArticleView.vue'
import HomeView from './components/HomeView.vue'
import type { Snapshot } from './types/page'
import { encodedRoutes, legacySystemRoutes } from './config/routes'
import { createSession } from './services/session'
const AdminView = () => import('./components/AdminView.vue')
const AccountView = () => import('./components/AccountView.vue')
const PublicProfileView = () => import('./components/PublicProfileView.vue')
const ImageView = () => import('./components/ImageView.vue')
const WorkspaceView = () => import('./components/WorkspaceView.vue')
const DirectoryView = () => import('./components/DirectoryView.vue')

export function createApplication(snapshot: Snapshot, server = false, hydrate = true) {
  const router = createRouter({
    history: server ? createMemoryHistory(snapshot.basePath) : createWebHistory(snapshot.basePath),
    routes: encodedRoutes([
      { path: '/', alias: '/p/inicio', component: HomeView },
      ...[
        ['login','login'],['cadastro','register'],['esqueci-minha-senha','forgot'],['redefinir-senha','reset'],['ativar-conta','activate'],['perfil','profile'],['alterar-senha','password'],['alterar-email','email'],
      ].map(([path, action]) => ({ path: `/p/${path}/:extra?`, component: AccountView, meta: { action } })),
      { path: '/p/busca/:term?', component: DirectoryView, meta: { action: 'search' } },
      { path: '/p/listas', component: DirectoryView, meta: { action: 'catalog' } },
      { path: '/p/criar', component: DirectoryView, meta: { action: 'create' } },
      ...[['Categorias','categorias'],['Páginas','paginas'],['Imagens','imagens'],['Vídeos','videos'],['Documentação','documentacao'],['Listas','listas'],['Links_Inexistentes','links_inexistentes'],['Páginas_Sem_Categoria','paginas_sem_categoria'],['Redirecionamentos','redirecionamentos'],['Imagens_sem_Página','imagens_sem_pagina'],['Imagens_Inexistentes','imagens_inexistentes']].map(([path, catalogType]) => ({ path: `/@${path}`, component: DirectoryView, meta: { action: 'catalog', catalogType } })),
      { path: '/p/estatisticas', component: DirectoryView, meta: { action: 'statistics' } },
      { path: '/p/usuarios', component: DirectoryView, meta: { action: 'users' } },
      { path: '/p/atualizar', component: DirectoryView, meta: { action: 'rebuild' } },
      { path: '/p/doacao', component: DirectoryView, meta: { action: 'donate' } },
      ...legacySystemRoutes(),
      { path: '/a', component: AdminView },
      { path: '/a/pages', component: AdminView, meta: { adminPages: true } },
      { path: '/a/users', component: AdminView, meta: { adminUsers: true } },
      { path: '/a/files', component: AdminView, meta: { adminFiles: true } },
      { path: '/u/:publicId', component: PublicProfileView },
      { path: '/i/:slug', component: ImageView },
      { path: '/i/:slug/historico', component: ImageView, meta: { imageHistory: true } },
      { path: '/imagem@:slug/:action?', redirect: to => '/i/' + encodeURIComponent(String(to.params.slug)) + (to.params.action === 'historico' ? '/historico' : '') },
      { path: '/:slug/editar', component: WorkspaceView, meta: { action: 'editor' } },
      { path: '/:slug/historico', component: WorkspaceView, meta: { action: 'history' } },
      { path: '/:slug(.*)', component: ArticleView },
    ]),
    scrollBehavior(to, from, saved) {
      if (saved) return saved
      if (to.hash) return { el: to.hash, top: 80 }
      if (to.path !== from.path) return { top: 0 }
    },
  })
  const app = server || hydrate ? createSSRApp(App) : createApp(App)
  app.provide('snapshot', snapshot)
  app.provide('session', createSession(snapshot))
  app.use(router)
  return { app, router }
}

