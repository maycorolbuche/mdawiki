<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
defineProps<{ title: string }>()
const emit = defineEmits<{ close: [] }>()
const dialog = ref<HTMLDialogElement>()
let overflow = '', focus: HTMLElement | null = null
onMounted(() => {
  focus = document.activeElement as HTMLElement | null
  overflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'
  dialog.value!.showModal()
})
onBeforeUnmount(() => {
  dialog.value?.close()
  document.body.style.overflow = overflow
  if (focus?.isConnected) focus.focus()
})
</script>
<template>
  <dialog ref="dialog" class="workspace-modal" aria-labelledby="workspace-modal-title" @cancel.prevent="emit('close')">
    <div class="modal-content">
      <header class="modal-header"><h2 id="workspace-modal-title" class="modal-title fs-5">{{ title }}</h2><button type="button" class="btn-close" aria-label="Fechar" @click="emit('close')" /></header>
      <div class="modal-body"><slot /></div>
      <footer class="modal-footer"><button type="button" class="btn btn-secondary" @click="emit('close')">Fechar</button></footer>
    </div>
  </dialog>
</template>
<style scoped>
.workspace-modal { width: min(1140px, calc(100vw - 24px)); max-width: none; max-height: calc(100dvh - 24px); padding: 0; border: 0; border-radius: 6px; color: inherit; overflow-y: auto; }
.workspace-modal::backdrop { background: #0009; }
.modal-header, .modal-body, .modal-footer { padding: 20px; }
.modal-body { min-width: 0; overflow-wrap: anywhere; }
.modal-title { margin: 0; }
@media(max-width:575px) { .modal-header, .modal-body, .modal-footer { padding: 15px; } }
</style>
