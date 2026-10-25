import { articles } from './specialtySeed.js'
const seed = articles[0]
const ref = (slug, title, description = '') => ({ slug, title, description })
const summary = ref(seed.slug, seed.title, seed.description)
const section = (id, title, blocks) => ({ id, title, blocks })
const paragraph = (text) => ({ type: 'paragraph', text })
const base = (slug, title, type = 'article') => ({
  id: slug || 'home',
  slug,
  title,
  type,
  description: '',
  notice: 'Dados fictícios da prévia. Conteúdo sujeito à conferência nas fontes oficiais.',
  sections: [],
  toc: [],
  categories: [],
  relatedPages: [],
  backlinks: [],
  missingLinks: [],
  members: [],
  contributors: [],
  capabilities: { suggest: true, history: true, discussion: true },
  metadata: null,
  media: null,
})
const specialty = {
  ...base(seed.slug, seed.title, 'specialty'),
  description: seed.description,
  contributors: seed.contributors.map((initials) => ({
    initials,
    name: `Colaborador ${initials}`,
  })),
  categories: [
    ref('adra', 'ADRA'),
    ref('nivel-1', 'Nível 1'),
    ref('especialidades', 'Especialidades'),
  ],
  metadata: {
    fields: [
      { label: 'Área', value: seed.area },
      { label: 'Código', value: seed.code },
      { label: 'Nível', value: seed.level },
      { label: 'Ano', value: seed.year },
      { label: 'Origem', value: seed.origin },
    ],
    images: [
      { url: '/demo/pathfinders.png', alt: 'Emblema dos Desbravadores' },
      { url: '/demo/adra.png', alt: 'Emblema da especialidade ADRA' },
    ],
  },
  sections: [
    section('requisitos', 'Requisitos', [{ type: 'requirements', items: seed.requirements }]),
    section('materiais', 'Materiais de estudo', [{ type: 'resources', items: seed.resources }]),
    section('referencias', 'Referências', [{ type: 'references', items: seed.references }]),
  ],
  relatedPages: [ref('acampamento', 'Acampamento')],
  backlinks: [ref('adra', 'ADRA')],
}
const home = {
  ...base('', 'Conhecimento que se compartilha.', 'home'),
  capabilities: { suggest: false, history: false, discussion: false },
  description: 'Explore, aprenda e compartilhe experiências com a comunidade MDAWiki.',
  sections: [
    section('destaques', 'Explore a wiki', [
      {
        type: 'page-cards',
        items: [
          summary,
          ref('acampamento', 'Acampamento', 'Planejamento e convivência ao ar livre.'),
          ref('adra', 'ADRA', 'Artigos e especialidades de serviço à comunidade.'),
        ],
      },
    ]),
  ],
}
const category = {
  ...base('adra', 'ADRA', 'category'),
  description: 'Agência Adventista de Desenvolvimento e Recursos Assistenciais.',
  sections: [
    section('sobre', 'Sobre esta categoria', [
      paragraph('Conteúdos sobre ajuda humanitária, serviço e resposta a emergências.'),
    ]),
  ],
  members: [summary],
}
const camp = {
  ...base('acampamento', 'Acampamento'),
  description: 'Planejamento e convivência ao ar livre.',
  sections: [
    section('planejamento', 'Planejamento', [
      paragraph(
        'Conheça o local, organize a equipe e consulte fontes confiáveis antes de planejar uma atividade.',
      ),
      {
        type: 'links',
        items: [
          ref('adra', 'ADRA'),
          ref('artigo-a-criar', 'Artigo a criar', 'Exemplo de link para página inexistente.'),
        ],
      },
    ]),
    section('seguranca', 'Segurança', [
      paragraph('Este é um artigo genérico de exemplo, sem os metadados de uma especialidade.'),
    ]),
  ],
  categories: [ref('desbravadores', 'Desbravadores')],
  relatedPages: [summary],
  missingLinks: [ref('artigo-a-criar', 'Artigo a criar')],
  backlinks: [ref('desbravadores', 'Desbravadores')],
}
const image = {
  ...base('imagem-emblema-adra', 'Emblema ADRA', 'media'),
  media: {
    url: '/demo/adra.png',
    alt: 'Emblema ADRA da referência',
    width: 128,
    height: 128,
    format: 'PNG',
    attribution: 'Imagem extraída do HTML de referência.',
  },
  backlinks: [summary],
}
const help = {
  ...base('ajuda', 'Ajuda', 'article'),
  sections: [
    section('como-usar', 'Como usar', [
      paragraph(
        'Use a busca para encontrar artigos, categorias e imagens. As páginas têm endereços próprios na raiz da wiki.',
      ),
    ]),
    section('como-colaborar', 'Como colaborar', [
      paragraph(
        'Confira as fontes oficiais. Nesta etapa, as sugestões podem ser baixadas, mas não são publicadas.',
      ),
    ]),
  ],
}
export const pages = [
  home,
  specialty,
  category,
  camp,
  image,
  help,
  ...[
    ['especialidades', 'Especialidades', [summary]],
    ['desbravadores', 'Desbravadores', [ref('acampamento', 'Acampamento'), summary]],
    ['aventureiros', 'Aventureiros', []],
    ['nivel-1', 'Nível 1', [summary]],
    ['mudancas-recentes', 'Mudanças recentes', [summary]],
    ['colaboradores', 'Colaboradores', []],
  ].map(([slug, title, members]) => ({
    ...base(slug, title, 'category'),
    description: 'Coleção demonstrativa; os dados serão fornecidos pela API.',
    members,
  })),
]
for (const page of pages) page.toc = page.sections.map(({ id, title }) => ({ id, label: title }))
export const aliases = {
  Resposta_a_Emergências_e_Desastres: seed.slug,
  resposta_a_emergencias_e_desastres: seed.slug,
  Emergências: seed.slug,
  ADRA: 'adra',
  'imagem@Emblema_ADRA': 'imagem-emblema-adra',
  Acampamento: 'acampamento',
}
export const site = {
  name: 'MDAWiki',
  tagline: 'Conhecimento ao ar livre',
  logoUrl: '/favicon.svg',
  viewerLabel: 'Visitante',
  helpSlug: 'ajuda',
  motto: 'Conhecimento que se compartilha.',
  mottoDescription: 'Pessoas, experiências e recursos para um mundo melhor.',
  navigation: [
    {
      id: 'explore',
      label: 'Explorar',
      items: [
        { slug: '', label: 'Página inicial', icon: 'house-door' },
        { slug: 'especialidades', label: 'Especialidades', icon: 'compass-fill' },
        { slug: 'desbravadores', label: 'Desbravadores', icon: 'shield-check' },
        { slug: 'aventureiros', label: 'Aventureiros', icon: 'backpack' },
        { slug: 'adra', label: 'ADRA', icon: 'heart' },
        { slug: 'acampamento', label: 'Acampamento', icon: 'tree' },
      ],
    },
    {
      id: 'community',
      label: 'Comunidade',
      items: [
        { slug: 'mudancas-recentes', label: 'Mudanças recentes', icon: 'clock-history' },
        { slug: 'colaboradores', label: 'Colaboradores', icon: 'people' },
        { slug: 'ajuda', label: 'Ajuda', icon: 'question-circle' },
      ],
    },
  ],
  footer: {
    text: 'Conhecimento que se compartilha.',
    notice: 'Prévia com API simulada. Nenhuma edição é publicada.',
    links: [{ slug: 'ajuda', label: 'Ajuda' }],
  },
  collaboration: {
    title: 'Conhecimento se constrói junto',
    description: 'Compartilhe o que você sabe com a comunidade.',
    steps: [
      'Confira as fontes oficiais.',
      'Prepare uma sugestão de melhoria.',
      'Converse sobre o artigo na aba Discussão.',
    ],
    notice: 'Publicação, autenticação e revisão dependem da futura API.',
  },
}
export const revisions = [
  {
    id: 'r1',
    date: '2026-10-25T12:00:00-03:00',
    author: 'Equipe MDAWiki',
    summary: 'Criação da prévia (exemplo).',
  },
]
