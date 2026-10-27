<script setup>
import { ref } from 'vue'
import { BApp } from 'bootstrap-vue-next'
import AppHeader from './layout/AppHeader.vue'
import AppFooter from './layout/AppFooter.vue'
import AppSidebar from './layout/AppSidebar.vue'

const page = ref(null)
const isSiteMenuOpen = ref(false)
</script>

<template>
  <BApp>
    <AppHeader
      :site-menu-open="isSiteMenuOpen"
      @open-site-menu="isSiteMenuOpen = true"
      @navigate="page?.goTo?.($event)"
      @notice="page?.showNotice?.($event)"
      @open-discussion="page?.openDiscussion?.()"
      @open-history="page?.openHistory?.()"
      @open-help="page?.openHelp?.()"
    />
    <div class="workspace">
      <AppSidebar v-model:open="isSiteMenuOpen"
        @navigate="page?.goTo?.($event)" @notice="page?.showNotice?.($event)"
        @open-discussion="page?.openDiscussion?.()" @open-history="page?.openHistory?.()"
        @open-help="page?.openHelp?.()" />
      <RouterView v-slot="{ Component, route }">
        <component :is="Component" :key="route.path" ref="page" />
      </RouterView>
    </div>
    <AppFooter @notice="page?.showNotice?.($event)" />
  </BApp>
</template>

