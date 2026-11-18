# Editor de código do legado

Origem: `maycorolbuche/mdawiki-site`, commit `233de1267fe20c1e219d373e51e3e2e787121c56`.

- Motor: `site/@lib/markitup/jquery.markitup.js` (markItUp! 1.1.x, licença MIT/GPL preservada no arquivo).
- Configuração: `site/@lib/markitup_codewiki/code_wiki.js`, com todos os comandos, atalhos, prompts, modelos e submenus originais.
- Estilos: skin markitup e `markitup_codewiki/estilo.css`, com os ícones e fundos locais correspondentes.

Adaptações de integração: exportação como módulos, variáveis locais no lugar de globais implícitas, evento keyup com namespace para limpeza ao desmontar e invocação direta de callbacks no lugar de eval. A configuração dos comandos não foi alterada. O wrapper Vue sincroniza inserções com o formulário e desmonta o plugin ao navegar. Os ajustes de largura responsiva e aparência da página ficam em `src/styles/main.css`.

jQuery 3.7.1 é carregado apenas junto do editor. Mantém as APIs usadas pelo plugin original, sem reintroduzir o jQuery 1.11.1 do site antigo. Não depende de scripts, imagens ou endpoints do legado em execução.
