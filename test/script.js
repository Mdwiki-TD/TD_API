let endpointGroups = {};
let endpoints = [];

// Load endpoint groups from JSON file
async function loadEndpointGroups() {
    try {
        const response = await fetch('../endpointGroups.json');
        endpointGroups = await response.json();
        // Flatten endpoints array for compatibility with existing code
        endpoints = Object.values(endpointGroups).flat();
    } catch (error) {
        console.error('Error loading endpoint groups:', error);
    }
}

function createParamInput(param) {
    const div = document.createElement('div');
    div.className = 'paramgroup';
    // Special handling for distinct parameter
    if (param.type === 'switch') {
        div.innerHTML = `
            <div class="input-group form-control mb-3">
                <div class="input-group-prepend">
                    <span class="me-3">&nbsp;${param.name}:</span>
                </div>
                <div class="form-check form-switch form-inline">
                    <input class="form-check-input" type="checkbox" name="${param.name}" value="1">
                </div>
            </div>`;
        return div;
    }

    let input;
    if (param.type === 'select' && param.options) {
        input = document.createElement('select');
        // add class form-select
        input.className = 'form-select';
        param.options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option;
            opt.textContent = option || 'Select...';
            input.appendChild(opt);
        });
        // ---
        const label = document.createElement('label');
        label.textContent = param.name;
        input.name = param.name;
        div.appendChild(label);
        div.appendChild(input);
        // ---
        return div;
    };
    // input = document.createElement('input');
    var type = param.type || 'text';
    var value = param.value || '';
    // ---
    var names_no_more = [
        "offset",
        "limit",
        "order",
        "select",
    ];
    // ---
    if (names_no_more.includes(param.name)) {
        div.className = 'param-group';
        let label = document.createElement('label');
        label.textContent = param.name;
        input = document.createElement('input');
        input.name = param.name;
        input.type = type;
        input.placeholder = param.placeholder || '';
        input.value = value;
        div.appendChild(label);
        div.appendChild(input);
        return div;
    }
    // ---
    input = document.createElement('div');
    // ---
    // default
    var innerHTML = `
    <div class="param-group">
        <label>${param.name}</label>
        <input name="${param.name}" type="${type}" placeholder="${param.placeholder}" value="${value}">
    </div>`;
    // ---
    let randomNumber = Math.floor(Math.random() * 1000);
    // ---
    if (!param.no_mt_options) {
        // ---
        if (param.type === 'text') {
            innerHTML = `
                <label>${param.name}</label>
                <div class="form-control one_group">
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="manualInput_${randomNumber}" name="${param.name}" value="manual" checked="">
                        <label class="form-check-label" for="manualInput_${randomNumber}">
                            <input name="!" type="${type}" class="textInput" id="manual_value" placeholder="${param.placeholder}" value="${value}">
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="empty_${randomNumber}" name="${param.name}" value="empty">
                        <label class="form-check-label" for="empty_${randomNumber}">empty</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="notEmpty_${randomNumber}" name="${param.name}" value="not_empty">
                        <label class="form-check-label" for="notEmpty_${randomNumber}">not_empty</label>
                    </div>
                </div>
            `;
        };
        // ---
        if (param.type === 'number') {
            innerHTML = `
                <label>${param.name}</label>
                <div class="form-control one_group">
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="manualInput_${randomNumber}" name="${param.name}" value="manual" checked>
                        <label class="form-check-label" for="manualInput_${randomNumber}">
                            <input name="!" type="${type}" class="textInput" id="manual_value" placeholder="${param.placeholder}" value="${value}">
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="not_0" name="${param.name}" value="&#62;0">
                        <label class="form-check-label" for="not_0">&#62;0</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" class="form-check-input" id="is_0" name="${param.name}" value="0">
                        <label class="form-check-label" for="is_0">= 0</label>
                    </div>
                </div>
            `;
        };
    };
    // ---
    input.innerHTML = innerHTML;
    // ---
    div.appendChild(input);
    // ---
    return div;
}

