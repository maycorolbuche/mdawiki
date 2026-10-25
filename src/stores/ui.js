import { defineStore } from 'pinia'
import { ref } from 'vue'
export const useUiStore = defineStore('ui', () => {
  const sidebarOpen = ref(false)
  const helpOpen = ref(false)
  return { sidebarOpen, helpOpen }
})
