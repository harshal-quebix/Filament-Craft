/**
 * Common JavaScript for Filament Admin Panel
 */

document.addEventListener('DOMContentLoaded', function () {
    // Completely disable logo click - remove href from parent anchor
    const logo = document.querySelector('.fi-logo');
    if (logo) {
        // Disable pointer events
        logo.style.pointerEvents = 'none';
        logo.style.cursor = 'default';
        
        // Find and disable parent anchor
        let parent = logo.parentElement;
        while (parent) {
            if (parent.tagName === 'A') {
                parent.removeAttribute('href');
                parent.style.pointerEvents = 'none';
                parent.style.cursor = 'default';
                parent.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                });
                break;
            }
            parent = parent.parentElement;
        }
    }
});
