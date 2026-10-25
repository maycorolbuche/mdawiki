<script setup>
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { BButton } from 'bootstrap-vue-next'
import { useSiteStore } from '@/stores/site'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import CollaborationModal from '@/components/common/CollaborationModal.vue'
const store = useSiteStore()
onMounted(() => {
  if (!store.site) store.load()
})
</script>
<template>
  <a href="#conteudo" class="skip-link btn btn-forest">Pular para o conteúdo</a>
  <template v-if="store.site"
    ><AppHeader :site="store.site" />
    <div class="layout page-layout">
      <AppSidebar :site="store.site" />
      <div class="min-w-0"><RouterView /><AppFooter :site="store.site" /></div>
    </div>
    <CollaborationModal :site="store.site"
  /></template>
  <main v-else id="conteudo" class="main" tabindex="-1">
    <p v-if="store.loading" role="status">Carregando a wiki…</p>
    <template v-else-if="store.error"
      ><h1>Não foi possível carregar a wiki</h1>
      <p role="alert">{{ store.error.message }}</p>
      <BButton variant="outline-success" @click="store.load">Tentar novamente</BButton></template
    >
  </main>
</template>
