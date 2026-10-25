<script setup>
import { RouterView } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppSidebar from '@/components/layout/AppSidebar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import CollaborationModal from '@/components/common/CollaborationModal.vue'
</script>
<template>
  <a href="#conteudo" class="skip-link btn btn-forest">Pular para o conteúdo</a>
  <AppHeader />
  <div class="layout page-layout">
    <AppSidebar />
    <div class="min-w-0"><RouterView /><AppFooter /></div>
  </div>
  <CollaborationModal />
</template>
