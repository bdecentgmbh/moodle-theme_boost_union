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
 * Initializes and handles events in the user menu.
 *
 * @module     core/smartMenu
 * @copyright  2021 Moodle
 * @author     Mihail Geshoski <mihail@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import {space, enter} from 'core/key_codes';

/**
 * User menu constants.
 */
const selectors = {
    smartMenu: '.theme-boost-union-smartmenu-carousel',
    smartMenuCarousel: '#smartmenu-carousel',
    smartMenuCarouselItem: '#smartmenu-carousel .carousel-item',
    smartMenuCarouselItemActive: '#smartmenu-carousel .carousel-item.active',
    smartMenuCarouselNavigationLink: '#smartmenu-carousel .carousel-navigation-link',
};

/**
 * Register event listeners.
 */
const registerEventListeners = (smartMenu) => {

    var smartMenuDropDown = smartMenu.querySelector('.dropdown-menu');
    var observer = new MutationObserver(function() {
        if (!smartMenuDropDown.classList.contains('show')) {
            return;
        }
        const activeCarouselItem = smartMenu.querySelector(selectors.smartMenuCarouselItemActive)
        // Set the focus on the active carousel item.
        activeCarouselItem.focus();

        var element = smartMenu.querySelector(selectors.smartMenuCarousel);
        if (element !== undefined || element.length !== null) {
            // Resize all non-active carousel items to match the height and width of the current active (main)
            // carousel item to avoid sizing inconsistencies. This has to be done once the dropdown menu is fully
            // displayed ('shown.bs.dropdown') as the offsetWidth and offsetHeight cannot be obtained when the
            // element is hidden.

            element.style.minWidth = element.offsetWidth + 'px';
            element.style.minHeight = element.offsetHeight + 'px';
        }
    }, true);
    observer.observe(smartMenuDropDown, { attributes: true, childList: true });

    // Handle click events in the user menu.
    smartMenu.addEventListener('click', (e) => {

        // Handle click event on the carousel navigation (control) links in the user menu.
        if (e.target.matches(selectors.smartMenuCarouselNavigationLink)) {
            carouselManagement(e);
        }

    }, true);

    smartMenu.addEventListener('keydown', e => {
        // Handle keydown event on the carousel navigation (control) links in the user menu.
        if ((e.keyCode === space ||
            e.keyCode === enter) &&
            e.target.matches(selectors.smartMenuCarouselNavigationLink)) {
            e.preventDefault();
            carouselManagement(e);
        }
    }, true);

    /**
     * We do the same actions here even if the caller was a click or button press.
     *
     * @param {Event} e The triggering element and key presses etc.
     */
    const carouselManagement = e => {

        // By default the user menu dropdown element closes on a click event. This behaviour is not desirable
        // as we need to be able to navigate through the carousel items (submenus of the user menu) within the
        // user menu. Therefore, we need to prevent the propagation of this event and then manually call the
        // carousel transition.
        e.stopPropagation();
        // The id of the targeted carousel item.
        const targetedCarouselItemId = e.target.dataset.carouselTargetId;
        const targetedCarouselItem = smartMenu.querySelector('#' + targetedCarouselItemId);
        // Get the position (index) of the targeted carousel item within the parent container element.
        const index = Array.from(targetedCarouselItem.parentNode.children).indexOf(targetedCarouselItem);
        // Navigate to the targeted carousel item.
        $(smartMenu.querySelector(selectors.smartMenuCarousel)).carousel(index);

    };

    // Handle the 'hide.bs.dropdown' event (Fired when the dropdown menu is being closed).
    $(selectors.smartMenu).on('hide.bs.dropdown', () => {
        // Reset the state once the user menu dropdown is closed and return back to the first (main) carousel item
        // if necessary.
        $(smartMenu.querySelector(selectors.smartMenuCarousel)).carousel(0);
    });

    // Handle the 'slid.bs.carousel' event (Fired when the carousel has completed its slide transition).
    $(selectors.smartMenuCarousel).on('slid.bs.carousel', () => {
        const activeCarouselItem = smartMenu.querySelector(selectors.smartMenuCarouselItemActive);
        // Set the focus on the newly activated carousel item.
        activeCarouselItem.focus();
    });
};

/**
 * Initialize the user menu.
 */
const init = () => {
    const smartMenus = document.querySelectorAll(selectors.smartMenu);
    if (smartMenus !== undefined && smartMenus.length !== null) {
        smartMenus.forEach((e) => registerEventListeners(e));
    }
};

export default {
    init: init,
};
