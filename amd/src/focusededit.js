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
 * Theme Boost Union - JS code for the focused editing mode.
 *
 * Adds a toggle button next to the edit mode switch which switches between the focused editing state (empty block
 * regions and 'Add block' buttons hidden, controlled by the theme-editing-focused body class) and the full editing
 * state. The choice is remembered on the user's device using localStorage. The focused state is applied server-side
 * by default; this module reconciles it with the stored preference.
 *
 * @module     theme_boost_union/focusededit
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str'], function(Str) {
    "use strict";

    // The localStorage key under which the user's focused preference is stored.
    const STORAGE_KEY = 'theme_boost_union-editing-focused';

    // The body class which puts editing into the focused state.
    const FOCUSED_CLASS = 'theme-editing-focused';

    /**
     * Read the stored focused preference.
     *
     * @return {?boolean} True/false if a preference is stored, null otherwise.
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
            return null;
        }
        return null;
    };

    /**
     * Store the focused preference.
     *
     * @param {boolean} focused Whether editing is focused.
     */
    const storePreference = function(focused) {
        try {
            window.localStorage.setItem(STORAGE_KEY, focused ? '1' : '0');
        } catch (e) {
            return;
        }
    };

    /**
     * Reflect the current state on the toggle button. The button is 'pressed' when the block regions are shown
     * (i.e. when editing is NOT focused).
     *
     * @param {HTMLElement} button The toggle button.
     * @param {boolean} focused Whether editing is focused.
     */
    const updateButton = function(button, focused) {
        button.setAttribute('aria-pressed', focused ? 'false' : 'true');
        button.classList.toggle('active', !focused);
    };

    /**
     * Initialise the focused editing toggle.
     */
    const init = function() {
        const body = document.body;
        const editswitch = document.querySelector('.editmode-switch-form');
        if (!editswitch) {
            return;
        }

        // Reconcile the (server-default focused) state with the user's stored preference.
        const stored = getStoredPreference();
        if (stored !== null) {
            body.classList.toggle(FOCUSED_CLASS, stored);
        }

        // Build the toggle button.
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn theme-focusededit-toggle';
        button.innerHTML = '<i class="fa-solid fa-table-cells-large" aria-hidden="true"></i>';
        updateButton(button, body.classList.contains(FOCUSED_CLASS));

        // Add the accessible label and tooltip once the strings are available.
        Str.get_strings([
            {key: 'editingfocustoggle', component: 'theme_boost_union'},
            {key: 'editingfocustoggle_desc', component: 'theme_boost_union'}
        ]).then(function(strings) {
            button.setAttribute('title', strings[1]);
            const sr = document.createElement('span');
            sr.className = 'visually-hidden';
            sr.textContent = strings[0];
            button.appendChild(sr);
            return strings;
        }).catch(function() {
            return;
        });

        // Toggle the focused state on click and persist the choice.
        button.addEventListener('click', function() {
            const focused = !body.classList.contains(FOCUSED_CLASS);
            body.classList.toggle(FOCUSED_CLASS, focused);
            updateButton(button, focused);
            storePreference(focused);
        });

        // Place the button right next to the edit mode switch.
        editswitch.insertAdjacentElement('afterend', button);
    };

    return {
        init: init
    };
});
