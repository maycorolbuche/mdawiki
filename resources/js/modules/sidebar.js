// Usage: https://github.com/Grsmto/simplebar
import SimpleBar from "simplebar";

const initialize = () => {
  initializeSimplebar();
  initializeSidebarCollapse();
}

const initializeSimplebar = () => {
  const simplebarElement = document.getElementsByClassName("js-simplebar")[0];

  if (simplebarElement) {
    const simplebarInstance = new SimpleBar(document.getElementsByClassName("js-simplebar")[0]);

    /* Recalculate simplebar on sidebar dropdown toggle */
    const sidebarDropdowns = document.querySelectorAll(".js-sidebar [data-bs-parent]");

    sidebarDropdowns.forEach(link => {
      link.addEventListener("shown.bs.collapse", () => {
        simplebarInstance.recalculate();
      });
      link.addEventListener("hidden.bs.collapse", () => {
        simplebarInstance.recalculate();
      });
    });
  }
}

const initializeSidebarCollapse = () => {
  const bodyElement = document.body;
  const sidebarElement = document.getElementsByClassName("js-sidebar")[0];
  const sidebarToggleElement = document.getElementsByClassName("js-sidebar-toggle")[0];
  const sidebarCompactElement = document.getElementsByClassName("js-sidebar-link-compact")[0];

  if (sidebarElement && sidebarToggleElement) {
    sidebarToggleElement.addEventListener("click", () => {
      sidebarElement.classList.toggle("collapsed");

      sidebarElement.addEventListener("transitionend", () => {
        window.dispatchEvent(new Event("resize"));
      });
    });
  }

  if (sidebarElement && sidebarCompactElement) {
    sidebarCompactElement.addEventListener("click", () => {
      if (bodyElement.hasAttribute('data-sidebar-layout')) {
        bodyElement.removeAttribute('data-sidebar-layout');
        deleteCookie('data-sidebar-layout');
      } else {
        bodyElement.setAttribute('data-sidebar-layout', 'compact');
        setCookie('data-sidebar-layout', 'compact');
      }

      sidebarElement.addEventListener("transitionend", () => {
        window.dispatchEvent(new Event("resize"));
      });
    });
  }
}

function setCookie(name, value) {
  let expires = "";
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function deleteCookie(name) {
  document.cookie = name + '=; Max-Age=-99999999; path=/';
}

function getCookie(name) {
  let nameEQ = name + "=";
  let ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

const bodyElement = document.body;
const dataSidebarLayout = getCookie("data-sidebar-layout");
if (dataSidebarLayout) {
  bodyElement.setAttribute('data-sidebar-layout', dataSidebarLayout);
}


document.addEventListener("DOMContentLoaded", () => initialize());