<script setup>
import { BButton } from 'bootstrap-vue-next'
defineProps({ loading: Boolean, error: { type: Object, default: null } })
defineEmits(['retry'])
</script>
<template>
  <p v-if="loading" role="status">Carregando…</p>
  <div v-else-if="error" role="alert">
    <p>{{ error.message }}</p>
    <BButton variant="outline-success" @click="$emit('retry')">Tentar novamente</BButton>
  </div>
  <slot v-else />
</template>
