<script setup lang="ts">
import { inject, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { SessionClient } from './services/session'
import { menuSections } from './config/navigation'
const route = useRoute()
const router = useRouter()
const session = inject<SessionClient>('session')!
onMounted(() => { void session.initialize().catch(() => {}) })
async function logout() { await session.logout(); await router.push('/') }
const menuOpen = ref(false)
const collapsed = ref(false)
const sidebarPreference = 'mdawiki.sidebar.collapsed'
onMounted(() => {
  try { collapsed.value = localStorage.getItem(sidebarPreference) === 'true' } catch { /* Storage can be disabled by the browser. */ }
})
watch(collapsed, value => {
  try { localStorage.setItem(sidebarPreference, String(value)) } catch { /* Keep the menu usable without storage. */ }
})
function expandGroup(event: MouseEvent) {
  if (!collapsed.value || !window.matchMedia('(min-width: 992px)').matches) return
  event.preventDefault()
  collapsed.value = false
  const details = (event.currentTarget as HTMLElement).closest('details')
  if (details) details.open = true
}
const search = ref('')
const base = import.meta.env.BASE_URL
watch(() => route.path, () => { menuOpen.value = false })
function submitSearch() {
  if (search.value.trim()) void router.push({ path: '/page@Busca', query: { q: search.value.trim() } })
}
</script>

<template>
  <a class="visually-hidden-focusable skip-link" href="#conteudo">Ir para o conteúdo</a>
  <div class="wiki-shell" :class="{ 'menu-collapsed': collapsed }">
    <header class="site-header">
      <div class="header-brand">
        <RouterLink to="/" aria-label="MDAWiki — Página inicial"><img :src="`${base}assets/logo_mdawiki.svg`" width="140" height="44" alt="MDAWiki"></RouterLink>
        <button class="menu-toggle desktop-menu-toggle" type="button" aria-label="Menu do site" :aria-expanded="!collapsed" aria-controls="site-menu" @click="collapsed = !collapsed"><span aria-hidden="true">☰</span></button>
        <button class="menu-toggle mobile-menu-toggle" type="button" aria-label="Menu do site" :aria-expanded="menuOpen" aria-controls="site-menu" @click="menuOpen = !menuOpen"><span aria-hidden="true">☰</span></button>
      </div>
      <div class="header-tools">
        <a class="facebook-link" href="https://www.facebook.com/mdawiki" target="_blank" rel="noopener noreferrer" aria-label="MDAWiki no Facebook">f</a>
        <form class="site-search" role="search" @submit.prevent="submitSearch"><label class="visually-hidden" for="site-search">Localizar na MDAWiki</label><input id="site-search" v-model="search" type="search" placeholder="Localizar…" autocomplete="off"><button class="visually-hidden" type="submit">Pesquisar</button></form>
        <nav class="account-links" aria-label="Conta"><template v-if="session.state.user"><RouterLink to="/page@Perfil">{{ session.state.user.APELIDO || session.state.user.NOME }}</RouterLink><button class="header-logout" @click="logout">Sair</button></template><template v-else><RouterLink to="/page@Acesso">Login</RouterLink><RouterLink to="/page@Cadastre-se">Cadastre-se</RouterLink></template></nav>
      </div>
    </header>
    <Transition name="menu-backdrop"><button v-if="menuOpen" class="menu-backdrop" aria-label="Fechar menu" @click="menuOpen = false" /></Transition>
    <aside id="site-menu" class="site-menu" :class="{ 'is-open': menuOpen }" aria-label="Menu do site">
      <nav>
        <RouterLink class="donation-link" to="/page@Doação" aria-label="Faça uma Doação" title="Faça uma Doação"><span class="menu-symbol" aria-hidden="true">▣</span><span class="menu-text">Faça uma Doação</span><span class="donation-badge">Doar</span></RouterLink>
        <section v-for="section in menuSections" :key="section.title" class="menu-section">
          <h2>{{ section.title }}</h2>
          <template v-for="item in section.items" :key="item.title">
            <details v-if="item.children" class="menu-group">
              <summary :aria-label="`${section.title} — ${item.title}`" :title="`${section.title} — ${item.title}`" @click="expandGroup"><img :src="`${base}assets/ico_${item.icon}.svg`" alt="" width="20" height="20"><span class="menu-text">{{ item.title }}</span><span class="menu-caret" aria-hidden="true">▾</span></summary>
              <ul><li v-for="child in item.children" :key="child.slug"><RouterLink :to="`/${encodeURIComponent(child.slug)}/`">{{ child.title }}</RouterLink></li></ul>
            </details>
            <RouterLink v-else-if="item.legacy" class="menu-link" to="/page@Listas?type=categorias" :aria-label="item.title" :title="item.title"><span class="menu-grid-icon" aria-hidden="true">▦</span><span class="menu-text">{{ item.title }}</span></RouterLink>
            <RouterLink v-else class="menu-link" :to="`/${encodeURIComponent(item.slug!)}/`" :aria-label="item.title" :title="item.title"><img :src="`${base}assets/ico_${item.icon}.svg`" alt="" width="20" height="20"><span class="menu-text">{{ item.title }}</span></RouterLink>
          </template>
        </section>
        <section class="menu-section"><h2>Sistema</h2>
          <RouterLink v-if="session.state.user" class="menu-link" to="/page@Criar" aria-label="Nova página" title="Nova página"><span class="menu-symbol" aria-hidden="true">＋</span><span class="menu-text">Nova página</span></RouterLink>
          <RouterLink class="menu-link" to="/page@Listas" aria-label="Todas as páginas" title="Todas as páginas"><span class="menu-symbol" aria-hidden="true">▤</span><span class="menu-text">Todas as páginas</span></RouterLink>
          <RouterLink class="menu-link" to="/page@Estatisticas" aria-label="Estatísticas" title="Estatísticas"><span class="menu-symbol" aria-hidden="true">▥</span><span class="menu-text">Estatísticas</span></RouterLink>
          <RouterLink v-if="session.state.user" class="menu-link" to="/page@Perfil" aria-label="Meu perfil" title="Meu perfil"><span class="menu-symbol" aria-hidden="true">●</span><span class="menu-text">Meu perfil</span></RouterLink>
          <RouterLink v-if="Number(session.state.user?.NIVEL) === 5" class="menu-link" to="/page@Usuarios" aria-label="Usuários" title="Usuários"><img :src="`${base}assets/ico_pessoas.svg`" alt="" width="20" height="20"><span class="menu-text">Usuários</span></RouterLink>
          <RouterLink v-if="Number(session.state.user?.NIVEL) === 5" class="menu-link" to="/page@Atualizar" aria-label="Atualizar páginas" title="Atualizar páginas"><span class="menu-symbol" aria-hidden="true">⟳</span><span class="menu-text">Atualizar páginas</span></RouterLink>
        </section>
        <div class="mobile-account-links"><template v-if="session.state.user"><RouterLink to="/page@Perfil">Meu perfil</RouterLink><button @click="logout">Sair</button></template><template v-else><RouterLink to="/page@Acesso">Login</RouterLink><RouterLink to="/page@Cadastre-se">Cadastre-se</RouterLink></template></div>
      </nav>
    </aside>
    <main id="conteudo" class="main-content" tabindex="-1"><RouterView /></main>
    <footer class="site-footer">MDAWiki · A Wikipedia dos Desbravadores e Aventureiros</footer>
  </div>
</template>


