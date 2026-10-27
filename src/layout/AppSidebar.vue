<script setup>
import { onMounted, onUnmounted } from 'vue'
import { BOffcanvas } from 'bootstrap-vue-next'
import SiteMenu from './SiteMenu.vue'
import TopMenu from './TopMenu.vue'

defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['update:open', 'navigate', 'notice', 'open-discussion', 'open-history', 'open-help'])
const close = () => emit('update:open', false)
const closeOnDesktop = () => { if (window.innerWidth >= 1200) close() }
onMounted(() => window.addEventListener('resize', closeOnDesktop))
onUnmounted(() => window.removeEventListener('resize', closeOnDesktop))
</script>

<template>
  <div class="left-sidebar-slot d-none d-xl-block">
    <aside class="left-sidebar" aria-label="Navegação do site">
      <SiteMenu />
    </aside>
  </div>
  <BOffcanvas id="menu-site-mobile" :model-value="open" placement="start" title="Menu do site"
    @update:model-value="emit('update:open', $event)">
    <div class="mobile-primary-menu px-3">
      <h2 class="mobile-menu-heading">Menu principal</h2>
      <TopMenu mobile @select="close" @navigate="emit('navigate', $event)"
        @notice="emit('notice', $event)" @open-discussion="emit('open-discussion')"
        @open-history="emit('open-history')" @open-help="emit('open-help')" />
    </div>
    <SiteMenu @navigate="close" />
  </BOffcanvas>
</template>

<style lang="scss" scoped>
.left-sidebar {
  position: fixed;
  top: var(--header-height);
  left: 0;
  bottom: 0;
  width: 250px;
  overflow-y: auto;
  overscroll-behavior: contain;
  background: #fff;
  border-right: 1px solid #e1e7ec;
  scrollbar-width: thin;
  scrollbar-color: #9bb6a6 #f4f7f5;
}
.left-sidebar::-webkit-scrollbar { width: 8px; }
.left-sidebar::-webkit-scrollbar-track { background: #f4f7f5; }
.left-sidebar::-webkit-scrollbar-thumb {
  background: #9bb6a6;
  border: 2px solid #f4f7f5;
  border-radius: 8px;
}
.left-sidebar::-webkit-scrollbar-thumb:hover { background: #658c75; }
@media (min-width: 1200px) and (max-width: 1399.98px) {
  .left-sidebar { width: 210px; }
}
.mobile-primary-menu { border-bottom: 1px solid #e1e7ec; padding-bottom: 0.75rem; }
.mobile-menu-heading { color: var(--wiki-dark); font-size: 0.875rem; font-weight: 650; margin: 0 0 0.5rem; }
@media print {
  .left-sidebar-slot { display: none !important; }
}
</style>

