<script setup>
import { ref } from 'vue'
import { BApp } from 'bootstrap-vue-next'
import AppHeader from './layout/AppHeader.vue'
import AppFooter from './layout/AppFooter.vue'
import AppSidebar from './layout/AppSidebar.vue'

const page = ref(null)
</script>

<template>
  <BApp>
    <AppHeader
      @navigate="page?.goTo?.($event)"
      @notice="page?.showNotice?.($event)"
      @open-discussion="page?.openDiscussion?.()"
      @open-history="page?.openHistory?.()"
      @open-help="page?.openHelp?.()"
    />
    <div class="workspace">
      <AppSidebar />
      <RouterView v-slot="{ Component, route }">
        <component :is="Component" :key="route.path" ref="page" />
      </RouterView>
    </div>
    <AppFooter @notice="page?.showNotice?.($event)" />
  </BApp>
</template>
