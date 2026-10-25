<script setup>
defineProps({
  id: { type: String, required: true },
  title: { type: String, required: true },
  icon: { type: String, default: '' },
  editable: { type: Boolean, default: false },
})
defineEmits(['edit'])
</script>
<template>
  <section :id="id" class="section-block" :aria-labelledby="`${id}-heading`">
    <div class="section-heading">
      <h2 :id="`${id}-heading`">
        <i v-if="icon" :class="`bi bi-${icon} me-2 fs-4`" aria-hidden="true" />{{ title }}
      </h2>
      <button
        v-if="editable"
        class="edit-section"
        :aria-label="`Editar seção ${title}`"
        @click="$emit('edit', id)"
      >
        <i class="bi bi-pencil me-1" aria-hidden="true" />Editar seção
      </button>
    </div>
    <slot />
  </section>
</template>
