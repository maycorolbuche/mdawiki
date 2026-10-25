<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import { BOffcanvas } from 'bootstrap-vue-next'
import { navigationGroups } from '@/data/navigation'
import { useUiStore } from '@/stores/ui'
const ui = useUiStore()
const route = useRoute()
watch(
  () => route.fullPath,
  () => {
    ui.sidebarOpen = false
  },
)
</script>
<template>
  <BOffcanvas id="sidebar" v-model="ui.sidebarOpen" responsive="lg" title="MDAWiki" class="sidebar">
    <div class="sidebar-inner">
      <template v-for="(group, index) in navigationGroups" :key="group.label"
        ><hr v-if="index" />
        <div class="nav-label">{{ group.label }}</div>
        <nav class="nav flex-column side-nav" :aria-label="group.label">
          <RouterLink
            v-for="item in group.items"
            :key="item.name"
            :to="{ name: item.name }"
            class="nav-link"
            :class="{ active: item.name === 'specialties' && route.name === 'specialty' }"
            :exact-active-class="'active'"
            active-class=""
            ><i :class="`bi bi-${item.icon}`" aria-hidden="true" />{{ item.label }}</RouterLink
          >
        </nav>
      </template>
      <div class="sidebar-motto mt-auto pt-5 px-2">
        <div class="tree-scene" aria-hidden="true">
          <i class="bi bi-tree" /><i class="bi bi-tree-fill" /><i class="bi bi-tree" />
        </div>
        <p class="aside-motto">Conhecimento<br />que se compartilha.</p>
        <p class="small text-secondary">Pessoas, experiências e recursos para um mundo melhor.</p>
      </div>
    </div>
  </BOffcanvas>
</template>
