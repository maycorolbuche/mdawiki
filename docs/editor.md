# Edição demonstrativa

O botão Editar e os lápis das seções abrem o mesmo formulário da página completa. Ele permite alterar título, tipo, categorias separadas por vírgulas e conteúdo HTML. Salvar atualiza a página em memória; cancelar descarta o rascunho. Recarregar ou navegar para outra página restaura os exemplos. Não há chamadas à API.

O editor usa Tiptap para Vue 3, com StarterKit configurado. A barra oferece parágrafos, H1–H6, negrito, itálico, listas, citações e desfazer/refazer. Links existentes são preservados quando seguros. A política central em `src/utils/articleHtml.js` permite apenas p, br, strong, em, h1–h6, ul, ol, li, blockquote e a, com atributos href/title. DOMPurify remove scripts, eventos, estilos, classes, imagens e elementos não autorizados. O schema do editor também normaliza conteúdo colado.

Os dados específicos da especialidade (emblemas e tabela) continuam fora do corpo editável. A primeira abertura converte as seções demonstrativas atuais em HTML editável, sem os botões. Depois do primeiro salvamento, o corpo passa a ser renderizado como HTML sanitizado.

Ao integrar a API, validar tipos/categorias e aplicar a mesma política no servidor antes de persistir ou devolver HTML. O filtro no navegador não substitui validação no backend. Os tipos atuais são exemplos: specialty, person, article e club.

Verificação: `node --test tests/articleHtml.test.mjs`.
