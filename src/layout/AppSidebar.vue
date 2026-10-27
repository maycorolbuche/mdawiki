<script setup>
// Destinos do menu atual da MDAWiki, até a integração das páginas nesta aplicação.
const siteUrl = 'https://mda.wiki.br/'
const menuGroups = [
  {
    title: 'Desbravadores',
    sections: [
      {
        title: 'Classes',
        icon: 'bi-award',
        items: [
          ['Amigo', 'Cartão_de_Amigo'],
          ['Companheiro', 'Cartão_de_Companheiro'],
          ['Pesquisador', 'Cartão_de_Pesquisador'],
          ['Pioneiro', 'Cartão_de_Pioneiro'],
          ['Excursionista', 'Cartão_de_Excursionista'],
          ['Guia', 'Cartão_de_Guia'],
          ['Classes Agrupadas', 'Cartão_de_Classes_Agrupadas'],
          ['Líder', 'Cartão_de_Líder'],
          ['Líder Master', 'Cartão_de_Líder_Master'],
          ['Líder Master Avançado', 'Cartão_de_Líder_Master_Avançado'],
        ],
      },
      {
        title: 'Especialidades',
        icon: 'bi-patch-check',
        items: [
          ['ADRA', 'ADRA'],
          ['Artes e Habilidades Manuais', 'Artes_e_Habilidades_Manuais'],
          ['Atividades Agrícolas', 'Atividades_Agrícolas'],
          ['Atividades Missionárias e Comunitárias', 'Atividades_Missionárias_e_Comunitárias'],
          ['Ensinos Bíblicos', 'Atividades_Missionárias_e_Comunitárias_-_Ensinos_Biblicos'],
          ['Atividades Profissionais', 'Atividades_Profissionais'],
          ['Atividades Recreativas', 'Atividades_Recreativas'],
          ['Ciência e Saúde', 'Ciência_e_Saúde'],
          ['Estudos da Natureza', 'Estudos_da_Natureza'],
          ['Habilidades Domésticas', 'Habilidades_Domésticas'],
          ['Mestrados de Especialidades', 'Mestrados'],
        ],
      },
    ],
  },
  {
    title: 'Aventureiros',
    sections: [
      {
        title: 'Classes',
        icon: 'bi-award',
        items: [
          ['Abelhinhas Laboriosas', 'Cartão_de_Abelhinhas_Laboriosas'],
          ['Luminares', 'Cartão_de_Luminares'],
          ['Edificadores', 'Cartão_de_Edificadores'],
          ['Mãos Ajudadoras', 'Cartão_de_Mãos_Ajudadoras'],
          ['Líder', 'Cartão_de_Líder_de_Aventureiros'],
        ],
      },
      {
        title: 'Especialidades',
        icon: 'bi-patch-check',
        items: [
          ['Artes Manuais', 'Artes_Manuais'],
          ['Atividades Espirituais', 'Atividades_Espirituais'],
          ['Atividades Recreativas', 'Atividades_Recreativas_(aventureiros)'],
          ['Estudos da Natureza', 'Estudos_da_Natureza_(aventureiros)'],
          ['Habilidades Domésticas', 'Habilidades_Domésticas_(aventureiros)'],
        ],
      },
    ],
  },
]
const categories = [
  ['Acampamento', 'Acampamento', 'bi-tree'],
  ['Camporis', 'Camporis', 'bi-flag'],
  ['Cerimônias', 'Cerimônias', 'bi-stars'],
  ['Civismo', 'Civismo', 'bi-flag'],
  ['Hinos', 'Hinos', 'bi-music-note-beamed'],
  ['Histórico', 'Histórico', 'bi-clock-history'],
  ['Ideais', 'Ideais', 'bi-lightbulb'],
  ['Liderança', 'Liderança', 'bi-people'],
  ['Nós e Amarras', 'Nós_e_Amarras', 'bi-link-45deg'],
  ['Ordem Unida', 'Ordem_Unida', 'bi-list-ol'],
  ['Pessoas', 'Pessoas', 'bi-person'],
  ['Uniformes', 'Uniforme', 'bi-person-badge'],
  ['Todas as Categorias', '@Categorias', 'bi-grid'],
]
</script>

<template>
  <aside class="left-sidebar d-none d-xl-block" aria-label="Navegação do site">
    <nav class="site-menu p-3" aria-label="Menu do site">
      <a class="menu-link donate-link" :href="siteUrl + 'page@Doação'">
        <i class="bi bi-heart" aria-hidden="true"></i>
        <span>Faça uma Doação</span>
        <span class="badge text-bg-primary ms-auto">Doar</span>
      </a>

      <section v-for="group in menuGroups" :key="group.title" class="menu-group">
        <h2 class="menu-heading">{{ group.title }}</h2>
        <details v-for="section in group.sections" :key="section.title" class="menu-section">
          <summary class="menu-link">
            <i class="bi" :class="section.icon" aria-hidden="true"></i>
            <span>{{ section.title }}</span>
            <i class="bi bi-chevron-down menu-chevron ms-auto" aria-hidden="true"></i>
          </summary>
          <ul class="submenu">
            <li v-for="[label, path] in section.items" :key="path">
              <a :href="siteUrl + path">{{ label }}</a>
            </li>
          </ul>
        </details>
      </section>

      <section class="menu-group">
        <h2 class="menu-heading">Categorias</h2>
        <a v-for="[label, path, icon] in categories" :key="path" class="menu-link" :href="siteUrl + path">
          <i class="bi" :class="icon" aria-hidden="true"></i><span>{{ label }}</span>
        </a>
      </section>
    </nav>
  </aside>
</template>

<style lang="scss" scoped>
.left-sidebar {
  position: fixed;
  top: var(--header-height);
  bottom: 0;
  left: 0;
  z-index: 2;
  width: 250px;
  overflow-y: auto;
  background: #fff;
  border-right: 1px solid #e1e7ec;
}
@media (min-width: 1200px) and (max-width: 1399.98px) {
  .left-sidebar { width: 210px; }
}
.menu-group {
  margin-top: 1.25rem;
}
.menu-heading {
  color: var(--wiki-dark);
  font-size: 0.875rem;
  font-weight: 650;
  margin: 0 0 0.5rem;
}
.menu-link {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.625rem 0.125rem;
  color: #59635f;
  font-size: 0.75rem;
  text-decoration: none;
  cursor: pointer;
}
.menu-link:hover, .submenu a:hover {
  color: var(--bs-primary);
}
.menu-link > .bi:first-child {
  font-size: 1.1875rem;
  flex: none;
}
.menu-section summary {
  list-style: none;
}
.menu-section summary::-webkit-details-marker {
  display: none;
}
.menu-chevron {
  font-size: 0.7rem;
}
.menu-section[open] .menu-chevron {
  transform: rotate(180deg);
}
.submenu {
  list-style: none;
  padding: 0 0 0 2rem;
  margin: 0;
}
.submenu a {
  display: block;
  color: #59635f;
  font-size: 0.75rem;
  padding: 0.4rem 0;
  text-decoration: none;
}
@media print {
  .left-sidebar { display: none !important; }
}
</style>


