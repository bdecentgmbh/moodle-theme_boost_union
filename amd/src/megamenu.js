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
 * Theme Boost Union - JS code for the mega menu navigation layout.
 *
 * Opens and closes the fullscreen mega menu overlay from the corner trigger, with a focus trap, Escape-to-close
 * and a body scroll lock while open. The open/close animation itself is handled in CSS (and respects
 * prefers-reduced-motion).
 *
 * @module     theme_boost_union/megamenu
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    "use strict";

    // The class added to the html element while the overlay is open (used to lock the page scroll).
    const BODY_OPEN_CLASS = 'theme-megamenu-open';

    // The element which had focus before the overlay was opened, so it can be restored on close.
    let lastFocus = null;

    /**
     * Return the focusable elements within a container.
     *
     * @param {HTMLElement} container The container element.
     * @return {HTMLElement[]} The visible focusable elements.
     */
    const getFocusable = function(container) {
        const selector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]),'
            + ' textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
        return Array.prototype.slice.call(container.querySelectorAll(selector)).filter(function(node) {
            return node.offsetParent !== null;
        });
    };

    /**
     * Open the mega menu overlay.
     *
     * @param {HTMLElement} overlay The overlay element.
     * @param {HTMLElement} trigger The trigger button.
     */
    const openOverlay = function(overlay, trigger) {
        lastFocus = document.activeElement;
        overlay.hidden = false;
        // Force a reflow so the opening transition runs after the element becomes visible.
        void overlay.offsetWidth;
        overlay.classList.add('is-open');
        document.documentElement.classList.add(BODY_OPEN_CLASS);
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
        const focusable = getFocusable(overlay);
        if (focusable.length) {
            focusable[0].focus();
        }
    };

    /**
     * Close the mega menu overlay.
     *
     * @param {HTMLElement} overlay The overlay element.
     * @param {HTMLElement} trigger The trigger button.
     */
    const closeOverlay = function(overlay, trigger) {
        overlay.classList.remove('is-open');
        document.documentElement.classList.remove(BODY_OPEN_CLASS);
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }

        // Hide the overlay once the closing transition has finished (or immediately under reduced motion).
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduced) {
            overlay.hidden = true;
        } else {
            window.setTimeout(function() {
                if (!overlay.classList.contains('is-open')) {
                    overlay.hidden = true;
                }
            }, 250);
        }

        if (lastFocus) {
            lastFocus.focus();
        }
    };

    /**
     * Initialise the mega menu overlay behaviour.
     */
    const init = function() {
        const overlay = document.getElementById('theme_boost_union-megamenu');
        const trigger = document.getElementById('theme_boost_union-megamenu-trigger');
        const closebtn = document.getElementById('theme_boost_union-megamenu-close');
        if (!overlay || !trigger) {
            return;
        }

        trigger.addEventListener('click', function() {
            openOverlay(overlay, trigger);
        });

        if (closebtn) {
            closebtn.addEventListener('click', function() {
                closeOverlay(overlay, trigger);
            });
        }

        // A mousedown directly on the overlay backdrop (not its children) closes it.
        overlay.addEventListener('mousedown', function(e) {
            if (e.target === overlay) {
                closeOverlay(overlay, trigger);
            }
        });

        // The messaging toggle opens the right-hand message drawer, which would otherwise be hidden behind the
        // overlay. Close the overlay when it is clicked so the drawer is visible.
        overlay.addEventListener('click', function(e) {
            if (e.target.closest('[data-region="popover-region-messages"]')) {
                closeOverlay(overlay, trigger);
            }
        });

        // Trap the focus inside the overlay while it is open.
        overlay.addEventListener('keydown', function(e) {
            if (e.key !== 'Tab') {
                return;
            }
            const focusable = getFocusable(overlay);
            if (!focusable.length) {
                return;
            }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });

        // Escape closes the overlay.
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !overlay.hidden) {
                closeOverlay(overlay, trigger);
            }
        });
    };

    return {
        init: init
    };
});
