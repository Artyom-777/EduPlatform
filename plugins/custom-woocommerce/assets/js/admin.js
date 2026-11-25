(function () {
    'use strict';
})();
document.addEventListener('DOMContentLoaded', function () {
    const productTypeSelect = document.getElementById('cwc_product_type');
    const simpleProductFields = document.getElementById('cwc-simple-product-fields');

    if (!productTypeSelect || !simpleProductFields) {
        return;
    }

    function toggleProductFields() {
        const selectedType = productTypeSelect.value;

        if (selectedType === 'simple') {
            simpleProductFields.style.display = '';
        } else {
            simpleProductFields.style.display = 'none';
        }
    }

    productTypeSelect.addEventListener('change', toggleProductFields);

    toggleProductFields();
});



document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('cwc-product-attributes');
    const addButton = document.getElementById('cwc-add-attribute');

    if (!container || !addButton) {
        return;
    }

    const attributesData = JSON.parse(
        container.dataset.attributes || '{}'
    );

    /**
     * Get the next available row index.
     */
    let nextIndex = 0;

    container.querySelectorAll('.cwc-attribute-row').forEach(row => {
        const input = row.querySelector('[name*="attribute_id"]');

        if (!input) return;

        const match = input.name.match(/cwc_attributes\[(\d+)\]/);

        if (match) {
            nextIndex = Math.max(nextIndex, Number(match[1]) + 1);
        }
    });

    /**
     * Render selected options as tags.
     */
    function renderTags(multiselect) {
        const selectedContainer = multiselect.querySelector(
            '.cwc-selected-options'
        );

        const checkboxes = multiselect.querySelectorAll(
            '.cwc-option-item input[type="checkbox"]'
        );

        selectedContainer.innerHTML = '';

        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) return;

            const label = checkbox.closest('.cwc-option-item');
            const name = label.querySelector('span').textContent;

            const tag = document.createElement('span');
            tag.className = 'cwc-selected-tag';

            const text = document.createElement('span');
            text.textContent = name;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.textContent = '×';
            removeButton.setAttribute('aria-label', 'Remove ' + name);

            removeButton.addEventListener('click', function () {
                checkbox.checked = false;
                renderTags(multiselect);
            });

            tag.append(text, removeButton);
            selectedContainer.appendChild(tag);
        });
    }

    /**
     * Filter options by search text.
     */
    function filterOptions(multiselect) {
        const search = multiselect.querySelector('.cwc-options-search');
        const query = search.value.toLowerCase();

        multiselect.querySelectorAll('.cwc-option-item').forEach(item => {
            const name = item.querySelector('span').textContent.toLowerCase();

            item.hidden = !name.includes(query);
        });
    }

    /**
     * Build option checkboxes.
     */
    function populateOptions(multiselect, attributeId, selectedIds = []) {
        const dropdown = multiselect.querySelector('.cwc-options-dropdown');
        const search = multiselect.querySelector('.cwc-options-search');

        dropdown.innerHTML = '';
        search.value = '';

        const attribute = attributesData[attributeId];

        if (!attribute) {
            renderTags(multiselect);
            return;
        }

        attribute.terms.forEach(term => {
            const label = document.createElement('label');
            label.className = 'cwc-option-item';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = multiselect.dataset.inputName;
            checkbox.value = term.term_id;
            checkbox.checked = selectedIds.includes(Number(term.term_id));

            const text = document.createElement('span');
            text.textContent = term.name;

            checkbox.addEventListener('change', function () {
                renderTags(multiselect);
            });

            label.append(checkbox, text);
            dropdown.appendChild(label);
        });

        renderTags(multiselect);
        filterOptions(multiselect);
    }

    /**
     * Initialize one searchable multi-select.
     */
    function initializeMultiselect(multiselect) {
        const search = multiselect.querySelector('.cwc-options-search');
        const dropdown = multiselect.querySelector('.cwc-options-dropdown');

        if (!search || !dropdown) return;

        search.addEventListener('focus', function () {
            dropdown.classList.add('is-open');
        });

        search.addEventListener('input', function () {
            dropdown.classList.add('is-open');
            filterOptions(multiselect);
        });

        multiselect.querySelectorAll(
            '.cwc-option-item input[type="checkbox"]'
        ).forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                renderTags(multiselect);
            });
        });

        renderTags(multiselect);
    }

    /**
     * Initialize existing attribute rows.
     */
    container.querySelectorAll('.cwc-attribute-row').forEach(row => {
        const multiselect = row.querySelector('.cwc-multiselect');
        const attributeSelect = row.querySelector('.cwc-attribute-select');

        if (multiselect) {
            const indexMatch = multiselect.querySelector(
                '.cwc-option-item input'
            )?.name.match(/cwc_attributes\[(\d+)\]/);

            const index = indexMatch ? indexMatch[1] : '0';

            multiselect.dataset.inputName =
                `cwc_attributes[${index}][options][]`;

            initializeMultiselect(multiselect);
        }

        if (attributeSelect && multiselect) {
            attributeSelect.addEventListener('change', function () {
                populateOptions(multiselect, this.value);
            });
        }
    });

    /**
     * Create a new attribute row.
     */
    function createAttributeRow(index) {
        const row = document.createElement('div');
        row.className = 'cwc-attribute-row';

        const attributeLabel = document.createElement('label');
        attributeLabel.innerHTML = '<strong>Attribute</strong>';

        const attributeSelect = document.createElement('select');
        attributeSelect.name =
            `cwc_attributes[${index}][attribute_id]`;
        attributeSelect.className = 'widefat cwc-attribute-select';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select attribute';
        attributeSelect.appendChild(defaultOption);

        Object.entries(attributesData).forEach(([id, attribute]) => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = attribute.label;
            attributeSelect.appendChild(option);
        });

        const optionsLabel = document.createElement('label');
        optionsLabel.innerHTML = '<strong>Options</strong>';

        const multiselect = document.createElement('div');
        multiselect.className = 'cwc-multiselect';
        multiselect.dataset.inputName =
            `cwc_attributes[${index}][options][]`;

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'widefat cwc-options-search';
        search.placeholder = 'Search options...';
        search.autocomplete = 'off';

        const selected = document.createElement('div');
        selected.className = 'cwc-selected-options';

        const dropdown = document.createElement('div');
        dropdown.className = 'cwc-options-dropdown';

        multiselect.append(search, selected, dropdown);

        const visibleLabel = document.createElement('label');
        const visibleCheckbox = document.createElement('input');
        visibleCheckbox.type = 'checkbox';
        visibleCheckbox.name = `cwc_attributes[${index}][visible]`;
        visibleCheckbox.value = '1';

        visibleLabel.append(
            visibleCheckbox,
            document.createTextNode(' Visible on product page')
        );

        const variationLabel = document.createElement('label');
        const variationCheckbox = document.createElement('input');
        variationCheckbox.type = 'checkbox';
        variationCheckbox.name = `cwc_attributes[${index}][variation]`;
        variationCheckbox.value = '1';

        variationLabel.append(
            variationCheckbox,
            document.createTextNode(' Used for variations')
        );

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'button cwc-remove-attribute';
        removeButton.textContent = 'Remove';

        row.append(
            attributeLabel,
            attributeSelect,
            document.createElement('br'),
            optionsLabel,
            multiselect,
            document.createElement('br'),
            visibleLabel,
            document.createElement('br'),
            variationLabel,
            document.createElement('br'),
            removeButton,
            document.createElement('hr')
        );

        initializeMultiselect(multiselect);

        attributeSelect.addEventListener('change', function () {
            populateOptions(multiselect, this.value);
        });

        return row;
    }

    /**
     * Add attribute row.
     */
    addButton.addEventListener('click', function () {
        container.appendChild(createAttributeRow(nextIndex));
        nextIndex++;
    });

    /**
     * Remove attribute row.
     */
    container.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.cwc-remove-attribute');

        if (removeButton) {
            removeButton.closest('.cwc-attribute-row').remove();
        }
    });

    /**
     * Close dropdowns when clicking outside.
     */
    document.addEventListener('click', function (event) {
        container.querySelectorAll('.cwc-multiselect').forEach(multiselect => {
            if (!multiselect.contains(event.target)) {
                multiselect.querySelector(
                    '.cwc-options-dropdown'
                )?.classList.remove('is-open');
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('cwc-variation-create-form');
    const createButton = document.getElementById('cwc-create-variation');

    if (!wrapper || !createButton) {
        return;
    }

    const variationFields = wrapper.querySelectorAll(
        'input[name], select[name], textarea[name]'
    );
    
    // Disable variation fields only when the main product form is submitted.
    const productForm = wrapper.closest('form');
    
    if (productForm) {
        productForm.addEventListener('submit', function () {
            variationFields.forEach(function (field) {
                field.disabled = true;
            });
        });
    }

    createButton.addEventListener('click', function () {
        const actionUrl = wrapper.dataset.actionUrl;

        if (!actionUrl) {
            return;
        }

        const form = document.createElement('form');

        form.method = 'POST';
        form.action = actionUrl;
        form.style.display = 'none';

        const fields = wrapper.querySelectorAll(
            'input[name], select[name], textarea[name]'
        );

        fields.forEach(function (field) {
            if (
                (field.type === 'checkbox' || field.type === 'radio') &&
                !field.checked
            ) {
                return;
            }

            if (field.tagName === 'SELECT' && field.multiple) {
                Array.from(field.selectedOptions).forEach(function (option) {
                    appendHiddenField(form, field.name, option.value);
                });

                return;
            }

            appendHiddenField(form, field.name, field.value);
        });

        document.body.appendChild(form);

        form.submit();
    });

    function appendHiddenField(form, name, value) {
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value;

        form.appendChild(input);
    }
});

