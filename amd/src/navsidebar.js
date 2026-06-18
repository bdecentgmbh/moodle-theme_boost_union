// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme Boost Union - JS code for the left navigation sidebar.
 *
 * On desktop the sidebar is persistent and toggles between its expanded and collapsed (icon-rail) state; the
 * user's choice is remembered on their device using localStorage. On mobile the sidebar behaves as an
 * off-canvas overlay which is opened with the floating menu button and closed via the backdrop, the toggle or
 * by following a navigation link.
 *
 * @module     theme_boost_union/navsidebar
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    "use strict";

    // The localStorage key under which the user's collapsed preference is stored.
    const STORAGE_KEY = 'theme_boost_union-navsidebar-collapsed';

    // The class which puts the sidebar (and the page) into the collapsed icon-rail state on desktop.
    const COLLAPSED_CLASS = 'theme-navsidebar-collapsed';

    // The class which opens the off-canvas sidebar overlay on mobile.
    const MOBILEOPEN_CLASS = 'theme-navsidebar-mobileopen';

    // The viewport at and above which the sidebar is persistent (matches the SCSS 'lg' breakpoint).
    const DESKTOP_QUERY = '(min-width: 992px)';

    /**
     * Whether the viewport is currently at desktop size.
     *
     * @return {boolean} True on desktop, false on mobile.
     */
    const isDesktop = function() {
        return window.matchMedia(DESKTOP_QUERY).matches;
    };

    /**
     * Read the stored collapsed preference.
     *
     * @return {?boolean} True or false if a preference is stored, null if none is stored or storage is unavailable.
     */
    const getStoredPreference = function() {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);
            if (value === '1') {
                return true;
            }
            if (value === '0') {
                return false;
            }
        } catch (e) {
            // Ignore storage access errors (e.g. private browsing) and fall back to the server-side default.
            return null;
        }
        return null;
    };

    /**
     * Store the collapsed preference.
     *
     * @param {boolean} collapsed Whether the sidebar is collapsed.
     */
    const storePreference = function(collapsed) {
        try {
            window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        } catch (e) {
            // Ignore storage access errors; the preference simply won't persist.
            return;
        }
    };

    /**
     * Reflect the current collapsed state on the toggle button (for assistive technology).
     *
     * @param {HTMLElement} toggle The toggle button.
     * @param {boolean} expanded Whether the sidebar is expanded.
     */
    const updateToggle = function(toggle, expanded) {
        if (toggle) {
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    };

    /**
     * Open or close the mobile off-canvas overlay.
     *
     * @param {HTMLElement} pagewrapper The page wrapper element.
     * @param {HTMLElement} mobiletoggle The floating mobile menu button.
     * @param {boolean} open Whether to open the overlay.
     */
    const setMobileOpen = function(pagewrapper, mobiletoggle, open) {
        pagewrapper.classList.toggle(MOBILEOPEN_CLASS, open);
        updateToggle(mobiletoggle, open);
    };

    /**
     * Initialise the sidebar toggle behaviour.
     */
    const init = function() {
        const pagewrapper = document.getElementById('page-wrapper');
        const sidebar = document.getElementById('theme_boost_union-navsidebar');
        const toggle = document.getElementById('theme_boost_union-navsidebar-toggle');
        const mobiletoggle = document.getElementById('theme_boost_union-navsidebar-mobiletoggle');
        if (!pagewrapper || !sidebar) {
            return;
        }

        // Apply the user's stored desktop preference, overriding the server-side default already in the markup.
        const stored = getStoredPreference();
        if (stored !== null) {
            pagewrapper.classList.toggle(COLLAPSED_CLASS, stored);
        }
        updateToggle(toggle, !pagewrapper.classList.contains(COLLAPSED_CLASS));

        // The header toggle collapses/expands the rail on desktop and closes the overlay on mobile.
        if (toggle) {
            toggle.addEventListener('click', function() {
                if (isDesktop()) {
                    const collapsed = pagewrapper.classList.toggle(COLLAPSED_CLASS);
                    updateToggle(toggle, !collapsed);
                    storePreference(collapsed);
                } else {
                    setMobileOpen(pagewrapper, mobiletoggle, false);
                }
            });
        }

        // The floating mobile button opens/closes the overlay.
        if (mobiletoggle) {
            mobiletoggle.addEventListener('click', function() {
                setMobileOpen(pagewrapper, mobiletoggle, !pagewrapper.classList.contains(MOBILEOPEN_CLASS));
            });
        }

        // Clicking the backdrop closes the overlay.
        const backdrop = sidebar.parentNode.querySelector('.bu-navsidebar__backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                setMobileOpen(pagewrapper, mobiletoggle, false);
            });
        }

        // Following a navigation link closes the overlay on mobile (the page is navigating away anyway, but this
        // keeps in-page anchors and same-page links tidy).
        sidebar.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]');
            if (link && !isDesktop()) {
                setMobileOpen(pagewrapper, mobiletoggle, false);
            }
        });

        // When resizing up to desktop, make sure the mobile overlay state is cleared.
        window.matchMedia(DESKTOP_QUERY).addEventListener('change', function(e) {
            if (e.matches) {
                setMobileOpen(pagewrapper, mobiletoggle, false);
            }
        });
    };

    return {
        init: init
    };
});
