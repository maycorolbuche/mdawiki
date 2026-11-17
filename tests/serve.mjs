import { createServer } from 'node:http'
import { readFile, stat } from 'node:fs/promises'
import { resolve, extname, sep } from 'node:path'
const root = resolve('dist')
createServer(async (req, res) => {
  try {
    let path = resolve(root, '.' + decodeURIComponent(new URL(req.url, 'http://local').pathname))
    if (!path.startsWith(root + sep) && path !== root) throw new Error('Forbidden')
    try {
      if ((await stat(path)).isDirectory()) path += '/index.html'
      const data = await readFile(path)
      res.setHeader('Content-Type', ({ '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css', '.xml': 'application/xml', '.svg': 'image/svg+xml', '.png': 'image/png', '.woff': 'font/woff', '.woff2': 'font/woff2' })[extname(path)] || 'application/octet-stream')
      res.end(data)
    } catch {
      res.writeHead(404, { 'Content-Type': 'text/html' })
      res.end(await readFile(resolve(root, '404.html')))
    }
  } catch { res.writeHead(400); res.end() }
}).listen(4173, '127.0.0.1')
