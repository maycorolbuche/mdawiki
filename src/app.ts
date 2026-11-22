import { createApp, createSSRApp } from 'vue'
import { createRouter, createMemoryHistory, createWebHistory } from 'vue-router'
import App from './App.vue'
import ArticleView from './components/ArticleView.vue'
import HomeView from './components/HomeView.vue'
import type { Snapshot } from './types/page'
import { encodedRoutes } from './config/routes'
import { createSession } from './services/session'
const AccountView = () => import('./components/AccountView.vue')
const ImageView = () => import('./components/ImageView.vue')
const WorkspaceView = () => import('./components/WorkspaceView.vue')
const DirectoryView = () => import('./components/DirectoryView.vue')

export function createApplication(snapshot: Snapshot, server = false, hydrate = true) {
  const router = createRouter({
    history: server ? createMemoryHistory(snapshot.basePath) : createWebHistory(snapshot.basePath),
    routes: encodedRoutes([
      { path: '/', alias: '/page@Inicio', component: HomeView },
      ...[
        ['Acesso','login'],['Cadastre-se','register'],['EsqueciMinhaSenha','forgot'],['RedefinirSenha','reset'],['AtivaConta','activate'],['Perfil','profile'],['AlterarSenha','password'],['AlterarEmail','email'],
      ].map(([path, action]) => ({ path: `/page@${path}/:extra?`, component: AccountView, meta: { action } })),
      { path: '/page@Busca/:term?', component: DirectoryView, meta: { action: 'search' } },
      { path: '/page@Listas', component: DirectoryView, meta: { action: 'catalog' } },
      { path: '/page@Criar', component: DirectoryView, meta: { action: 'create' } },
      ...[['Categorias','categorias'],['Páginas','paginas'],['Imagens','imagens'],['Vídeos','videos'],['Documentação','documentacao'],['Listas','listas'],['Links_Inexistentes','links_inexistentes'],['Páginas_Sem_Categoria','paginas_sem_categoria'],['Redirecionamentos','redirecionamentos'],['Imagens_sem_Página','imagens_sem_pagina'],['Imagens_Inexistentes','imagens_inexistentes']].map(([path, catalogType]) => ({ path: `/@${path}`, component: DirectoryView, meta: { action: 'catalog', catalogType } })),
      { path: '/page@Estatisticas', alias: '/page@Estatísticas', component: DirectoryView, meta: { action: 'statistics' } },
      { path: '/page@Usuarios', alias: '/page@Usuários', component: DirectoryView, meta: { action: 'users' } },
      { path: '/page@Atualizar', component: DirectoryView, meta: { action: 'rebuild' } },
      { path: '/page@Doação', alias: '/page@Doacao', component: DirectoryView, meta: { action: 'donate' } },
      { path: '/i/:slug', component: ImageView },
      { path: '/imagem@:slug', redirect: to => '/i/' + encodeURIComponent(String(to.params.slug)) },
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

