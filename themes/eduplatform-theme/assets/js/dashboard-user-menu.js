(function () {
    var menu = document.getElementById('dashboard-user-menu');
    if (!menu) {
        return;
    }

    document.addEventListener('click', function (event) {
        if (!menu.open) {
            return;
        }
        var target = event.target;
        if (!(target instanceof Node)) {
            return;
        }
        if (!menu.contains(target)) {
            menu.open = false;
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && menu.open) {
            menu.open = false;
        }
    });
})();
