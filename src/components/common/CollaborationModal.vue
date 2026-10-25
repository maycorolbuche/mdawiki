<script setup>
import { BModal } from 'bootstrap-vue-next'
import { useUiStore } from '@/stores/ui'
defineProps({ site: { type: Object, required: true } })
const ui = useUiStore()
</script>
<template>
  <BModal
    v-model="ui.helpOpen"
    :title="site.collaboration.title"
    centered
    ok-only
    ok-title="Entendi"
    ok-variant="success"
    ><p>{{ site.collaboration.description }}</p>
    <ol>
      <li v-for="step in site.collaboration.steps" :key="step">{{ step }}</li>
    </ol>
    <p v-if="site.collaboration.notice" class="demo-note mb-0">
      {{ site.collaboration.notice }}
    </p></BModal
  >
</template>
