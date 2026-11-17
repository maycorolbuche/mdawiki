import { createApplication } from './app'
import type { Snapshot } from './types/page'
import publicConfig from '../config/public.json'
import 'bootstrap/dist/css/bootstrap.min.css'
import './styles/main.css'

const defaults: Snapshot = {
  page: null, pages: [], apiBaseUrl: import.meta.env.VITE_API_BASE_URL || publicConfig.apiBaseUrl,
  siteUrl: import.meta.env.VITE_SITE_URL || publicConfig.siteUrl, basePath: import.meta.env.BASE_URL,
}
let snapshot = defaults
const payload = document.getElementById('page-data')?.textContent
if (payload && !payload.includes('<!--page-data-->')) snapshot = JSON.parse(payload)
const { app, router } = createApplication(snapshot, false, Boolean(payload && !payload.includes('<!--page-data-->')))
await router.isReady()
app.mount('#app')
