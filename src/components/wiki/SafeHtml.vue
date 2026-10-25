<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { sanitizeHtml } from '@/utils/sanitizeHtml'
const props = defineProps({ html: { type: String, default: '' } })
const router = useRouter()
const clean = computed(() => sanitizeHtml(props.html))
function navigate(event) {
  const link = event.target.closest('a[href]')
  if (
    !link ||
    event.defaultPrevented ||
    event.button !== 0 ||
    event.metaKey ||
    event.ctrlKey ||
    event.shiftKey ||
    event.altKey
  )
    return
  const url = new URL(link.href, window.location.href)
  if (url.origin !== window.location.origin) return
  event.preventDefault()
  router.push(`${url.pathname}${url.search}${url.hash}`)
}
</script>
<template><div class="wiki-html" @click="navigate" v-html="clean" /></template>
