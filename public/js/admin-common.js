/**
 * Common JavaScript for Filament Admin Panel
 */

document.addEventListener('DOMContentLoaded', function () {
    // Wrap logo in anchor tag for proper navigation
    const logo = document.querySelector('.fi-logo');
    if (logo && !logo.closest('a')) {
        const link = document.createElement('a');
        link.href = window.landingRoute || '/';
        link.className = 'fi-logo-wrap';
        logo.parentNode.insertBefore(link, logo);
        link.appendChild(logo);
    }
});
