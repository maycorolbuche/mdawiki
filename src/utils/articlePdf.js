import htmlToPdfmake from 'html-to-pdfmake'

/** Convert article HTML into a PDF model. Does not mount the HTML in the page. */
export function createArticlePdfDefinition({ title, html, sourceUrl = '', windowObject = window }) {
  if (!title?.trim() || !html?.trim()) throw new Error('Título e conteúdo são obrigatórios.')

  const container = windowObject.document.createElement('template')
  container.innerHTML = html
  container.content
    .querySelectorAll('script, style, iframe, button, form, nav, [data-pdf-exclude]')
    .forEach((element) => element.remove())
  container.content.querySelectorAll('a[href]').forEach((element) => {
    if (!/^(https?:|mailto:|#)/i.test(element.getAttribute('href'))) element.removeAttribute('href')
  })

  const content = htmlToPdfmake(container.innerHTML, {
    window: windowObject,
    tableAutoSize: true,
    defaultStyles: {
      h1: { fontSize: 20, bold: true, color: '#07523b', margin: [0, 16, 0, 8] },
      h2: { fontSize: 15, bold: true, color: '#07523b', margin: [0, 16, 0, 8] },
      h3: { fontSize: 12, bold: true, margin: [0, 12, 0, 6] },
      p: { margin: [0, 0, 0, 8] },
      th: { bold: true, fillColor: '#07523b', color: '#ffffff' },
      a: { color: '#07523b', decoration: 'underline' },
    },
  })

  return {
    pageSize: 'A4',
    pageMargins: [42, 62, 42, 54],
    info: { title, author: 'MDAWiki', subject: 'Artigo da MDAWiki' },
    defaultStyle: { font: 'Roboto', fontSize: 10, color: '#303940', lineHeight: 1.25 },
    header: {
      text: 'MDAWiki | Conhecimento construído em comunidade',
      margin: [42, 24, 42, 0],
      fontSize: 9,
      bold: true,
      color: '#07523b',
    },
    footer: (currentPage, pageCount) => ({
      columns: [
        { text: 'MDAWiki', color: '#66766b' },
        { text: `Página ${currentPage} de ${pageCount}`, alignment: 'right', color: '#66766b' },
      ],
      margin: [42, 16, 42, 0],
      fontSize: 8,
    }),
    content: [
      { text: title, fontSize: 22, bold: true, color: '#07523b', margin: [0, 0, 0, 16] },
      ...content,
      ...(sourceUrl
        ? [
            {
              text: `Fonte: ${sourceUrl}`,
              link: sourceUrl,
              fontSize: 8,
              color: '#66766b',
              margin: [0, 20, 0, 0],
            },
          ]
        : []),
    ],
    // Mark a heading with class="pdf-page-break" to start a new page.
    pageBreakBefore: (node) => Array.isArray(node.style) && node.style.includes('pdf-page-break'),
  }
}

/** Libraries and bundled fonts are loaded only when a download is requested. */
export async function downloadArticlePdf({ filename = 'artigo-mdawiki.pdf', ...article }) {
  const [{ default: pdfMake }, { default: pdfFonts }] = await Promise.all([
    import('pdfmake/build/pdfmake'),
    import('pdfmake/build/vfs_fonts'),
  ])
  pdfMake.addVirtualFileSystem(pdfFonts)
  const definition = createArticlePdfDefinition(article)
  await pdfMake.createPdf(definition).download(filename)
}
