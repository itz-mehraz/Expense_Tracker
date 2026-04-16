(function () {
    'use strict';

    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-password-toggle');
            var input = document.getElementById(targetId);
            if (!input) return;

            var showLabel = btn.getAttribute('data-label-show') || 'Show';
            var hideLabel = btn.getAttribute('data-label-hide') || 'Hide';

            if (input.type === 'password') {
                input.type = 'text';
                btn.setAttribute('aria-pressed', 'true');
                btn.textContent = hideLabel;
            } else {
                input.type = 'password';
                btn.setAttribute('aria-pressed', 'false');
                btn.textContent = showLabel;
            }
        });
    });
})();
