import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;
Alpine.plugin(intersect);

window.Alpine = Alpine;
Alpine.start();

// Keyboard Shortcuts
window.addEventListener('keydown', (e) => {
    // Ctrl + K or Cmd + K (Mac) for Search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    }
});
