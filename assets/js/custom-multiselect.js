/**
 * Custom Multi-Select Initialization with "Select All" and Checkboxes
 * Designed to work with Select2
 */
$(document).ready(function () {
    // Inject global CSS for right-aligned checkboxes and 'Select All' styling
    if ($('#custom-multiselect-css').length === 0) {
        var style = `
        <style id="custom-multiselect-css">
        /* Custom styled right-aligned checkboxes for Select2 */
        .select2-results__option {
            padding-right: 35px !important; 
            padding-left: 15px !important;
            position: relative;
            color: #3f4254;
            background-image: none !important;
        }
        .select2-results__option:after,
        .select2-container--bootstrap5 .select2-results__option:after,
        .select2-container--default .select2-results__option:after {
            display: none !important;
            content: none !important;
            background-image: none !important;
        }
        .select2-results__option:before {
            content: "";
            display: inline-block;
            position: absolute;
            right: 15px !important; 
            left: auto !important;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 1px solid #d8d8d8;
            border-radius: 4px;
            background-color: #fff;
            transition: all 0.2s ease;
        }
        .select2-results__option[aria-selected="true"]:before,
        .select2-results__option--selected:before {
            background-color: #009ef7 !important; 
            border-color: #009ef7 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e") !important;
            background-size: 14px 14px !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        .select2-results__option[aria-selected="true"],
        .select2-results__option--selected {
            background-color: transparent !important;
            background-image: none !important; 
            color: #3f4254 !important;
        }
        .select2-results__option--highlighted[aria-selected="true"],
        .select2-results__option--highlighted.select2-results__option--selected {
            background-color: #f3f6f9 !important;
            color: #3f4254 !important; 
        }
        .select2-results__option--highlighted[aria-selected="false"]:before {
            background-color: #fff !important;
            border-color: #d8d8d8 !important;
            background-image: none !important;
        }
        .select2-results__option.select2-results__message:before {
            display: none !important;
        }
        .select2-results__option.partial-selected:before {
            background-color: #009ef7 !important; 
            border-color: #009ef7 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cline x1='4' y1='10' x2='16' y2='10' stroke='%23fff' stroke-width='3' stroke-linecap='round'/%3e%3c/svg%3e") !important;
            background-size: 14px 14px !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #e4e6ef;
            background-color: #fff;
            width: 100% !important; 
        }
        .select2-container .select2-selection--multiple .select2-selection__choice {
            background-color: #f3f6f9 !important;
            border: 1px solid #e4e6ef !important;
            color: #3f4254 !important;
            border-radius: 4px !important;
            padding: 2px 8px !important;
            margin: 3px 4px 3px 0 !important;
            font-size: 11px !important;
            line-height: 1.5 !important;
            display: block !important;
            width: calc(100% - 4px) !important;
            float: none !important;
            clear: both;
        }
        .select2-container .select2-selection--multiple .select2-selection__choice__remove {
            color: #000000   !important;
            margin-right: 6px !important;
            font-weight: bold !important;
            border: none !important;
            
            font-size: 14px !important;
        }
        .select2-container {
            width: 100% !important; 
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #b5b5c3;
        }
        .select2-container .select2-selection--multiple .select2-selection__choice.hidden-choice {
            display: none !important;
        }
        .select2-container .select2-selection--multiple .select2-selection__choice.custom-all-tag {
            background-color: #009ef7 !important;
            border: 1px solid #009ef7 !important;
            color: #fff !important;
        }
        .select2-container .select2-selection--multiple .select2-selection__choice.custom-all-tag .select2-selection__choice__remove {
            color: #fff !important;
        }
        </style>
        `;
        $('head').append(style);
    }
    function updateSelectAllIndeterminateState($selectEl) {
        var selectedOptions = $selectEl.val() || [];
        var normalSelected = selectedOptions.filter(v => v !== 'selectAll' && v !== '');
        var allOptionsCount = $selectEl.find('option').filter(function() {
            return this.value !== 'selectAll' && this.value !== '';
        }).length;

        var data = $selectEl.data('select2');
        if (data && data.$dropdown) {
            var $selectAllLi = data.$dropdown.find('.select2-results__option').filter(function () {
                return $(this).text().trim() === 'Select All';
            });

            if ($selectAllLi.length) {
                if (normalSelected.length > 0 && normalSelected.length < allOptionsCount) {
                    $selectAllLi.addClass('partial-selected');
                } else {
                    $selectAllLi.removeClass('partial-selected');
                }
            }
        }
    }

    // Target all select elements that have 'multiple' attribute across the specified dashboards
    function initCustomMultiSelect() {
        var selectors = [
            'select[multiple]',
            '.custom-multi-select',
            '.hse-select2[multiple]',
            '.oe-select2[multiple]',
            '.select2[multiple]',
            '.select2-filter[multiple]',
            '.js-example-basic-single[multiple]'
        ].join(', ');

        $(selectors).each(function () {
            var $select = $(this);

            if ($select.data('custom-multiselect-initialized')) {
                // Ensure Select All exists even if HTML was replaced by AJAX
                var exists = false;
                $select.find('option').each(function () {
                    if ($(this).val() === 'selectAll') exists = true;
                });
                if (!exists) {
                    $select.prepend(new Option("Select All", "selectAll", false, false));
                }
                return;
            }
            $select.data('custom-multiselect-initialized', true);

            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2('destroy');
            }

            var hasSelectAll = false;
            var totalCount = 0;
            var selectedCount = 0;

            $select.find('option').each(function () {
                var text = $(this).text().trim().toLowerCase();
                var val = $(this).val();
                
                if (val === 'selectAll' || val === 'ALL' || text === 'select all' || text === 'all') {
                    // Prevent duplicate Select All options if they both exist
                    if (hasSelectAll) {
                        $(this).remove();
                    } else {
                        hasSelectAll = true;
                        $(this).val('selectAll');
                        $(this).text('Select All');
                    }
                } else if (val !== '') {
                    totalCount++;
                    if ($(this).prop('selected')) {
                        selectedCount++;
                    }
                }
            });

            var shouldBeSelected = (totalCount > 0 && selectedCount === totalCount);

            if (!hasSelectAll) {
                $select.prepend(new Option("Select All", "selectAll", shouldBeSelected, shouldBeSelected));
            } else {
                if (shouldBeSelected) {
                    $select.find('option[value="selectAll"]').prop('selected', true);
                }
            }

            // Prevent "selectAll" from polluting backend requests on form submit
            var $form = $select.closest('form');
            if ($form.length) {
                $form.on('submit', function() {
                    $select.find('option[value="selectAll"]').prop('disabled', true);
                    // Re-enable after a tiny delay so the UI doesn't break if submit is prevented/AJAX
                    setTimeout(function() {
                        $select.find('option[value="selectAll"]').prop('disabled', false);
                    }, 50);
                });
            }

            $select.select2({
                placeholder: $select.data('placeholder') || "Select options",
                closeOnSelect: false,
                width: '100%',
                templateSelection: function(data, container) {
                    if (!data.id) { return data.text; } // placeholder
                    
                    var $selectEl = $(data.element).closest('select');
                    var selected = $selectEl.val() || [];
                    var allOptionsCount = $selectEl.find('option').filter(function() {
                        return this.value !== 'selectAll' && this.value !== '';
                    }).length;
                    var normalSelectedCount = selected.filter(v => v !== 'selectAll' && v !== '').length;
                    
                    var isAllSelected = (normalSelectedCount >= allOptionsCount && allOptionsCount > 0);
                    
                    if (isAllSelected) {
                        if (data.id === 'selectAll') {
                            $(container).addClass('custom-all-tag');
                            $(container).removeClass('hidden-choice');
                            return 'All Selected'; 
                        } else {
                            $(container).addClass('hidden-choice');
                            return data.text;
                        }
                    } else {
                        if (data.id === 'selectAll') {
                            $(container).addClass('hidden-choice');
                            return data.text;
                        }
                        $(container).removeClass('hidden-choice');
                        $(container).removeClass('custom-all-tag');
                        return data.text;
                    }
                }
            }).on('select2:select select2:unselect', function (e) {
                var selectedOptions = $select.val() || [];
                var allOptions = $select.find('option').filter(function() {
                    return this.value !== 'selectAll' && this.value !== '';
                }).length;

                if (e.params && e.params.data && e.params.data.id === 'selectAll') {
                    if (e.type === 'select2:select') {
                        $select.find('option').prop('selected', true);
                        $select.trigger('change');
                        var select2Inst = $select.data('select2');
                        if (select2Inst && select2Inst.isOpen()) {
                            select2Inst.$dropdown.find('.select2-results__option').attr('aria-selected', 'true');
                        }
                    } else {
                        $select.find('option').prop('selected', false);
                        $select.trigger('change');
                        var select2Inst = $select.data('select2');
                        if (select2Inst && select2Inst.isOpen()) {
                            select2Inst.$dropdown.find('.select2-results__option').attr('aria-selected', 'false');
                        }
                    }
                } else {
                    var selectAllOption = $select.find('option[value="selectAll"]');
                    var isAll = (selectedOptions.filter(v => v !== 'selectAll' && v !== '').length >= allOptions && allOptions > 0);
                    var wasAll = selectAllOption.prop('selected');
                    
                    if (isAll !== wasAll) {
                        selectAllOption.prop('selected', isAll);
                        $select.trigger('change');
                    }
                    updateSelectAllIndeterminateState($select);
                }

                // Clear the search input so the user can easily search for the next item
                var select2Inst = $select.data('select2');
                if (select2Inst && select2Inst.$dropdown) {
                    var $search = select2Inst.$dropdown.find('.select2-search__field');
                    if ($search.length) {
                        $search.val('');
                        $search.trigger('keyup'); // Trigger keyup to reset the dropdown list
                    }
                }
                
                // For inline search field (if any)
                if (select2Inst && select2Inst.$selection) {
                    var $inlineSearch = select2Inst.$selection.find('.select2-search__field');
                    if ($inlineSearch.length) {
                        $inlineSearch.val('');
                        $inlineSearch.trigger('keyup');
                    }
                }
            }).on('select2:open', function () {
                setTimeout(function () {
                    updateSelectAllIndeterminateState($select);
                }, 10);
            });
        });
    }

    initCustomMultiSelect();
    window.reinitMultiSelect = initCustomMultiSelect;
    $(document).ajaxComplete(function () {
        setTimeout(initCustomMultiSelect, 200);
    });

    // Restrict selection to the checkbox area only
    function preventSelectIfOutsideCheckbox(e) {
        var option = e.target.closest('.select2-results__option');
        if (option) {
            var ul = option.closest('.select2-results__options');
            if (ul && ul.getAttribute('aria-multiselectable') === 'true') {
                var rect = option.getBoundingClientRect();
                var clickX = e.clientX;
                // The checkbox is on the right side (right: 15px, width: 18px).
                // If the click is more than 50px from the right edge, it's outside the checkbox.
                if (rect.right - clickX > 50) {
                    e.stopPropagation();
                }
            }
        }
    }
    document.addEventListener('mousedown', preventSelectIfOutsideCheckbox, true);
    document.addEventListener('mouseup', preventSelectIfOutsideCheckbox, true);
});
