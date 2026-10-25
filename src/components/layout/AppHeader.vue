<script setup>
import { BButton, BDropdown, BDropdownItem } from 'bootstrap-vue-next'
import { useUiStore } from '@/stores/ui'
import WikiSearch from '@/components/search/WikiSearch.vue'
import { pageLocation, safeUrl } from '@/utils/wikiRoutes'
defineProps({ site: { type: Object, required: true } })
const ui = useUiStore()
</script>
<template>
  <header class="topbar sticky-top d-flex align-items-center gap-4 px-4">
    <BButton
      variant="link"
      class="icon-btn d-lg-none"
      aria-label="Abrir navegação"
      aria-controls="sidebar"
      :aria-expanded="ui.sidebarOpen"
      @click="ui.sidebarOpen = true"
      ><i class="bi bi-list fs-4" aria-hidden="true" /></BButton
    ><RouterLink class="brand" :to="pageLocation('')" :aria-label="`${site.name}, início`"
      ><img
        v-if="safeUrl(site.logoUrl)"
        :src="safeUrl(site.logoUrl)"
        class="brand-mark"
        alt=""
      /><span
        ><span class="brand-name d-block">{{ site.name }}</span
        ><span class="brand-tag d-block">{{ site.tagline }}</span></span
      ></RouterLink
    ><WikiSearch />
    <div class="top-actions d-flex align-items-center gap-3">
      <BButton class="btn-forest" @click="ui.helpOpen = true"
        ><i class="bi bi-pencil me-2" aria-hidden="true" />Contribuir</BButton
      ><BDropdown variant="link" no-caret end aria-label="Menu do visitante"
        ><template #button-content
          ><i class="bi bi-person-circle fs-4" aria-hidden="true" /><span class="visually-hidden"
            >Menu do visitante</span
          ></template
        >
        <li class="dropdown-header">{{ site.viewerLabel }}</li>
        <BDropdownItem :to="pageLocation(site.helpSlug)">Como colaborar</BDropdownItem></BDropdown
      >
    </div>
  </header>
</template>
