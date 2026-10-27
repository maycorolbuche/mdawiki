<script setup>
const emit = defineEmits(['notice'])
</script>

<template>
  <footer class="site-footer d-flex flex-wrap justify-content-between gap-3">
    <span>MDAWiki · Conhecimento construído em comunidade</span>
    <nav class="d-flex gap-4" aria-label="Rodapé">
      <a href="#" @click.prevent="emit('notice', 'Adicione a página Sobre.')">Sobre</a
      ><a href="#" @click.prevent="emit('notice', 'Adicione a política de privacidade.')"
        >Privacidade</a
      ><a href="#" @click.prevent="emit('notice', 'Adicione os canais de contato.')">Contato</a>
    </nav>
  </footer>
</template>

<style lang="scss" scoped>
.site-footer {
  grid-column: 1;
  border-top: 1px solid #dbe3e8;
  background: rgba(255, 255, 255, 0.8);
  padding: 17px 30px;
  font-size: 0.625rem;
  color: #66766b;
  a {
    color: inherit;
    text-decoration: none;
  }
}

@media (max-width: 575.98px) {
  .site-footer {
    padding: 17px 20px;
  }
}

@media print {
  .site-footer {
    display: none !important;
  }
}
</style>

