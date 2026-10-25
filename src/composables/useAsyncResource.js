import { onScopeDispose, ref, shallowRef, toValue, watch } from 'vue'
export function useAsyncResource(key, loader) {
  const data = shallowRef(null)
  const loading = ref(false)
  const error = shallowRef(null)
  let controller
  let version = 0
  async function load() {
    const current = ++version
    controller?.abort()
    controller = new AbortController()
    const value = toValue(key)
    data.value = null
    error.value = null
    if (value === null || value === undefined) {
      loading.value = false
      return
    }
    loading.value = true
    try {
      const result = await loader(value, { signal: controller.signal })
      if (current === version) data.value = result
    } catch (reason) {
      if (current === version && reason.name !== 'AbortError') error.value = reason
    } finally {
      if (current === version) loading.value = false
    }
  }
  watch(() => toValue(key), load, { immediate: true })
  onScopeDispose(() => {
    version++
    controller?.abort()
  })
  return { data, loading, error, reload: load }
}