function createEndpoint(endpoint) {
    const div = document.createElement('div');
    div.className = 'endpoint';
    // if endpoint in hash, add active class
    if (window.location.hash === `#${endpoint}`) {
        div.classList.add('active');
    }
    div.innerHTML = `
        <div id="${endpoint}" class="endpoint-header" onclick="this.closest('.endpoint').classList.toggle('active')">
            <a href="#${endpoint}"><i class="bi bi-link-45deg"></i></a>
            <span class="method">GET</span>
            <span class="endpoint-url">?get=${endpoint}</span>
            <!-- <button class="toggle-btn" onclick="event.stopPropagation()">▼</button> -->
            <button class="toggle-btn">▼</button>
        </div>
        <div class="endpoint-content">
            <form>
            <div class="params-container"></div>
            </form>
            <div class="row">
                <div class="col-2">
                    <button class="method" onclick="testEndpoint('${endpoint}', this)">Try it</button>
                </div>
                <div class="col-10">
                    <div class="url-display"></div>
                </div>
            </div>
            <div class="response language-json"></div>
        </div>
    `;
    return div;
}

async function testEndpoint(endpoint, button) {
    const params = new URLSearchParams();
    params.append('get', endpoint);

    // Get parameters
    const endpointContent = button.closest('.endpoint-content');
    // ---
    const paramInputs = endpointContent.querySelectorAll('input');
    // const paramInputs = endpointContent.querySelectorAll('.param-group input, .param-group select');
    paramInputs.forEach(input => {
        var value = input.value;
        if (value === 'manual') {
            // get value from #manual_value in parent of input
            // value = $(`#${input.name}_manual`).val();
            value = $(input).parent().find('#manual_value').val();
        };
        if (input.name !== '!' && input.name !== '' && value !== '') {
            if (['checkbox', 'radio'].includes(input.type)) {
                if (input.checked) {
                    params.append(input.name, value);
                }
            } else if (value) {
                params.append(input.name, value);
            }
        }
    });
    // ---
    const paramInputs_select = endpointContent.querySelectorAll('select');
    // const paramInputs = endpointContent.querySelectorAll('.param-group input, .param-group select');
    paramInputs_select.forEach(input => {
        // ---
        console.log(input);
        // ---
        // console.table(input);
        // ---
        let value;
        // ---
        for (let i = 0; i < input.options.length; i++) {
            if (input.options[i].selected) {
                value = input.options[i].value;
                break;
            }
        }
        // ---
        console.log(value);
        // ---
        if (value) {
            params.append(input.name, value);
        }
    });
    // ---
    var baseUrl
    // ---
    if (window.location.hostname === 'localhost') {
        // baseUrl = `${window.location.protocol}//${window.location.host}/index.php`;
        baseUrl = `${window.location.protocol}//${window.location.host}/api/api/proxy.php`;
    } else {
        baseUrl = `https://mdwiki.toolforge.org/api.php`;
    }
    // ---
    baseUrl = `${window.location.protocol}//${window.location.host}/api.php`;
    // ---
    const url = `${baseUrl}?${params.toString()}`;

    // Display the URL
    const urlDisplay = endpointContent.querySelector('.url-display');
    // ---
    // var url2 = `https://mdwiki.toolforge.org/api.php?${params.toString()}`;
    // ---
    urlDisplay.innerHTML = `<a href="${url}" target="_blank">${url}</a>`;
    // ---
    try {
        const response = await fetch(url, {
            method: 'GET',
            mode: 'cors',
            headers: {
                'Origin': window.location.origin
            }
        });
        const data = await response.json();

        const responseElement = endpointContent.querySelector('.response');
        const formattedJson = JSON.stringify(data, null, 2);
        responseElement.innerHTML = `<code>${formattedJson}</code>`;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Load endpoint parameters from JSON file
async function loadEndpointParams() {
    try {
        const response = await fetch('../endpoint_params.json');
        const endpointParams = await response.json();
        generateEndpoints(endpointParams);
    } catch (error) {
        console.error('Error loading endpoint parameters:', error);
    }
}

// Generate endpoints

function generateEndpoints(endpointParams) {
    const tabHeadersContainer = document.getElementById('endpointTabs');
    const tabContentContainer = document.getElementById('endpointTabContent');

    let isFirstGroup = true;

    for (const [groupName, groupEndpoints] of Object.entries(endpointGroups)) {
        const groupId = groupName.replace(/\s+/g, '-').toLowerCase(); // Create a valid ID

        // Create tab header (li element)
        const tabHeaderLi = document.createElement('li');
        tabHeaderLi.className = 'nav-item';
        tabHeaderLi.setAttribute('role', 'presentation');

        const tabHeaderButton = document.createElement('button');
        tabHeaderButton.className = `nav-link ${isFirstGroup ? 'active' : ''}`;
        tabHeaderButton.id = `${groupId}-tab`;
        tabHeaderButton.setAttribute('data-bs-toggle', 'tab');
        tabHeaderButton.setAttribute('data-bs-target', `#${groupId}`);
        tabHeaderButton.setAttribute('type', 'button');
        tabHeaderButton.setAttribute('role', 'tab');
        tabHeaderButton.setAttribute('aria-controls', groupId);
        tabHeaderButton.setAttribute('aria-selected', isFirstGroup ? 'true' : 'false');
        tabHeaderButton.textContent = groupName.charAt(0).toUpperCase() + groupName.slice(1);

        tabHeaderLi.appendChild(tabHeaderButton);
        tabHeadersContainer.appendChild(tabHeaderLi);

        // Create tab content (div element)
        const tabContentDiv = document.createElement('div');
        tabContentDiv.className = `tab-pane fade show ${isFirstGroup ? 'active' : ''}`;
        tabContentDiv.id = groupId;
        tabContentDiv.setAttribute('role', 'tabpanel');
        tabContentDiv.setAttribute('aria-labelledby', `${groupId}-tab`);

        // Add endpoints for this group within the tab content
        groupEndpoints.forEach(endpoint => {
            const div = createEndpoint(endpoint);
            const paramsContainer = div.querySelector('.params-container');
            let end_params = endpointParams[endpoint].params;
            // ---
            if (end_params === undefined && endpointParams[endpoint].redirect) {
                end_params = endpointParams[endpointParams[endpoint].redirect].params;
            };
            // ---
            if (end_params) {
                // sort end_params by param.type
                // end_params.sort((a, b) => { return a.type.localeCompare(b.type); })
                end_params.forEach(param => {
                    paramsContainer.appendChild(createParamInput(param));
                });
            }

            // check if paramsContainer has limit before appending
            if (!paramsContainer.querySelector(`input[name="offset"]`)) {
                // Add common parameter for offset
                const limitParam = {
                    name: 'offset',
                    type: 'number',
                    placeholder: 'Offset of results',
                    value: '0'
                };
                paramsContainer.appendChild(createParamInput(limitParam));
            }

            // check if paramsContainer has limit before appending
            if (!paramsContainer.querySelector(`input[name="limit"]`)) {
                // Add common parameter for limit
                const limitParam = {
                    name: 'limit',
                    type: 'number',
                    placeholder: 'Number of results',
                    value: '50'
                };
                paramsContainer.appendChild(createParamInput(limitParam));
            }

            tabContentDiv.appendChild(div);
        });

        tabContentContainer.appendChild(tabContentDiv);

        isFirstGroup = false; // Only the first group is active initially
    }
}

async function add_event() {
    $(".one_group").each(function () {
        var form = $(this);

        form.find("input[type='radio']").change(function () {
            var textInput = form.find(".textInput");

            if ($(this).val() === "manual") {
                textInput.prop("disabled", false);
            } else {
                textInput.prop("disabled", true);
            }
        });
    });
};
// Initialize when the document is loaded
document.addEventListener('DOMContentLoaded', async () => {
    await loadEndpointGroups();
    await loadEndpointParams();
    await add_event();
});
