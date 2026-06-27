
const endpointTabsNav = document.getElementById('endpointTabs');
const endpointTabContent = document.getElementById('endpointTabContent');

Promise.all([
    fetch('../endpoint_params.json').then(response => response.json()),
    fetch('../test/endpointGroups.json').then(response => response.json())
])
    .then(([endpoints, endpointGroups]) => {
        let firstGroup = true;
        for (const groupName in endpointGroups) {
            const groupEndpoints = endpointGroups[groupName];
            const groupId = groupName.replace(/[^a-zA-Z0-9]/g, '_'); // Sanitize group name for ID

            // Create tab navigation item
            const navItem = document.createElement('li');
            navItem.classList.add('nav-item');
            navItem.setAttribute('role', 'presentation');

            const navLink = document.createElement('button');
            navLink.classList.add('nav-link');
            if (firstGroup) {
                navLink.classList.add('active');
            }
            navLink.setAttribute('id', `${groupId}-tab`);
            navLink.setAttribute('data-bs-toggle', 'tab');
            navLink.setAttribute('data-bs-target', `#${groupId}`);
            navLink.setAttribute('type', 'button');
            navLink.setAttribute('role', 'tab');
            navLink.setAttribute('aria-controls', groupId);
            navLink.setAttribute('aria-selected', firstGroup ? 'true' : 'false');
            navLink.textContent = groupName.replace(/_/g, ' ').toUpperCase();

            navItem.appendChild(navLink);
            endpointTabsNav.appendChild(navItem);

            // Create tab content pane
            const tabPane = document.createElement('div');
            tabPane.classList.add('tab-pane', 'fade');
            if (firstGroup) {
                tabPane.classList.add('show', 'active');
            }
            tabPane.setAttribute('id', groupId);
            tabPane.setAttribute('role', 'tabpanel');
            tabPane.setAttribute('aria-labelledby', `${groupId}-tab`);

            groupEndpoints.forEach(endpointName => {
                const endpointData = endpoints[endpointName];
                if (endpointData) {
                    const endpointId = `${groupId}-${endpointName.replace(/[^a-zA-Z0-9]/g, '_')}`;

                    const endpointCard = document.createElement('div');
                    endpointCard.classList.add('card', 'mb-3');

                    const endpointCardHeader = document.createElement('div');
                    endpointCardHeader.classList.add('card-header');
                    endpointCardHeader.setAttribute('id', `heading-${endpointId}`);

                    const headerRow = document.createElement('div');
                    headerRow.classList.add('row', 'align-items-center');

                    const titleCol = document.createElement('div');
                    titleCol.classList.add('col');

                    const endpointTitleButton = document.createElement('button');
                    endpointTitleButton.classList.add('btn', 'btn-link', 'text-decoration-none');
                    endpointTitleButton.setAttribute('data-bs-toggle', 'collapse');
                    endpointTitleButton.setAttribute('data-bs-target', `#collapse-${endpointId}`);
                    endpointTitleButton.setAttribute('aria-expanded', 'false');
                    endpointTitleButton.setAttribute('aria-controls', `collapse-${endpointId}`);
                    endpointTitleButton.textContent = endpointName;
                    endpointTitleButton.style.fontWeight = 'bold'; // Make title bold

                    titleCol.appendChild(endpointTitleButton);
                    headerRow.appendChild(titleCol);

                    const methodCol = document.createElement('div');
                    methodCol.classList.add('col-auto');

                    const getButton = document.createElement('span');
                    getButton.classList.add('badge', 'bg-primary', 'me-2');
                    getButton.textContent = 'GET';
                    methodCol.appendChild(getButton);

                    const queryParamsSpan = document.createElement('span');
                    queryParamsSpan.textContent = `?get=${endpointName}`;
                    methodCol.appendChild(queryParamsSpan);

                    headerRow.appendChild(methodCol);
                    endpointCardHeader.appendChild(headerRow);
                    endpointCard.appendChild(endpointCardHeader);

                    const endpointCollapse = document.createElement('div');
                    endpointCollapse.classList.add('collapse');
                    endpointCollapse.setAttribute('id', `collapse-${endpointId}`);
                    endpointCollapse.setAttribute('aria-labelledby', `heading-${endpointId}`);
                    endpointCollapse.setAttribute('data-bs-parent', `#${groupId}`); // Ensure only one collapse is open at a time within the tab

                    const endpointCardBody = document.createElement('div');
                    endpointCardBody.classList.add('card-body');

                    const form = document.createElement('form');
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();
                        const formData = new FormData(form);
                        const params = new URLSearchParams();
                        params.append('get', endpointName);

                        endpointData.params.forEach(param => {
                            const paramName = param.name;
                            if (param.type === 'text') {
                                const selectedOption = form.elements[paramName + '_option'].value;
                                if (selectedOption === 'input') {
                                    const textInput = form.elements[paramName];
                                    if (textInput && 'value' in textInput && textInput.value !== '') {
                                        params.append(paramName, String(textInput.value));
                                    }
                                } else if (selectedOption === 'empty') {
                                    params.append(paramName, '');
                                }
                                // If 'not_empty' is selected, do not append the parameter
                            } else if (param.type === 'checkbox') {
                                const checkboxInput = form.elements[paramName];
                                if (checkboxInput && 'checked' in checkboxInput && checkboxInput.checked) {
                                    params.append(paramName, '1');
                                }
                            } else if (param.type === 'select') {
                                const selectElement = form.elements[paramName];
                                if (selectElement && 'value' in selectElement && selectElement.value !== '') {
                                    params.append(paramName, String(selectElement.value));
                                }
                            }
                            else { // Handles number and other input types with value property
                                const inputElement = form.elements[paramName];
                                if (inputElement && 'value' in inputElement && inputElement.value !== '') {
                                    params.append(paramName, String(inputElement.value));
                                }
                            }
                        });

                        const url = `../api.php?${params.toString()}`;
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                responseDiv.innerHTML = highlightJson(data); // Use innerHTML and highlightJson function
                            })
                            .catch(error => {
                                responseDiv.textContent = 'Error: ' + error;
                            });
                    });

                    // Function to highlight JSON
                    function highlightJson(json) {
                        if (typeof json !== 'string') {
                            json = JSON.stringify(json, null, 2);
                        }
                        json = json.replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>');
                        return json.replace(/"(\\u[a-zA-Z0-9]{4}|\\[^u]|[^"\\])*"(\s*:)?/g, function (match) {
                            let cls = 'json-string';
                            if (/^\d+(\.\d+)?([eE][+-]?\d+)?$/.test(match)) {
                                cls = 'json-number';
                            } else if (/^(true|false)$/.test(match)) {
                                cls = 'json-boolean';
                            } else if (/^null$/.test(match)) {
                                cls = 'json-null';
                            } else if (/:$/.test(match)) {
                                cls = 'json-key';
                            }
                            return `<span class="${cls}">${match}</span>`;
                        });
                    }


                    if (endpointData.params) {
                        // Ensure endpointData.params exists and add default limit if not present
                        if (!endpointData.params) {
                            endpointData.params = [];
                        }
                        const hasLimit = endpointData.params.some(param => param.name === 'limit');
                        if (!hasLimit) {
                            endpointData.params.push({ "name": "limit", "column": "limit", "type": "number", "placeholder": "Limit results", "value": "50" });
                        }

                        const paramsContainer = document.createElement('div');
                        form.appendChild(paramsContainer);

                        let paramsRow = document.createElement('div');
                        paramsRow.classList.add('row');
                        paramsContainer.appendChild(paramsRow);

                        endpointData.params.forEach((param, index) => {
                            if (index > 0 && index % 4 === 0) {
                                paramsRow = document.createElement('div');
                                paramsRow.classList.add('row');
                                paramsContainer.appendChild(paramsRow);
                            }

                            const paramCol = document.createElement('div');
                            paramCol.classList.add('col-md-3', 'mb-3'); // Use Bootstrap columns for layout

                            const paramDiv = document.createElement('div');

                            const label = document.createElement('label');
                            label.textContent = param.name + ':';
                            label.classList.add('form-label');
                            paramDiv.appendChild(label);

                            let input;
                            if (param.type === 'select') {
                                input = document.createElement('select');
                                input.classList.add('form-select');
                                param.options.forEach(optionValue => {
                                    const option = document.createElement('option');
                                    option.value = optionValue;
                                    option.textContent = optionValue;
                                    input.appendChild(option);
                                });
                            } else if (param.type === 'switch') {
                                // New structure for switch inputs
                                const inputGroupDiv = document.createElement('div');
                                inputGroupDiv.classList.add('input-group', 'form-control', 'mb-3');

                                const inputGroupPrependDiv = document.createElement('div');
                                inputGroupPrependDiv.classList.add('input-group-prepend');

                                const labelSpan = document.createElement('span');
                                labelSpan.classList.add('me-3');
                                labelSpan.textContent = param.name + ':';
                                inputGroupPrependDiv.appendChild(labelSpan);

                                const formCheckDiv = document.createElement('div');
                                formCheckDiv.classList.add('form-check', 'form-switch', 'form-inline');

                                input = document.createElement('input');
                                input.classList.add('form-check-input');
                                input.type = 'checkbox';
                                input.value = '1';
                                input.name = param.name; // Set name here for switch

                                formCheckDiv.appendChild(input);
                                inputGroupDiv.appendChild(inputGroupPrependDiv);
                                inputGroupDiv.appendChild(formCheckDiv);

                                paramCol.appendChild(inputGroupDiv); // Append the new structure to the column
                                // }
                            } else if (param.type === 'text') {
                                const inputGroupDiv = document.createElement('div'); // Container for text input and radios
                                let placeholderDis = param.placeholder || '';
                                if (param.default) {
                                    placeholderDis += ` (default: ${param.default})`;
                                }

                                inputGroupDiv.innerHTML = `
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="${param.name}_option"
                                            id="${param.name}_option_input"
                                            value="input"
                                            checked
                                        >
                                        <label class="form-check-label" for="${param.name}_option_input">
                                            <input
                                                type="text"
                                                class="form-control"
                                                placeholder="${placeholderDis}"
                                                value="${param.value || ''}"
                                                name="${param.name}"
                                            >
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="${param.name}_option"
                                            id="${param.name}_option_empty"
                                            value="empty"
                                        >
                                        <label class="form-check-label" for="${param.name}_option_empty">
                                            empty
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="${param.name}_option"
                                            id="${param.name}_option_not_empty"
                                            value="not_empty"
                                        >
                                        <label class="form-check-label" for="${param.name}_option_not_empty">
                                            not_empty
                                        </label>
                                    </div>
                                `;

                                paramCol.appendChild(paramDiv); // Append the main paramDiv (which contains the label)
                                paramDiv.appendChild(inputGroupDiv); // Append the inputGroupDiv to paramDiv

                            } else {
                                input = document.createElement('input');
                                input.type = param.type;
                                input.classList.add('form-control');
                                if (param.placeholder) {
                                    input.placeholder = param.placeholder;
                                }
                                if (param.value) {
                                    input.value = param.value;
                                }
                                input.name = param.name; // Set name here for other types
                                paramDiv.appendChild(input);
                                paramCol.appendChild(paramDiv); // Append paramDiv to the column
                            }

                            // input.name = param.name; // Removed from here

                            // if (param.type !== 'switch') { // Removed this condition
                            //     paramDiv.appendChild(input);
                            // }
                            // paramCol.appendChild(paramDiv); // Removed from here
                            paramsRow.appendChild(paramCol); // Append column to the row
                        });
                    }

                    const submitButton = document.createElement('button');
                    submitButton.type = 'submit';
                    submitButton.textContent = 'TRY IT';
                    submitButton.classList.add('btn', 'btn-primary', 'mt-3'); // Add margin top to button
                    form.appendChild(submitButton);

                    endpointCardBody.appendChild(form);

                    const responseDiv = document.createElement('pre');
                    responseDiv.classList.add('response', 'mt-3'); // Add margin top
                    endpointCardBody.appendChild(responseDiv);

                    endpointCollapse.appendChild(endpointCardBody);
                    endpointCard.appendChild(endpointCollapse);

                    tabPane.appendChild(endpointCard);
                } else {
                    console.warn(`Endpoint "${endpointName}" found in endpointGroups.json but not in endpoint_params.json`);
                }
            });

            endpointTabContent.appendChild(tabPane);
            firstGroup = false;
        }
    })
    .catch(error => {
        console.error('Error loading data:', error);
        endpointTabContent.textContent = 'Error loading endpoints or groups.';
    });
