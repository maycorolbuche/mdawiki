import { ref } from 'vue'
export function useClipboard() {
  const feedback = ref('')
  async function copy(text) {
    try {
      await navigator.clipboard.writeText(text)
      feedback.value = 'Copiado para a área de transferência.'
    } catch {
      feedback.value =
        'Não foi possível copiar. Selecione o endereço ou a referência e copie manualmente.'
    }
  }
  return { feedback, copy }
}
