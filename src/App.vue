<script setup>
import { RouterView } from 'vue-router'
import AppHeader from './layout/AppHeader.vue'
import AppSidebar from './layout/AppSidebar.vue'
import AppFooter from './layout/AppFooter.vue'
</script>

<template>
  <AppHeader />
  <AppSidebar />
  <RouterView />
  <AppFooter />
</template>
