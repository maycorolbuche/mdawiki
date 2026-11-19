<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import Cropper, { DEFAULT_TEMPLATE } from 'cropperjs'
const props = defineProps<{ source: string; busy: boolean; error: string }>()
const emit = defineEmits<{ close: []; confirm: [data: string] }>()
const dialog = ref<HTMLDialogElement>(), container = ref<HTMLDivElement>()
const ready = ref(false), working = ref(false), cropError = ref('')
let cropper: Cropper | undefined
let disposed = false
let previousOverflow = '', previousFocus: HTMLElement | null = null
function close() { if (!props.busy && !working.value) emit('close') }
function zoom(amount: number) { cropper?.getCropperImage()?.$zoom(amount) }
function rotate() { cropper?.getCropperImage()?.$rotate('90deg') }
function reset() { cropper?.getCropperImage()?.$resetTransform().$center('cover'); cropper?.getCropperSelection()?.$reset() }
onMounted(async () => {
  previousFocus = document.activeElement as HTMLElement | null
  previousOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'
  dialog.value!.showModal()
  try {
    const image = new Image()
    image.alt = 'Foto selecionada para recorte'
    image.src = props.source
    const template = DEFAULT_TEMPLATE.replace('<cropper-image ', '<cropper-image initial-center-size="cover" ')
      .replace('<cropper-selection ', '<cropper-selection aspect-ratio="1" initial-aspect-ratio="1" keyboard ')
    cropper = new Cropper(image, { container: container.value!, template })
    await cropper.getCropperImage()!.$ready()
    if (disposed) return
    ready.value = true
  } catch { if (!disposed) cropError.value = 'Não foi possível abrir esta imagem. Escolha outro arquivo.' }
})
onBeforeUnmount(() => {
  disposed = true
  cropper?.destroy()
  dialog.value?.close()
  document.body.style.overflow = previousOverflow
  if (previousFocus?.isConnected) previousFocus.focus()
})
async function confirm() {
  if (!ready.value || props.busy || working.value) return
  working.value = true; cropError.value = ''
  try {
    const selection = cropper?.getCropperSelection()
    if (!selection || selection.width <= 0 || selection.height <= 0) throw new Error('No selection')
    const canvas = await selection.$toCanvas({ width: 512, height: 512 })
    if (!disposed) emit('confirm', canvas.toDataURL('image/png'))
  } catch { cropError.value = 'Não foi possível recortar esta imagem. Ajuste a seleção ou escolha outro arquivo.' }
  finally { working.value = false }
}
</script>

<template>
  <dialog ref="dialog" class="avatar-crop-dialog" aria-labelledby="avatar-crop-title" aria-describedby="avatar-crop-help" @cancel.prevent="close">
    <div class="modal-content">
      <div class="modal-header"><h2 id="avatar-crop-title" class="modal-title fs-5">Recortar foto de perfil</h2><button type="button" class="btn-close" aria-label="Fechar recorte" :disabled="busy || working" @click="close" /></div>
      <div class="modal-body">
        <p id="avatar-crop-help">Arraste a imagem e ajuste a área de recorte. A foto será quadrada.</p>
        <p v-if="cropError || error" class="alert alert-danger" role="alert">{{ cropError || error }}</p>
        <div ref="container" class="avatar-crop-container" :aria-busy="!ready" />
        <p v-if="!ready && !cropError" role="status">Carregando imagem…</p>
        <div class="crop-tools" role="group" aria-label="Ajustar recorte">
          <button type="button" class="btn btn-outline-secondary" aria-label="Ampliar imagem" :disabled="!ready || busy || working" @click="zoom(.1)"><i class="bi bi-zoom-in" aria-hidden="true" /></button>
          <button type="button" class="btn btn-outline-secondary" aria-label="Reduzir imagem" :disabled="!ready || busy || working" @click="zoom(-.1)"><i class="bi bi-zoom-out" aria-hidden="true" /></button>
          <button type="button" class="btn btn-outline-secondary" :disabled="!ready || busy || working" @click="rotate"><i class="bi bi-arrow-clockwise" aria-hidden="true" /> Girar</button>
          <button type="button" class="btn btn-outline-secondary" :disabled="!ready || busy || working" @click="reset">Reiniciar</button>
        </div>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" :disabled="busy || working" @click="close">Cancelar</button><button type="button" class="btn btn-primary" :disabled="!ready || busy || working" @click="confirm">{{ busy ? 'Enviando…' : working ? 'Recortando…' : 'Recortar e salvar' }}</button></div>
    </div>
  </dialog>
</template>

<style scoped>
.avatar-crop-dialog { width: min(760px, calc(100vw - 24px)); max-width: none; max-height: calc(100dvh - 24px); padding: 0; border: 0; border-radius: 8px; color: #555; overflow-y: auto; }
.avatar-crop-dialog::backdrop { background: #0009; }
.avatar-crop-container { height: min(420px, 48dvh); min-height: 180px; background: #222; }
.avatar-crop-container :deep(cropper-canvas) { height: 100%; width: 100%; }
.crop-tools { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.modal-header, .modal-footer { padding: 16px; }
.modal-body { padding: 16px; }
@media(max-width:575px) { .modal-footer .btn { flex: 1; } }
</style>
