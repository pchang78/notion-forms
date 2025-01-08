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
            $("#form_sync_for_notion_field_order").val(serializedString);
        }
    });

    // Drag-and-drop between Available Fields and Form Fields
    $('.form-sync-for-notion-list').sortable({
        connectWith: '.form-sync-for-notion-list',
        items: '.form-sync-for-notion-field-item',
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

    var dragSrcEl = null;
    var touchTimeout;
    var touchStartY;
    var touchStartX;

    function handleDragStart(e) {
        this.style.opacity = '0.4';
        dragSrcEl = this;

        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragEnd(e) {
        this.style.opacity = '1';
    }

    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }

        if (dragSrcEl != this) {
            // Swap elements
            var sourceParent = dragSrcEl.parentNode;
            var targetParent = this.parentNode;

            if (sourceParent === targetParent) {
                // Same list - swap positions
                var children = [...targetParent.children];
                const sourceIndex = children.indexOf(dragSrcEl);
                const targetIndex = children.indexOf(this);
                
                if (sourceIndex < targetIndex) {
                    this.parentNode.insertBefore(dragSrcEl, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(dragSrcEl, this);
                }
            } else {
                // Different lists - move element
                sourceParent.removeChild(dragSrcEl);
                targetParent.appendChild(dragSrcEl);
            }

            // Update active status
            var isActive = targetParent.id === 'form-fields' ? 1 : 0;
            $('#is_active' + $(dragSrcEl).data('id')).val(isActive);

            // Show/hide attributes
            $(dragSrcEl).find('.attributes').toggle(isActive === 1);
        }

        updateFieldOrder();
        return false;
    }

    // Touch event handlers
    function handleTouchStart(e) {
        const touch = e.touches[0];
        touchStartY = touch.clientY;
        touchStartX = touch.clientX;
        
        touchTimeout = setTimeout(() => {
            this.classList.add('dragging');
            dragSrcEl = this;
        }, 200);
    }

    function handleTouchMove(e) {
        if (!dragSrcEl) return;
        
        e.preventDefault();
        const touch = e.touches[0];
        
        // Get the element under the touch point
        const target = document.elementFromPoint(touch.clientX, touch.clientY);
        if (target && target.classList.contains('notion-field-item')) {
            handleDrop.call(target, e);
        }
    }

    function handleTouchEnd(e) {
        clearTimeout(touchTimeout);
        if (dragSrcEl) {
            dragSrcEl.classList.remove('dragging');
            dragSrcEl = null;
        }
    }

    function updateFieldOrder() {
        var fieldOrder = [];
        $('#form-fields .notion-field-item').each(function() {
            fieldOrder.push($(this).data('id'));
        });
        $('#notion_forms_field_order').val(fieldOrder.join(','));
    }

    // Attach event listeners
    $('.notion-field-item').each(function() {
        this.addEventListener('dragstart', handleDragStart, false);
        this.addEventListener('dragend', handleDragEnd, false);
        this.addEventListener('dragover', handleDragOver, false);
        this.addEventListener('drop', handleDrop, false);
        
        // Touch events
        this.addEventListener('touchstart', handleTouchStart, false);
        this.addEventListener('touchmove', handleTouchMove, false);
        this.addEventListener('touchend', handleTouchEnd, false);
    });
});