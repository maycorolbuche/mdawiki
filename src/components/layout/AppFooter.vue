<script setup>
import { pageLocation } from '@/utils/wikiRoutes'
defineProps({ site: { type: Object, required: true } })
</script>
<template>
  <footer class="site-footer">
    <strong>{{ site.name }}</strong> · {{ site.footer.text }}
    <p v-if="site.footer.notice" class="mb-0 mt-1">{{ site.footer.notice }}</p>
    <nav class="d-flex gap-3 mt-2" aria-label="Rodapé">
      <RouterLink
        v-for="link in site.footer.links"
        :key="link.slug"
        :to="pageLocation(link.slug)"
        >{{ link.label }}</RouterLink
      >
    </nav>
  </footer>
</template>
