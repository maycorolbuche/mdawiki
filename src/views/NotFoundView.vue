<template>
  <main id="conteudo" class="main" tabindex="-1">
    <span class="category-label">404</span>
    <h1>Página não encontrada</h1>
    <p>O endereço não corresponde a uma página da MDAWiki.</p>
    <RouterLink :to="{ name: 'home' }">Voltar ao início</RouterLink>
  </main>
</template>
