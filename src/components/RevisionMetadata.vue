<script setup lang="ts">
import AuthorAvatar from './AuthorAvatar.vue'
defineProps<{ revision?: { author?: string | null; author_photo?: string | null; created_at?: string | null } | null }>()
</script>
<template>
  <div class="revision-metadata">
    <span class="revision-metadata-author"><AuthorAvatar :photo="revision?.author_photo" /><span>{{ revision?.author || 'Usuário' }}</span></span>
    <span class="revision-metadata-date"><i class="bi bi-clock" aria-hidden="true"></i> {{ revision?.created_at || 'Data não informada' }}</span>
  </div>
</template>
<style scoped>
.revision-metadata { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 18px; margin: 10px 0 14px; font-size: 13px; font-weight: 400; }
.revision-metadata-author { display: inline-flex; align-items: center; gap: 12px; min-width: 0; overflow-wrap: anywhere; }
.revision-metadata-date { overflow-wrap: anywhere; }
</style>
