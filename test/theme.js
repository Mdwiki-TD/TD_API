// Theme toggle functionality
const themeToggle = document.querySelector('.theme-toggle');
const themeIcon = themeToggle.querySelector('i');
const htmlElement = document.documentElement;

// Available themes
const themes = {
    system: {
        icon: 'bi-circle-half',
        label: 'Auto'
    },
    dark: {
        icon: 'bi-moon-stars-fill',
        label: 'Dark'
    },
    light: {
        icon: 'bi-sun-fill',
        label: 'Light'
    }
};

// Function to get system theme preference
function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

// Function to update theme based on preference
function updateTheme(preference) {
    const theme = preference === 'system' ? getSystemTheme() : preference;
    htmlElement.setAttribute('data-bs-theme', theme);
    updateThemeIcon(preference);
}

// Update theme when system preference changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (localStorage.getItem('theme') === 'system') {
        updateTheme('system');
    }
});

// Function to create theme dropdown menu
function createThemeMenu() {
    const menu = document.createElement('div');
    menu.className = 'theme-menu';

    Object.entries(themes).forEach(([value, { icon, label }]) => {
        const item = document.createElement('button');
        item.className = 'theme-menu-item';
        item.innerHTML = `<i class="bi ${icon}"></i> ${label}`;
        item.addEventListener('click', () => {
            localStorage.setItem('theme', value);
            updateTheme(value);
            menu.remove();
        });
        menu.appendChild(item);
    });

    return menu;
}

// Initialize theme
const savedTheme = localStorage.getItem('theme') || 'system';
updateTheme(savedTheme);

// Toggle theme menu on click
let activeMenu = null;
themeToggle.addEventListener('click', (e) => {
    e.stopPropagation();

    if (activeMenu) {
        activeMenu.remove();
        activeMenu = null;
        return;
    }

    const menu = createThemeMenu();
    document.body.appendChild(menu);

    // Position menu below button
    const buttonRect = themeToggle.getBoundingClientRect();
    menu.style.position = 'fixed';
    menu.style.top = `${buttonRect.bottom + 5}px`;
    menu.style.right = `${window.innerWidth - buttonRect.right}px`;

    activeMenu = menu;
});

// Close menu when clicking outside
document.addEventListener('click', () => {
    if (activeMenu) {
        activeMenu.remove();
        activeMenu = null;
    }
});

// Update theme icon based on current theme
function updateThemeIcon(preference) {
    const { icon } = themes[preference];
    themeIcon.className = `bi ${icon}`;
}
