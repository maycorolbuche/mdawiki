<script setup>
import { BButton, BDropdown, BDropdownItem } from 'bootstrap-vue-next'
import { useUiStore } from '@/stores/ui'
import WikiSearch from '@/components/search/WikiSearch.vue'
import brand from '@/assets/images/brand.svg'
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
      ><i class="bi bi-list fs-4" aria-hidden="true"
    /></BButton>
    <RouterLink class="brand" :to="{ name: 'home' }" aria-label="MDAWiki, início"
      ><img :src="brand" class="brand-mark" alt="" /><span
        ><span class="brand-name d-block">MDAWiki</span
        ><span class="brand-tag d-block">Conhecimento ao ar livre</span></span
      ></RouterLink
    >
    <WikiSearch />
    <div class="top-actions d-flex align-items-center gap-3">
      <BButton class="btn-forest" @click="ui.helpOpen = true"
        ><i class="bi bi-pencil me-2" aria-hidden="true" />Contribuir</BButton
      >
      <BDropdown variant="link" no-caret aria-label="Notificações" end
        ><template #button-content
          ><i class="bi bi-bell fs-5" aria-hidden="true" /><span class="visually-hidden"
            >Notificações</span
          ></template
        >
        <li class="px-3 py-2 small text-secondary">
          Nenhuma notificação nesta demonstração.
        </li></BDropdown
      >
      <BDropdown variant="link" no-caret end aria-label="Menu do visitante"
        ><template #button-content
          ><span class="avatar">MW</span
          ><span class="visually-hidden">Menu do visitante</span></template
        >
        <li class="dropdown-header">Visitante · demonstração</li>
        <BDropdownItem :to="{ name: 'help' }">Como colaborar</BDropdownItem></BDropdown
      >
    </div>
  </header>
</template>
