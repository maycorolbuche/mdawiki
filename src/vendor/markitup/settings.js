const myBbcodeSettings = {
  nameSpace:          "mdawiki",
  //previewParserPath:  "~/sets/bbcode/preview.php",
  //previewInWindow: 'width=1000, height=600, resizable=yes, scrollbars=yes',
  previewAutoRefresh: true,
  markupSet: [
      {name:'T&iacute;tulo', openWith:"==", closeWith:"==", className:"m_h1"}, 
      {name:'Subt&iacute;tulo', openWith:"===", closeWith:"===", className:"m_h2"}, 
	  {name:'T&iacute;tulo N&iacute;vel 3', openWith:"====", closeWith:"====", className:"m_h3"}, 
      {separator:'---------------' },
      {name:'Negrito', key:'B', openWith:"'''", closeWith:"'''", className:"m_negrito"}, 
      {name:'It&aacute;lico', key:'I', openWith:"''", closeWith:"''", className:"m_italico"},
      {name:'Sublinhado', key:'U', openWith:'__', closeWith:'__', className:"m_sublinhado"}, 
      {separator:'---------------' },
	  {name:'Tamanho da Fonte', openWith:'{{TamanhoTexto:[![Tamanho\u0020do\u0020Texto\u0020(1\u0020a\u00207)]!]}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size", dropMenu: [
          {name:'Tamanho 1', openWith:'{{TamanhoTexto:1}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 2', openWith:'{{TamanhoTexto:2}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 3', openWith:'{{TamanhoTexto:3}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 4', openWith:'{{TamanhoTexto:4}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 5', openWith:'{{TamanhoTexto:5}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 6', openWith:'{{TamanhoTexto:6}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"},
		  {name:'Tamanho 7', openWith:'{{TamanhoTexto:7}}', closeWith:'{{/TamanhoTexto}}', className:"m_font_size"}
      ]},
	  {name:'Cor da Fonte', openWith:'{{Cor:[![Cor\u0020da\u0020Fonte]!]}}', closeWith:'{{/Cor}}', className:"m_cor"},
	  {separator:'---------------' },
	  {name:'Alinhar ao Centro', openWith:'{{Centro}}', closeWith:'{{/Centro}}', className:"m_text_center"},
	  {name:'Alinhar \xE0 Direita', openWith:'{{Direita}}', closeWith:'{{/Direita}}', className:"m_text_right"},
      {name:'Colunas', openWith:'{{Colunas:[![Qtd.\u0020Colunas]!]}}\n', closeWith:'{{/Colunas}}', className:"m_colunas"}, 
      {name:'Tabela', replaceWith:'{|class=\"tabela-dados\"\n|!!-\n|Celula 1,1\n|Celula 2,1\n|!!-\n|Celula 2,1\n|Celula 2,2\n|}', className:"m_tabela", dropMenu: [
          {name:'Tabela simples', replaceWith:'{|class=\"tabela-dados\"\n|!!-\n|Celula 1,1\n|Celula 2,1\n|!!-\n|Celula 2,1\n|Celula 2,2\n|}', className:"m_submenu"},
          {name:'Tabela de imagens', replaceWith:'{|class=\"tabela-imgs\" style=\"width: 100%;\"\n|!class=\"backg1\"!-\n|Imagem 1,1\n|Imagem 2,1\n|!class=\"backg2\"!-\n|Celula 2,1\n|Celula 2,2\n|}', className:"m_submenu"},
          {name:'Tabela de dados', replaceWith:'{|class=\"tabela-artigo\" style=\"width: 100%;\"\n|!!-\n|Titulo 1,1\n|Titulo 2,1\n|!!-\n|Celula 2,1\n|Celula 2,2\n|}', className:"m_submenu"},
          {name:'Tabela sem bordas', replaceWith:'{|\n|!!-\n|Celula 1,1\n|Celula 2,1\n|!!-\n|Celula 2,1\n|Celula 2,2\n|}', className:"m_submenu"},
      ]}, 
	  {separator:'---------------' },
	  {name:'Subscrito', openWith:'{{Sub}}', closeWith:'{{/Sub}}', className:"m_text_sub"}, 
	  {name:'Sobrescrito', openWith:'{{Sup}}', closeWith:'{{/Sup}}', className:"m_text_sup"}, 
	  {separator:'---------------' },
	  {name:'Quebra de Linha', replaceWith:'-//-', className:"m_quebra_linha"},
	  {name:'Linha', replaceWith:'---', className:"m_linha"},
	  {separator:'---------------' },
      {name:'Lista', openWith:'(!(*|!|*)!)', className:"m_lista"}, 
      {name:'Lista Num&eacute;rica', openWith:'(!(#|!|#)!)', className:"m_lista_numerica", dropMenu: [
          {name:'Iniciar lista em...', replaceWith:'[[#=[![Iniciar.\u0020em]!]]]', className:"m_submenu"}
      ]},
      {name:'Lista Alfab&eacute;tica', openWith:'(!(@|!|@)!)', className:"m_lista_alfabetica", dropMenu: [
          {name:'Iniciar lista em...', replaceWith:'[[@=[![Iniciar.\u0020em]!]]]', className:"m_submenu"}
      ]},
      {name:'Lista Romana', openWith:'(!(%|!|%)!)', className:"m_lista_romana", dropMenu: [
          {name:'Iniciar lista em...', replaceWith:'[[%=[![Iniciar.\u0020em]!]]]', className:"m_submenu"}
      ]},
      {separator:'---------------' },
      {name:'Imagem', replaceWith:'{{Imagem:[![Nome\u0020da\u0020Imagem]!]}}', className:"m_imagem"}, 
      {name:'V&iacute;deo', replaceWith:'{{Video:[![Link\u0020do\u0020V\u00eddeo]!]}}', className:"m_video"}, 
      {separator:'---------------' },
      {name:'Link', replaceWith:'[[[![Nome\u0020da\u0020P\u00e1gina]!]]]', className:"m_link", dropMenu: [
          {name:'Converter em link...', openWith:"[[", closeWith:"]]", className:"m_submenu"},
          {name:'Adicionar link mascarado...', replaceWith:'[[[![Nome\u0020da\u0020P\u00e1gina]!]|[![Texto]!]]]', className:"m_submenu"},
          {name:'Adicionar site', replaceWith:'[[[![Site:!:http://]!]]]', className:"m_submenu m_link_externo"}
      ]},
      {name:'Links Notas', replaceWith:'{{Mais Links:[![Digite\u0020os\u0020par\u00e2metros\u0020dos\u0020links]!]}}', className:"m_addlink", dropMenu: [
          {name:'Desambigua&ccedil;&atilde;o', replaceWith:'{{Mais Links: Desambiguacao=[![P\u00e1gina\u0020de\u0020Desambigua\u00e7\u00e3o]!]}}', className:"m_submenu"},
          {name:'Veja Tamb&eacute;m', replaceWith:'{{Mais Links: Links=[![Digite\u0020os\u0020links\u0020separados\u0020por\u0020v\u00edrgula]!]}}', className:"m_submenu"},
          {name:'Desambigua&ccedil;&atilde;o + Veja Tamb&eacute;m', replaceWith:'{{Mais Links: Desambiguacao=[![P\u00e1gina\u0020de\u0020Desambigua\u00e7\u00e3o]!]\n|Links=[![Digite\u0020os\u0020links\u0020separados\u0020por\u0020v\u00edrgula]!]\n}}', className:"m_submenu"},
          {name:'Veja Tamb&eacute;m (texto personalizado)', replaceWith:'{{Mais Links: Texto=[![Texto]!]\n|Links=[![Digite\u0020os\u0020links\u0020separados\u0020por\u0020v\u00edrgula]!]\n}}', className:"m_submenu"},
		  {name:'Refer&ecirc;ncias', replaceWith:'{{Referencias:\nTexto=[![Texto]!]\n|Link=[![Link]!]\n|Texto=[![Texto\u00202]!]\n|Link=[![Link\u00202]!]\n|Texto=[![Texto\u00203]!]\n|Link=[![Link\u00203]!]\n}}', className:"m_submenu"},
		  {name:'Refer&ecirc;ncias (t&iacute;tulo personalizado)', replaceWith:'{{Referencias:\nTitulo=[![T\u00edtulo]!]\n|Texto=[![Texto]!]\n|Link=[![Link]!]\n|Texto=[![Texto\u00202]!]\n|Link=[![Link\u00202]!]\n|Texto=[![Texto\u00203]!]\n|Link=[![Link\u00203]!]\n}}', className:"m_submenu"}
      ]},
      {separator:'---------------' },
      {name:'Objeto', replaceWith:'{{[![Digite\u0020os\u0020par\u00e2metros\u0020do\u0020objeto]!]}}', className:"m_objeto", dropMenu: [
          {name:'Especialidade Desbravadores', replaceWith:'{{Especialidade:Area=[![\u00c1rea]!]|Icone=[![Imagem]!]|Cod=[![C\u00f3digo]!]|Nivel=[![N\u00edvel]!]|Ano=[![Ano]!]|Origem=[![Origem]!]}}', className:"m_submenu m_especialidade_dbv"},
          {name:'Especialidade Aventureiros', replaceWith:'{{Especialidade:Clube=AVT|Area=[![\u00c1rea]!]|Icone=[![Imagem]!]|Cod=[![C\u00f3digo]!]}}', className:"m_submenu m_especialidade_avt"},
          {name:'Especialidade Obsoleta', replaceWith:'{{Especialidade Obsoleta:Icone=[![Imagem]!]|Termino=[![Descontinuada\u0020em]!]}}', className:"m_submenu m_especialidade_obsoleta"},
          {name:'P&aacute;gina de Desambigua&ccedil;&atilde;o', replaceWith:'{{Desambiguacao:\n|Termo=[![P\u00e1gina\u0020de\u0020refer\u00eancia]!]\n|Links=[![Digite\u0020os\u0020links\u0020separados\u0020por\u0020v\u00edrgula]!]\n}}', className:"m_submenu m_desambiguacao"},
          {name:'Biografia', replaceWith:'{{Biografia:\n|Foto=[![Foto]!]\n|Nascimento=[![Data\u0020de\u0020Nascimento]!]\n|Morte=[![Data\u0020da\u0020Morte]!]\n|Feitos=[![Principais\u0020Feitos]!]\n\n|Cargo=[![Cargo\u00201]!]\n|Mandato=[![Per\u00edodo\u0020Mandato\u00201]!]\n|Antecessor=[![Antecessor\u00201]!]\n|Sucessor=[![Sucessor\u00201]!]\n\n|Cargo=[![Cargo\u00202]!]\n|Mandato=[![Per\u00edodo\u0020Mandato\u00202]!]\n|Antecessor=[![Antecessor\u00202]!]\n|Sucessor=[![Sucessor\u00202]!]\n}}', className:"m_submenu"}
      ]},
      {name:'Listas', replaceWith:'{{{[![Digite\u0020os\u0020par\u00e2metros\u0020da\u0020lista]!]}}}', className:"m_listas", dropMenu: [
          {name:'Listar Listas', replaceWith:'{{{Lista:Listas|colunas:2|exibeTotais:sim}}}', className:"m_submenu"},
          {name:'Listar Categorias', replaceWith:'{{{Lista:Categorias|colunas:2|exibeTotais:sim|mostraqtditens:sim}}}', className:"m_submenu"},
          {name:'Listar Documenta&ccedil;&atilde;o', replaceWith:'{{{Lista:Codigos|exibeTotais:sim}}}', className:"m_submenu"},
          {name:'Listar Links Inexistentes', replaceWith:'{{{Lista:Links Inexistentes|exibeTotais:sim|colunas:2|link_classe:link_nok}}}', className:"m_submenu"},
          {name:'Listar P&aacute;ginas', replaceWith:'{{{Lista:Paginas|exibeTotais:sim|colunas:2}}}', className:"m_submenu"},
          {name:'Listar P&aacute;ginas sem categoria', replaceWith:'{{{Lista:Paginas Sem Categoria|exibeTotais:sim|colunas:2}}}', className:"m_submenu"},
          {name:'Listar P&aacute;ginas de redirecionamento', replaceWith:'{{{Lista:Redirecionamentos|exibeTotais:sim|paginapara:sim}}}', className:"m_submenu"}
      ]},
      {name:'Categorias', openWith:'{{Categorias:[![Coloque\u0020as\u0020categorias\u0020separadas\u0020por\u0020v\u00edrgula]!]}}', className:"m_categorias"}, 
      {separator:'---------------' },
      {name:'Ignorar c&oacute;digo', openWith:'[codigo]',closeWith:'[/codigo]', className:"m_codigo"},
      {name:'Inserir anota&ccedil;&atilde;o (obs.: Anota&ccedil;&otilde;es n&atilde;o aparecem no texto publicado)', openWith:'/*',closeWith:'*/', className:"m_comentario"}, 
      {separator:'---------------' },
      {name:'Incluir p&aacute;gina', openWith:'{{Incluir:[![Nome\u0020da\u0020P\u00e1gina]!]}}', className:"m_include"}, 
      {name:'Redirecionar p&aacute;gina', openWith:'{{{Redirecionar:[![Nome\u0020da\u0020P\u00e1gina]!]}}}', className:"m_redirect"}, 
   ]
}
export default myBbcodeSettings

