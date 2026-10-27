<script setup>
import { ref } from 'vue'
import { BApp } from 'bootstrap-vue-next'
import AppHeader from './layout/AppHeader.vue'
import AppFooter from './layout/AppFooter.vue'
import AppSidebar from './layout/AppSidebar.vue'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import 'bootstrap-icons/font/bootstrap-icons.css'

const page = ref(null)
</script>

<template>
  <BApp>
    <AppHeader
      @navigate="page?.goTo?.($event)"
      @notice="page?.showNotice?.($event)"
      @open-discussion="page?.openDiscussion?.()"
      @open-history="page?.openHistory?.()"
      @open-help="page?.openHelp?.()"
    />
    <div class="workspace">
      <AppSidebar />
      <RouterView v-slot="{ Component, route }">
        <component :is="Component" :key="route.path" ref="page" />
      </RouterView>
    </div>
    <AppFooter @notice="page?.showNotice?.($event)" />
  </BApp>
</template>

<style lang="scss">
// Tema global do Bootstrap. Para manter tudo em um arquivo nesta etapa, o
// Bootstrap é compilado aqui. Depois, mova estas linhas para um arquivo .scss global.
$primary: #07523b;
$secondary: #557b64;
$body-color: #303940;
$body-bg: #edf1f4;
$font-family-sans-serif: 'Open Sans', Helvetica, Arial, sans-serif;
$link-color: $primary;
$link-hover-color: #032d20;

@import 'bootstrap/scss/bootstrap';

@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Light.ttf') format('truetype');
  font-style: normal;
  font-weight: 300;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Regular.ttf') format('truetype');
  font-style: normal;
  font-weight: 400;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-SemiBold.ttf') format('truetype');
  font-style: normal;
  font-weight: 600;
  font-display: swap;
}
@font-face {
  font-family: 'Open Sans';
  src: url('./assets/fonts/OpenSans-Bold.ttf') format('truetype');
  font-style: normal;
  font-weight: 700;
  font-display: swap;
}

// Estilos próprios: apenas detalhes do layout que as utilidades não cobrem.
:root {
  --header-height: 122px;
  --wiki-line: #dfe5e9;
  --wiki-dark: #063e2e;
  --wiki-paper-width: 840px;
}

