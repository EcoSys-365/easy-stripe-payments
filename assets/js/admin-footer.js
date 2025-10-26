/*
 * On DOM content loaded:
 * - Get the top-level menu item by its ID.
 * - Exit early if the menu item is not found.
 * - Remove the 'current' class from all submenu list items and their links to reset active states.
 * - Retrieve the current active tab from the URL query parameters (default to 'welcome' if none found).
 * - Based on the active tab, determine which second-level menu item should be highlighted:
 *     - If the tab is one of 'forms', 'preview', 'recurring', or 'mails', highlight the 4th submenu item.
 *     - Otherwise, highlight the 2nd submenu item for the 'welcome' tab.
 * - Add the 'current' class to the determined second-level menu item.
 * - Find the submenu link that corresponds exactly to the current tab and add the 'current' class to it and its parent <li>.
 * - Also add the 'current' class to the top-level menu item to highlight it.
*/
document.addEventListener('DOMContentLoaded', function () {

    const topMenuItem = document.getElementById('toplevel_page_espd_main');

    // Exit if the top-level menu item is not found
    if ( !topMenuItem ) return;

    // Remove 'current' class from all submenu items and their links
    const submenuItems = topMenuItem.querySelectorAll('.wp-submenu li');
    submenuItems.forEach(li => {
        li.classList.remove('current');
        const link = li.querySelector('a');
        if (link) link.classList.remove('current');
    });

    // Determine the active tab from the URL parameters
    const currentTab = new URLSearchParams(window.location.search).get('tab') || 'welcome';

    // Highlight a specific second-level menu item based on the current tab
    if ( currentTab == 'welcome' || currentTab == 'forms' || currentTab == 'preview' || currentTab == 'recurring' || currentTab == 'mails' ) {

        let secondMenuItem;

        if ( currentTab == 'forms' || currentTab == 'preview' || currentTab == 'recurring' || currentTab == 'mails' ) {
            secondMenuItem = topMenuItem.querySelector('ul li:nth-child(4)');
        } else {
            secondMenuItem = topMenuItem.querySelector('ul li:nth-child(2)');    
        }

        if ( secondMenuItem ) {
            // Add 'current' class to the matched second menu item
            secondMenuItem.classList.add('current');
        }            
    }           

    // Add 'current' class to the link and its parent <li> that matches the current tab
    const activeLink = topMenuItem.querySelector(`.wp-submenu a[href$="tab=${currentTab}"]`);
    if (activeLink) {
        activeLink.classList.add('current');
        const parentLi = activeLink.closest('li');
        if (parentLi) parentLi.classList.add('current');

        // Also highlight the top-level menu item
        topMenuItem.classList.add('current');
    }
    
});