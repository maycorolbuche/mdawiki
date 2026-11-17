// Labels, destinations and artwork from the legacy page_inc_menu.php.
export interface MenuItem { title: string; slug?: string; icon: string; children?: { title: string; slug: string }[]; legacy?: boolean }
const links = (titles: string[], prefix = '') => titles.map(title => ({ title, slug: (prefix + title).replaceAll(' ', '_') }))
export const menuSections: { title: string; items: MenuItem[] }[] = [
  { title: 'Desbravadores', items: [
    { title: 'Classes', icon: 'classe_dbv', children: links(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro', 'Excursionista', 'Guia', 'Classes Agrupadas', 'Líder', 'Líder Master', 'Líder Master Avançado'], 'Cartão de ') },
    { title: 'Especialidades', icon: 'especialidade_dbv', children: [
      ...links(['ADRA', 'Artes e Habilidades Manuais', 'Atividades Agrícolas', 'Atividades Missionárias e Comunitárias']),
      { title: 'Ensinos Bíblicos', slug: 'Atividades_Missionárias_e_Comunitárias_-_Ensinos_Biblicos' },
      ...links(['Atividades Profissionais', 'Atividades Recreativas', 'Ciência e Saúde', 'Estudos da Natureza', 'Habilidades Domésticas']),
      { title: 'Mestrados de Especialidades', slug: 'Mestrados' },
    ] },
  ] },
  { title: 'Aventureiros', items: [
    { title: 'Classes', icon: 'classe_avt', children: [...links(['Abelhinhas Laboriosas', 'Luminares', 'Edificadores', 'Mãos Ajudadoras'], 'Cartão de '), { title: 'Líder', slug: 'Cartão_de_Líder_de_Aventureiros' }] },
    { title: 'Especialidades', icon: 'especialidade_avt', children: [...links(['Artes Manuais', 'Atividades Espirituais']), ...['Atividades Recreativas', 'Estudos da Natureza', 'Habilidades Domésticas'].map(title => ({ title, slug: title.replaceAll(' ', '_') + '_(aventureiros)' }))] },
  ] },
  { title: 'Categorias', items: [
    ['Acampamento', 'acampamento'], ['Camporis', 'campori'], ['Cerimônias', 'cerimonias'], ['Civismo', 'civismo'], ['Hinos', 'hinos'], ['Histórico', 'historico'], ['Ideais', 'ideais'], ['Liderança', 'lideranca'], ['Nós e Amarras', 'noseamarras'], ['Ordem Unida', 'ordemunida'], ['Pessoas', 'pessoas'], ['Uniformes', 'uniforme'],
  ].map<MenuItem>(([title, icon]) => ({ title: title!, icon: icon!, slug: title === 'Uniformes' ? 'Uniforme' : title!.replaceAll(' ', '_') })).concat([{ title: 'Todas as Categorias', icon: 'categorias', slug: '@Categorias', legacy: true }]) },
]
