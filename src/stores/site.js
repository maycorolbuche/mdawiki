import { defineStore } from 'pinia'
import { ref, shallowRef } from 'vue'
import { wikiService } from '@/services/wikiService'
export const useSiteStore = defineStore('site', () => {
  const site = shallowRef(null)
  const loading = ref(false)
  const error = shallowRef(null)
  async function load() {
    if (loading.value) return
    loading.value = true
    error.value = null
    try {
      site.value = await wikiService.site()
    } catch (reason) {
      error.value = reason
    } finally {
      loading.value = false
    }
  }
  return { site, loading, error, load }
})
