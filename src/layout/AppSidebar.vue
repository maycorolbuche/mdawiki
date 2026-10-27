<script setup>
import { onMounted, onUnmounted } from 'vue'
import { BOffcanvas } from 'bootstrap-vue-next'
import SiteMenu from './SiteMenu.vue'

defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['update:open'])
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
@media print {
  .left-sidebar { display: none !important; }
}
</style>
