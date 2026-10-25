<script setup>
import { safeUrl } from '@/utils/wikiRoutes'
defineProps({ metadata: { type: Object, required: true } })
</script>
<template>
  <div class="specialty-info" aria-label="Dados da página">
    <img
      v-if="safeUrl(metadata.images?.[0]?.url)"
      class="pathfinder"
      :src="safeUrl(metadata.images[0].url)"
      :alt="metadata.images[0].alt"
    />
    <dl class="metadata">
      <div v-for="field in metadata.fields" :key="field.label">
        <dt>{{ field.label }}</dt>
        <dd>{{ field.value }}</dd>
      </div>
    </dl>
    <img
      v-if="safeUrl(metadata.images?.[1]?.url)"
      class="patch"
      :src="safeUrl(metadata.images[1].url)"
      :alt="metadata.images[1].alt"
    />
  </div>
</template>
