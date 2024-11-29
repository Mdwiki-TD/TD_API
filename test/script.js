const endpoints = [
    'pages',
    'pages_users',
    'views',
    'categories',
    'full_translators',
    'projects',
    'settings',
    'words',
    'translate_type',
    'coordinator',
    'count_pages',
    'graph_data',
    'inter_wiki',
    'lang_names',
    'lang_names_new',
    'lang_views',
    'leaderboard_table',
    'qids',
    'qids_others',
    'site_matrix',
    'status',
    'user_views',
    'users',
    'users_by_last_pupdate',
    'users_by_last_pupdate_old'
];

function createParamInput(param) {
    const div = document.createElement('div');
    div.className = 'param-group';
    
    const label = document.createElement('label');
    label.textContent = param.name;
    
    let input;
    
    // Special handling for target_empty and target_notempty
    if (param.name === 'target_empty' || param.name === 'target_notempty') {
        const radioGroup = document.createElement('div');
        radioGroup.className = 'radio-group';
        
        // Create radio buttons
        const options = [
            { value: '', label: 'Not selected' },
            { value: 'yes', label: 'Yes' }
        ];
        
        options.forEach(option => {
            const radioDiv = document.createElement('div');
            radioDiv.className = 'radio-option';
            
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'target_status'; // Same name for both to make them exclusive
            radio.value = option.value;
            radio.id = `${param.name}_${option.value}`;
            
            // Set default selection for the first option (Not selected)
            if (option.value === '') {
                radio.checked = true;
            }
            
            // Add change event to handle mutual exclusivity
            radio.addEventListener('change', function() {
                if (this.checked && this.value === 'yes') {
                    // Clear the other target parameter if it exists
                    const otherParam = param.name === 'target_empty' ? 'target_notempty' : 'target_empty';
                    const otherRadios = document.querySelectorAll(`input[name="target_status"][id^="${otherParam}"]`);
                    otherRadios.forEach(radio => radio.checked = false);
                }
            });
            
            const radioLabel = document.createElement('label');
            radioLabel.textContent = option.label;
            radioLabel.htmlFor = radio.id;
            
            radioDiv.appendChild(radio);
            radioDiv.appendChild(radioLabel);
            radioGroup.appendChild(radioDiv);
        });
        
        div.appendChild(label);
        div.appendChild(radioGroup);
        return div;
    }
    
    // Original code for other parameters
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
            <span class="endpoint-url">/api/?get=${endpoint}</span>
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
    const inputs = button.parentElement.querySelectorAll('input:not([type="radio"]), select');
    inputs.forEach(input => {
        if (input.value) {
            params.set(input.name, input.value);
        }
    });

    // Handle radio buttons for target status
    const checkedRadio = button.parentElement.querySelector('input[type="radio"][name="target_status"]:checked');
    if (checkedRadio && checkedRadio.value === 'yes') {
        // Set the appropriate parameter based on which radio group was checked
        const paramName = checkedRadio.id.startsWith('target_empty') ? 'target_empty' : 'target_notempty';
        params.set(paramName, 'yes');
    }

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
    
    endpoints.forEach(endpoint => {
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
        if (endpointParams[endpoint] && endpointParams[endpoint].params) {
            endpointParams[endpoint].params.forEach(param => {
                paramsContainer.appendChild(createParamInput(param));
            });
        }
        
        container.appendChild(div);
    });
}

// Initialize when the document is loaded
document.addEventListener('DOMContentLoaded', loadEndpointParams);
