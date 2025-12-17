/**
 * Universal Theme Initializer
 * This script loads immediately to prevent theme flash
 * Must be loaded in <head> before page renders
 */

(function() {
    'use strict';

    // Get saved theme from localStorage (consistent across all pages)
    const savedTheme = localStorage.getItem('theme') || 'light';

    // Apply theme immediately to html element
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Also add dark-mode class to body for legacy support (home page)
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        // Add to body as well when it exists
        if (document.body) {
            document.body.classList.add('dark-mode');
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                document.body.classList.add('dark-mode');
            });
        }
    }
})();
