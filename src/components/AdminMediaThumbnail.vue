<script setup lang="ts">
import { inject, onBeforeUnmount, onMounted, ref } from 'vue'
import type { SessionClient } from '../services/session'

const props = defineProps<{ mediaKey: string; location?: string }>()
const api = inject<SessionClient>('session')!
const thumbnail = ref<HTMLElement>()
const image = ref('')
let observer: IntersectionObserver | undefined

async function load() {
  observer?.disconnect()
  const query = new URLSearchParams({ media_key: props.mediaKey })
  if (props.location) query.set('location', props.location)
  try {
    const data = await api.request('/admin/files?' + query)
    image.value = data.image
  } catch {
    image.value = ''
  }
}

onMounted(() => {
  if (!thumbnail.value || typeof IntersectionObserver === 'undefined') {
    void load()
    return
  }
  observer = new IntersectionObserver(entries => {
    if (entries.some(entry => entry.isIntersecting)) void load()
  })
  observer.observe(thumbnail.value)
})
onBeforeUnmount(() => observer?.disconnect())
</script>

<template>
  <span ref="thumbnail" class="media-thumbnail d-inline-flex align-items-center justify-content-center rounded bg-light">
    <img v-if="image" :src="image" alt="" width="56" height="56" @error="image = ''">
    <i v-else class="bi bi-image text-secondary" aria-hidden="true" />
  </span>
</template>

<style scoped>
.media-thumbnail { width: 56px; height: 56px; }
.media-thumbnail img { object-fit: contain; }
</style>
