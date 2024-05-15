document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('nav.anchors a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const targetName = this.getAttribute('href').substring(1);
            const targetElement = document.querySelector(`[name='${targetName}']`);
            const targetPosition = targetElement.getBoundingClientRect().top - document.querySelector('nav.navbar').clientHeight - 5;

            document.body.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        });
    });
});

function open_url(url) {
    window.location.href = url;
}

window.open_url = open_url;