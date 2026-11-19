<script setup lang="ts">
import { ref, watch } from 'vue'
const props = defineProps<{ photo?: string | null }>()
const failed = ref(false)
const fallback = import.meta.env.BASE_URL + 'assets/user.png'
watch(() => props.photo, () => { failed.value = false })
</script>

<template>
  <img class="history-author-avatar" :src="photo && !failed ? photo : fallback" alt="" width="32" height="32" loading="lazy" @error="failed = true">
</template>

<style scoped>
.history-author-avatar { width: 32px; height: 32px; flex: 0 0 32px; border-radius: 50%; object-fit: cover; }
</style>
