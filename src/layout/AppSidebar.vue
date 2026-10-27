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
  <aside class="left-sidebar d-none d-xl-block" aria-label="Navegação do site">
    <SiteMenu />
  </aside>
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
  position: sticky;
  top: var(--header-height);
  margin-top: -24px;
  height: calc(100vh - var(--header-height));
  max-height: calc(100vh - var(--header-height));
  overflow-y: auto;
  background: #fff;
  border-right: 1px solid #e1e7ec;
}
.mobile-primary-menu { border-bottom: 1px solid #e1e7ec; padding-bottom: 0.75rem; }
.mobile-menu-heading { color: var(--wiki-dark); font-size: 0.875rem; font-weight: 650; margin: 0 0 0.5rem; }
@media print {
  .left-sidebar { display: none !important; }
}
</style>
