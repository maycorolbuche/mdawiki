<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { menuSections } from './config/navigation'
const route = useRoute()
const menuOpen = ref(false)
const collapsed = ref(false)
const search = ref('')
const base = import.meta.env.BASE_URL
watch(() => route.path, () => { menuOpen.value = false })
function submitSearch() {
  if (search.value.trim()) window.location.assign('https://mda.wiki.br/page@Busca/buscar:' + encodeURIComponent(search.value.trim()))
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
        <nav class="account-links" aria-label="Conta"><a href="https://mda.wiki.br/page@Acesso">Login</a><a href="https://mda.wiki.br/page@Cadastre-se">Cadastre-se</a></nav>
      </div>
    </header>
    <button v-if="menuOpen" class="menu-backdrop" aria-label="Fechar menu" @click="menuOpen = false" />
    <aside id="site-menu" class="site-menu" :class="{ 'is-open': menuOpen }" aria-label="Menu do site">
      <nav>
        <a class="donation-link" href="https://mda.wiki.br/page@Doação"><span aria-hidden="true">▣</span> Faça uma Doação <span class="donation-badge">Doar</span></a>
        <RouterLink class="home-link" to="/">Página inicial</RouterLink>
        <section v-for="section in menuSections" :key="section.title" class="menu-section">
          <h2>{{ section.title }}</h2>
          <template v-for="item in section.items" :key="item.title">
            <details v-if="item.children" class="menu-group">
              <summary><img :src="`${base}assets/ico_${item.icon}.svg`" alt="" width="20" height="20"><span>{{ item.title }}</span><span class="menu-caret" aria-hidden="true">▾</span></summary>
              <ul><li v-for="child in item.children" :key="child.slug"><RouterLink :to="`/${encodeURIComponent(child.slug)}/`">{{ child.title }}</RouterLink></li></ul>
            </details>
            <a v-else-if="item.legacy" class="menu-link" :href="`https://mda.wiki.br/${encodeURIComponent(item.slug!)}`"><span class="menu-grid-icon" aria-hidden="true">▦</span>{{ item.title }}</a>
            <RouterLink v-else class="menu-link" :to="`/${encodeURIComponent(item.slug!)}/`"><img :src="`${base}assets/ico_${item.icon}.svg`" alt="" width="20" height="20">{{ item.title }}</RouterLink>
          </template>
        </section>
        <div class="mobile-account-links"><a href="https://mda.wiki.br/page@Acesso">Login</a><a href="https://mda.wiki.br/page@Cadastre-se">Cadastre-se</a></div>
      </nav>
    </aside>
    <main id="conteudo" class="main-content" tabindex="-1"><RouterView /></main>
    <footer class="site-footer">MDAWiki · A Wikipedia dos Desbravadores e Aventureiros</footer>
  </div>
</template>
