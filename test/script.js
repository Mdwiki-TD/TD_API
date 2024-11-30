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
    div.className = 'param-group';
    /*
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="save" name="save" value="1">
            <label class="check-label" for="save">Auto save</label>
        </div>
    */
    // Special handling for distinct parameter
    if (param.name === 'distinct') {
        div.className = 'param-group form-check form-switch';

        const input = document.createElement('input');
        input.type = 'checkbox';
        input.className = 'form-check-input';
        input.id = 'distinct-switch';
        input.name = param.name;
        input.role = 'switch';

        const label = document.createElement('label');
        label.className = 'check-label';
        label.htmlFor = 'distinct-switch';
        label.textContent = 'Distinct';

        div.appendChild(input);
        div.appendChild(label);

        // Add change event to set the value
        input.addEventListener('change', function () {
            this.value = this.checked ? 'yes' : '';
        });

        return div;
    }

    const label = document.createElement('label');
    label.textContent = param.name;

    let input;
    if (param.type === 'select' && param.options) {
        input = document.createElement('select');
        param.options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option;
            opt.textContent = option || 'Select...';
            input.appendChild(opt);
        });
    } else {
        input = document.createElement('input');
        input.type = param.type || 'text';
        input.placeholder = param.placeholder || '';
        if (param.value) {
            input.value = param.value;
        }
    }

    input.name = param.name;
    div.appendChild(label);
    div.appendChild(input);
    return div;
}

function createEndpoint(endpoint) {
    const div = document.createElement('div');
    div.className = 'endpoint';
    div.innerHTML = `
        <div class="endpoint-header" onclick="this.closest('.endpoint').classList.toggle('active')">
            <span class="method">GET</span>
            <span class="endpoint-url">?get=${endpoint}</span>
            <button class="toggle-btn" onclick="event.stopPropagation()">▼</button>
        </div>
        <div class="endpoint-content">
            <div class="params-container"></div>
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
    const paramInputs = endpointContent.querySelectorAll('.param-group input, .param-group select');
    paramInputs.forEach(input => {
        if (input.value) {
            params.append(input.name, input.value);
        }
    });
    // ---
    var baseUrl
    // ---
    if (window.location.hostname === 'localhost') {
        // baseUrl = `${window.location.protocol}//${window.location.host}/index.php`;
        baseUrl = `${window.location.protocol}//${window.location.host}/api/proxy.php`;
    } else {
        baseUrl = `https://mdwiki.toolforge.org/api.php`;
    }
    // ---
    baseUrl = `${window.location.protocol}//${window.location.host}/index.php`;
    const url = `${baseUrl}?${params.toString()}`;

    // Display the URL
    const urlDisplay = endpointContent.querySelector('.url-display');
    // ---
    // var url2 = `https://mdwiki.toolforge.org/api.php?${params.toString()}`;
    // ---
    urlDisplay.innerHTML = `<a href="${url}" target="_blank">${url}</a>`;

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
    const container = document.getElementById('endpoints-container');

    // Create groups and add endpoints
    for (const [groupName, groupEndpoints] of Object.entries(endpointGroups)) {
        // Create group header
        const groupHeader = document.createElement('h3');
        groupHeader.textContent = groupName.charAt(0).toUpperCase() + groupName.slice(1);
        container.appendChild(groupHeader);

        // Create group container
        const groupContainer = document.createElement('div');
        groupContainer.className = 'endpoint-group';
        container.appendChild(groupContainer);

        // Add endpoints for this group
        groupEndpoints.forEach(endpoint => {
            const div = createEndpoint(endpoint);
            const paramsContainer = div.querySelector('.params-container');

            // Add common parameter for limit
            const limitParam = {
                name: 'limit',
                type: 'number',
                placeholder: 'Number of results',
                value: '50'
            };
            paramsContainer.appendChild(createParamInput(limitParam));

            if (endpointParams[endpoint] && endpointParams[endpoint].params) {
                endpointParams[endpoint].params.forEach(param => {
                    paramsContainer.appendChild(createParamInput(param));
                });
            }

            groupContainer.appendChild(div);
        });
    }
}

// Initialize when the document is loaded
document.addEventListener('DOMContentLoaded', async () => {
    await loadEndpointGroups();
    await loadEndpointParams();
});
