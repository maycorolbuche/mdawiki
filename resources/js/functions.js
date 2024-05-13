function nav_page(name, pos) {
    let el = document.querySelector(`[name='${name}']`);
    let el_scroll = document.body;
    if (pos === undefined) {
        pos = el.offsetTop + 80;
    }

    /***************************************************************/
    var targetPosition = pos;
    var duration = 200;
    var startPosition = el_scroll.scrollTop || 0;
    var distance = targetPosition - startPosition;
    var interval = 10;
    var increment = distance / (duration / interval);

    function animate() {
        var newPosition = startPosition + increment;
        startPosition = newPosition;

        el_scroll.scrollTo(0, newPosition);

        if ((increment > 0 && newPosition < targetPosition) || (increment < 0 && newPosition > targetPosition)) {
            setTimeout(animate, interval);
        } else {
            el_scroll.scrollTo(0, targetPosition);
        }
    }

    animate();
}


window.nav_page = nav_page;