<script setup>
import { computed } from 'vue'
import { pageLocation, safeUrl, validSlug } from '@/utils/wikiRoutes'
const props = defineProps({ link: { type: Object, required: true } })
const target = computed(() =>
  props.link.slug === '' || validSlug(props.link.slug) ? pageLocation(props.link.slug) : null,
)
const external = computed(() => safeUrl(props.link.url))
</script>
<template>
  <RouterLink v-if="target" :to="target"
    ><slot>{{ link.title || link.label }}</slot></RouterLink
  ><a v-else-if="external" :href="external" target="_blank" rel="noopener noreferrer"
    ><slot>{{ link.title || link.label }}</slot></a
  ><span v-else
    ><slot>{{ link.title || link.label }}</slot></span
  >
</template>
