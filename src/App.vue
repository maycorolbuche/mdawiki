<script setup lang="ts">
import { ref } from 'vue'
const menuOpen = ref(false)
</script>

<template>
  <a class="visually-hidden-focusable p-2" href="#conteudo">Ir para o conteúdo</a>
  <header class="site-header border-bottom">
    <div class="container-fluid d-flex align-items-center gap-3 py-3">
      <RouterLink class="brand text-decoration-none" to="/">MDA<span>Wiki</span></RouterLink>
      <span class="text-muted d-none d-md-inline">Tudo sobre Desbravadores e Aventureiros</span>
      <button class="btn btn-outline-secondary ms-auto d-lg-none" type="button" :aria-expanded="menuOpen" aria-controls="site-menu" @click="menuOpen = !menuOpen">Menu</button>
    </div>
  </header>
  <div class="site-layout container-fluid">
    <aside id="site-menu" class="site-menu" :class="{ 'is-open': menuOpen }" aria-label="Menu do site">
      <nav class="nav flex-column gap-1" @click="menuOpen = false">
        <RouterLink class="nav-link" to="/">Página inicial</RouterLink>
      </nav>
    </aside>
    <main id="conteudo" class="main-content" tabindex="-1"><RouterView /></main>
  </div>
  <footer class="site-footer border-top text-muted">MDAWiki · A Wikipedia dos Desbravadores e Aventureiros</footer>
</template>
