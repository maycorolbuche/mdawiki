<script setup>
import { watch } from 'vue'
import { useRoute } from 'vue-router'
import { BOffcanvas } from 'bootstrap-vue-next'
import { useUiStore } from '@/stores/ui'
import { pageLocation } from '@/utils/wikiRoutes'
defineProps({ site: { type: Object, required: true } })
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
  <BOffcanvas
    id="sidebar"
    v-model="ui.sidebarOpen"
    responsive="lg"
    :title="site.name"
    class="sidebar"
    header-close-label="Fechar navegação"
    ><div class="sidebar-inner">
      <template v-for="(group, index) in site.navigation" :key="group.id"
        ><hr v-if="index" />
        <div class="nav-label">{{ group.label }}</div>
        <nav class="nav flex-column side-nav" :aria-label="group.label">
          <RouterLink
            v-for="item in group.items"
            :key="item.slug"
            :to="pageLocation(item.slug)"
            class="nav-link"
            exact-active-class="active"
            active-class=""
            ><i :class="`bi bi-${item.icon || 'file-earmark-text'}`" aria-hidden="true" />{{
              item.label
            }}</RouterLink
          >
        </nav></template
      >
      <div class="sidebar-motto mt-auto pt-5 px-2">
        <div class="tree-scene" aria-hidden="true">
          <i class="bi bi-tree" /><i class="bi bi-tree-fill" /><i class="bi bi-tree" />
        </div>
        <p class="aside-motto">{{ site.motto }}</p>
        <p class="small text-secondary">{{ site.mottoDescription }}</p>
      </div>
    </div></BOffcanvas
  >
</template>
