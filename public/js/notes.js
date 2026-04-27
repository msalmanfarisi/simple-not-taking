(function () {
    'use strict';
    var addBtn = document.querySelector('[data-attachments-add]');
    var list = document.getElementById('attachmentsList');
    var template = document.getElementById('attachmentTemplate');
    if (!addBtn || !list || !template) {
        return;
    }

    addBtn.addEventListener('click', function () {
        var clone = template.content.cloneNode(true);
        list.appendChild(clone);
    });

    list.addEventListener('click', function (event) {
        var target = event.target;
        if (target && target.matches && target.matches('[data-attachments-remove]')) {
            event.preventDefault();
            var row = target.closest('.attachment-row');
            if (row) {
                row.parentNode.removeChild(row);
            }
        }
    });
})();
