(function () {
    'use strict';

    var forms = document.querySelectorAll('.course-unenrollment-form');

    forms.forEach(function (form) {
        var dialogId = form.getAttribute('data-confirm-dialog');
        var dialog = dialogId ? document.getElementById(dialogId) : null;
        var confirmed = false;

        form.addEventListener('submit', function (event) {
            if (confirmed) {
                return;
            }

            event.preventDefault();

            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm('Are you sure you want to unenroll this user from this course?')) {
                    confirmed = true;
                    form.requestSubmit();
                }
                return;
            }

            dialog.showModal();
        });

        if (!dialog) {
            return;
        }

        var yesButton = dialog.querySelector('[data-unenroll-confirm]');
        var noButton = dialog.querySelector('[data-unenroll-cancel]');

        if (yesButton) {
            yesButton.addEventListener('click', function () {
                confirmed = true;
                dialog.close();
                form.requestSubmit();
            });
        }

        if (noButton) {
            noButton.addEventListener('click', function () {
                dialog.close();
            });
        }
    });
})();
