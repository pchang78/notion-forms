jQuery(document).ready(function ($) {

    var serializedOrder = $("#form-fields").children('li').map(function() {
        return $(this).data('id');
    }).get();
    var serializedString = serializedOrder.join(',');
    $("#notion_forms_field_order").val(serializedString);

    // Make the form fields sortable
    $('#form-fields').sortable({
        stop: function (event, ui) {
            var serializedOrder = $(this).children('li').map(function() {
                return $(this).data('id');
            }).get();
            var serializedString = serializedOrder.join(',');
            $("#notion_forms_field_order").val(serializedString);
        }
    });

    // Drag-and-drop between Available Fields and Form Fields
    $('.notion-forms-list').sortable({
        connectWith: '.notion-forms-list',
        items: '.notion-field-item',
        update: function (event, ui) {
            const sourceId = ui.sender ? ui.sender.attr('id') : $(this).attr('id');
            const targetId = $(this).attr('id');
            const fieldId = ui.item.data('id');
            // Determine if the field was moved between lists
            if (sourceId !== targetId) {
                const isActive = targetId === 'form-fields' ? 1 : 0;
                if(isActive) {
                    $(this).find('.attributes').removeClass('hidden');
                }
                else {
                    $(this).find('.attributes').addClass('hidden');
                }
                $("#is_active" + fieldId).val(isActive);
                var serializedOrder = $("#form-fields").children('li').map(function() {
                    return $(this).data('id');
                }).get();
                var serializedString = serializedOrder.join(',');
                $("#notion_forms_field_order").val(serializedString);
            }
        },
    });

    // Add "draggable" cursor styling
    $('.notion-field-item').css('cursor', 'move');
});