import { renderToString } from '@vue/server-renderer'
import { createApplication } from './app'
import type { Snapshot } from './types/page'

export async function render(path: string, snapshot: Snapshot): Promise<string> {
  const { app, router } = createApplication(snapshot, true)
  await router.push(path)
  await router.isReady()
  return renderToString(app)
}
