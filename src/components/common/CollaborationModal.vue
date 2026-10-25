<script setup>
import { BModal } from 'bootstrap-vue-next'
import { useUiStore } from '@/stores/ui'
const ui = useUiStore()
</script>
<template>
  <BModal
    v-model="ui.helpOpen"
    title="Conhecimento se constrói junto"
    centered
    ok-only
    ok-title="Entendi"
    ok-variant="success"
    ><p>Uma wiki reúne contribuições de pessoas que compartilham o que sabem.</p>
    <ol>
      <li>Confira as fontes e os requisitos oficiais.</li>
      <li>Prepare sugestões com fontes confiáveis.</li>
      <li>Converse na aba Discussão sobre melhorias.</li>
    </ol>
    <p class="demo-note mb-0">
      Demonstração do layout. Autenticação, publicação e revisão serão conectadas à API.
    </p></BModal
  >
</template>