.workspace {
  display: grid;
  grid-template-columns: 250px minmax(0, 1fr) 264px;
  gap: 30px;
  max-width: 1800px;
  min-height: calc(100vh - 176px);
  margin: 0 auto;
  padding: 24px 22px 38px;
  align-items: start;
}
.left-sidebar,
.right-sidebar {
  top: calc(var(--header-height) + 22px);
}
.side-panel {
  border-color: #e1e7ec !important;
  hr {
    border-color: #c9d3d9;
    margin: 1.6rem 0;
  }
}
.panel-title {
  font-size: 0.875rem;
  font-weight: 650;
  color: var(--wiki-dark);
  margin: 0;
}
.toc {
  border-left: 1px solid #d8e0dc;
  a {
    display: block;
    position: relative;
    padding: 0.625rem 0.75rem 0.625rem 1.625rem;
    color: #5b6660;
    font-size: 0.75rem;
    text-decoration: none;
    &.active {
      color: var(--bs-primary);
      background: var(--bs-primary-bg-subtle);
      font-weight: 600;
      &::before {
        content: '';
        position: absolute;
        left: -2px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--bs-primary);
        border-radius: 3px;
      }
    }
  }
}
.article-container {
  max-width: var(--wiki-paper-width);
}
.paper {
  width: 100%;
  min-height: 1050px;
  padding: 0 52px 24px;
  h1,
  h2 {
    font-family: inherit;
    color: #777;
  }
  h1 {
    font-size: clamp(1.75rem, 2.5vw, 2rem);
    line-height: 1.2;
    font-weight: 300;
    margin: 0 0 1rem;
  }
  h2 {
    font-size: 1.5rem;
    font-weight: 400;
    margin: 0;
  }
}
.toolbar-edit {
  z-index: 2;
  height: 0;
  margin-right: -18px;
  .edit-btn {
    margin-top: 9px;
    text-decoration: none;
  }
}
.article-tabs-container {
  position: relative;
  > .nav {
    margin: 0 -30px 30px;
    padding-right: 80px;
    gap: 20px;
    border-bottom: 1px solid var(--wiki-line);
  }
  .nav-link {
    padding: 15px 3px;
    font-size: 0.75rem;
    white-space: nowrap;
    color: #66716a;
    background: none;
    border: 0;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    &.active {
      color: var(--bs-primary);
      background: none;
      border-bottom-color: var(--bs-primary);
      font-weight: 600;
    }
  }
}
.edit-btn {
  font-size: 0.75rem;
  white-space: nowrap;
}
.eyebrow {
  margin: 0 0 0.875rem;
  color: var(--bs-primary);
  font-size: 0.625rem;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  a {
    color: inherit;
    text-decoration: none;
  }
}
.article-intro,
.article-support {
  color: #777;
  font-size: 13px;
  line-height: 1.5;
}
.article-support a,
.article-copy a {
  color: #4a89dc;
  overflow-wrap: anywhere;
}
.article-divider {
  margin: 1.25rem 0;
  border-color: #ccc;
}
.specialty-wrapper {
  overflow-x: auto;
  margin-bottom: 1.5rem;
}
.specialty {
  width: 100%;
  min-width: 590px;
  border-collapse: collapse;
  color: #fff;
  font-size: 13px;
  text-align: center;
  th, td {
    border: 1px solid rgba(255, 255, 255, 0.35);
    padding: 0.75rem 0.45rem;
    vertical-align: middle;
  }
  th {
    background: #0e0d34;
    font-weight: 700;
  }
  td, .specialty-image {
    background: #55577f;
  }
  .specialty-image {
    width: 100px;
    padding: 4px;
  }
  img {
    display: block;
    width: 90px;
    height: 90px;
    object-fit: contain;
    margin: auto;
  }
}
.article-section {
  scroll-margin-top: calc(var(--header-height) + 25px);
  margin-bottom: 2.25rem;
}
.section-heading {
  border-bottom: 1px solid #ddd;
  margin-bottom: 1rem;
  padding-bottom: 0.4375rem;
}
.article-copy {
  font-size: 13px;
  line-height: 1.5;
  color: #666;
}
.requirements {
  padding-left: 19px;
  > li {
    padding-left: 9px;
    margin-bottom: 9px;
  }
  ol {
    padding-left: 27px;
    margin-top: 4px;
    li {
      padding-left: 2px;
    }
  }
}
.study-links {
  padding-left: 20px;
  line-height: 1.95;
}
.paper-note {
  border-left: 3px solid #a9bca9;
  padding: 9px 12px;
  background: #f6f8f4;
  font-size: 0.625rem;
  color: #657264;
  margin: 23px 0;
}
.paper-footer {
  border-top: 1px solid var(--wiki-line);
  padding-top: 13px;
  margin-top: 30px;
  font-size: 0.625rem;
  color: #69766d;
}
.avatar {
  width: 33px;
  height: 33px;
  flex: none;
  font-size: 0.625rem;
}
.avatar-purple {
  background: #836594;
}
.caption {
  font-size: 0.75rem;
  font-weight: 600;
  margin: 0 0 4px;
}
.muted {
  color: #66766b;
  font-size: 0.6875rem;
  line-height: 1.7;
}
.comment {
  .avatar {
    width: 28px;
    height: 28px;
    font-size: 0.5625rem;
  }
  strong {
    font-size: 0.625rem;
    display: block;
    margin-bottom: 4px;
  }
  p {
    font-size: 0.625rem;
    line-height: 1.65;
    color: #55635a;
    margin: 0;
  }
}
.info-list {
  display: grid;
  gap: 17px;
  margin: 0;
  > div {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 0.6875rem;
  }
  i {
    font-size: 1.0625rem;
  }
  dt {
    font-weight: 400;
    color: #66766b;
  }
  dd {
    margin: 0 0 0 auto;
    text-align: right;
    font-size: 0.625rem;
  }
}
@media (max-width: 1399.98px) {
  .workspace {
    grid-template-columns: 210px minmax(0, 1fr) 235px;
    gap: 22px;
    padding-left: 18px;
    padding-right: 18px;
  }
  .paper {
    padding-left: 32px;
    padding-right: 32px;
  }
  .article-tabs-container > .nav {
    margin-left: -15px;
    margin-right: -15px;
  }
  .toolbar-edit {
    margin-right: -8px;
  }
}
@media (max-width: 1199.98px) {
  .workspace {
    grid-template-columns: minmax(0, 1fr) 240px;
    max-width: 1160px;
  }
}
@media (max-width: 991.98px) {
  .workspace {
    display: block;
    max-width: 860px;
    padding: 20px;
  }
  .right-sidebar {
    position: static !important;
    display: grid !important;
    grid-template-columns: 1fr 1fr;
    margin: 22px auto 0;
    max-width: 800px;
    > .side-panel:last-child {
      grid-column: 1 / -1;
    }
  }
}
@media (max-width: 575.98px) {
  .workspace {
    padding: 14px 10px 25px;
  }
  .paper {
    padding: 0 21px 20px;
    min-height: auto;
    h1 {
      font-size: 1.875rem;
    }
    h2 {
      font-size: 1.5625rem;
    }
  }
  .article-tabs-container > .nav {
    margin: 0 -8px 25px;
    gap: 13px;
    padding-right: 50px;
  }
  .article-tabs-container .nav-link {
    font-size: 0.625rem;
    padding: 14px 0;
  }
  .toolbar-edit {
    margin-right: -6px;
  }
  .right-sidebar {
    grid-template-columns: 1fr;
    > .side-panel:last-child {
      grid-column: auto;
    }
  }
}
@media (prefers-reduced-motion: no-preference) {
  html {
    scroll-behavior: smooth;
  }
}
@media print {
  .left-sidebar,
  .right-sidebar,
  .mobile-tools,
  .toolbar-edit,
  .article-tabs-container > .nav,
  .edit-btn,
  .paper-note,
  .modal,
  .toast-container {
    display: none !important;
  }
  body,
  .workspace {
    background: white;
    padding: 0;
    display: block;
  }
  .paper {
    border: 0 !important;
    box-shadow: none !important;
    max-width: none;
    min-height: 0;
    padding: 0;
  }
  .article-section {
    break-inside: avoid;
  }
  .article-copy {
    font-size: 11pt;
  }
  .paper h1 {
    font-size: 26pt;
  }
  .specialty {
    print-color-adjust: exact;
  }
}
</style>

