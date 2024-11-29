const endpointGroups = {
    pages: [
        'pages',
        'pages_users'
    ],
    identifiers: [
        'qids',
        'qids_others'
    ],
    views: [
        'views',
        'user_views',
        'lang_views'
    ],
    users: [
        'users',
        'users_by_last_pupdate',
        'users_by_last_pupdate_old',
        'full_translators',
        'coordinator'
    ],
    statistics: [
        'status',
        'leaderboard_table',
        'count_pages',
        'graph_data'

    ],
    languages: [
        'lang_names',
        'lang_names_new',
        'site_matrix',
        'translate_type'

    ],
    other: [
        'categories',
        'projects',
        'settings',
        'words',
        'inter_wiki'
    ]
};

// Flatten endpoints array for compatibility with existing code
const endpoints = Object.values(endpointGroups).flat();

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
            <button class="try-btn" onclick="testEndpoint('${endpoint}', this)">Try it</button>
            <div class="url-display"></div>
            <div class="response"></div>
        </div>
    `;
    return div;
}

async function testEndpoint(endpoint, button) {
    const params = new URLSearchParams();
    params.set('get', endpoint);
    params.set('limit', '50');

    // Get all input values for this endpoint
    const inputs = button.parentElement.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.value) {
            params.set(input.name, input.value);
        }
    });

    // Create the full URL
    const url = `../index.php?${params.toString()}`;

    // Display the URL
    const urlDisplay = button.parentElement.querySelector('.url-display');
    urlDisplay.textContent = url;

    try {
        const response = await fetch(url);
        const data = await response.json();

        const responseElement = button.parentElement.querySelector('.response');
        responseElement.textContent = JSON.stringify(data, null, 2);
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

            // Add endpoint-specific parameters
            if (endpoint === 'pages_users') {
                // استخدام نفس معلمات pages
                endpointParams['pages'].params.forEach(param => {
                    paramsContainer.appendChild(createParamInput(param));
                });
            } else if (endpointParams[endpoint] && endpointParams[endpoint].params) {
                endpointParams[endpoint].params.forEach(param => {
                    paramsContainer.appendChild(createParamInput(param));
                });
            }

            groupContainer.appendChild(div);
        });
    }
}

// Initialize when the document is loaded
document.addEventListener('DOMContentLoaded', loadEndpointParams);
