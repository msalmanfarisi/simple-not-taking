(function () {
    'use strict';
    var btn = document.getElementById('captchaReload');
    var img = document.getElementById('captchaImage');
    var input = document.getElementById('captcha');
    if (!btn || !img || !input) {
        return;
    }
    btn.addEventListener('click', function () {
        var url = img.getAttribute('data-src') || img.src.split('?')[0];
        img.src = url + '?_=' + Date.now();
        input.value = '';
        input.focus();
    });
})();
