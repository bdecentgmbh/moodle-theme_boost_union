<?php
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
 * Theme Boost Union - Navigation layout include.
 *
 * Adds the template context which controls the active navigation layout (default top navbar,
 * left sidebar or mega menu) and, for the left sidebar layout, the enriched navigation items.
 *
 * This file is included by the drawers layout after the base $templatecontext has been composed.
 *
 * @package   theme_boost_union
 * @copyright 2026 bdecent GmbH <info@bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $OUTPUT, $PAGE;

// Resolve the configured navigation layout.
$navigationlayout = theme_boost_union_get_navigationlayout();

// Expose simple boolean flags for the templates so they can switch the navigation chrome.
$templatecontext['navlayoutdefault'] = ($navigationlayout === THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_DEFAULT);
$templatecontext['navlayoutsidebar'] = ($navigationlayout === THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_SIDEBAR);
$templatecontext['navlayoutmegamenu'] = ($navigationlayout === THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_MEGAMENU);

// Both the sidebar and the mega menu render a brand logo. Resolve the logo URL based on the configured logo type
// (the full logo or the compact logo). If the chosen logo is not configured, the URL stays empty and the templates
// fall back to the site name.
if ($templatecontext['navlayoutsidebar'] || $templatecontext['navlayoutmegamenu']) {
    $logotype = get_config('theme_boost_union', 'navigationlogotype');
    if ($logotype === THEME_BOOST_UNION_SETTING_NAVLOGO_FULL) {
        $logourl = $OUTPUT->get_logo_url(null, 80);
    } else {
        $logourl = $OUTPUT->get_compact_logo_url();
    }
    $templatecontext['navlogourl'] = $logourl ? $logourl->out(false) : '';
}

// Both the left sidebar and the mega menu render the primary navigation as a vertical, icon-decorated list.
// Enrich the (already vertical) mobile primary navigation items with an index, icon, plain label and monogram,
// so that the sidebar rail and the mega menu cards can show a recognisable glyph and tidy label for each item.
// Always use the main-location navigation (not $mobileprimarynav, which switches to the bottom bar menu when one
// exists) so that the sidebar and mega menu show the main navigation and do not duplicate the bottom bar menu.
$navlayoutsource = $templatecontext['mainmobileprimarynav'] ?? $templatecontext['mobileprimarynav'] ?? [];
$navlayoutusesitems = ($templatecontext['navlayoutsidebar'] || $templatecontext['navlayoutmegamenu']);
if ($navlayoutusesitems && !empty($navlayoutsource)) {
    $navitems = [];
    $index = 0;
    foreach ($navlayoutsource as $item) {
        // The nav items can be arrays or objects; normalize to an array for processing and rendering.
        $itemarr = (array) $item;

        // Add a stable index, used to build unique ids for the collapsible submenus.
        $itemarr['navindex'] = $index++;

        // Extract the item's own leading icon markup from its text, if any. Smart menu items carry their configured
        // icon (a Font Awesome <i> or an <img>) inside the text; this is the most accurate glyph for the item.
        $itemicon = '';
        $itemtext = (string) ($itemarr['text'] ?? '');
        if ($itemtext !== '' && preg_match('~^\s*(<i\b[^>]*>.*?</i>|<img\b[^>]*>)~is', $itemtext, $iconmatch)) {
            $itemicon = $iconmatch[1];
        }
        $itemarr['navitemicon'] = $itemicon;

        // Derive a Font Awesome icon from the item URL (used as a fallback for core nav items without their own icon).
        $itemarr['navicon'] = theme_boost_union_get_navsidebar_icon($itemarr['url'] ?? '');

        // Derive a plain-text label (used for tooltips and the mega menu cards) and a one-character monogram from it
        // (used as the rail/card glyph for items which have neither their own icon nor a mapped one).
        $label = isset($itemarr['text']) ? trim(strip_tags((string) $itemarr['text'])) : '';
        $itemarr['navlabel'] = $label;
        $itemarr['navmonogram'] = ($label !== '') ? core_text::strtoupper(core_text::substr($label, 0, 1)) : '';

        $navitems[] = $itemarr;
    }
    $templatecontext['navsidebaritems'] = $navitems;
}

// Process the left sidebar navigation layout.
if ($templatecontext['navlayoutsidebar']) {
    // Determine the default collapsed (icon-rail) state from the admin setting. The user's own choice, if any,
    // is applied client-side and overrides this server-side default.
    $sidebarstate = get_config('theme_boost_union', 'navigationsidebardefaultstate');
    $templatecontext['navsidebarcollapsed'] = ($sidebarstate === THEME_BOOST_UNION_SETTING_SIDEBARSTATE_COLLAPSED);

    // Determine where the sidebar logo / site name should be displayed and expose it as a CSS class on the wrapper.
    $sidebarlogo = get_config('theme_boost_union', 'navigationsidebarlogo');
    if (empty($sidebarlogo)) {
        $sidebarlogo = THEME_BOOST_UNION_SETTING_SIDEBARLOGO_BOTH;
    }
    $templatecontext['navsidebarlogoclass'] = 'theme-navsidebar-logo-' . $sidebarlogo;

    // Determine whether the collapsed sidebar should expand on hover and expose it as a CSS class on the wrapper.
    $expandonhover = get_config('theme_boost_union', 'navigationsidebarexpandonhover');
    if ($expandonhover === THEME_BOOST_UNION_SETTING_SELECT_YES) {
        $templatecontext['navsidebarlogoclass'] .= ' theme-navsidebar-hoverexpand';
    }
}

// Process the mega menu navigation layout.
if ($templatecontext['navlayoutmegamenu']) {
    // Determine the trigger visibility (combo keeps a visible labelled trigger, hidden shows only a bare icon).
    $trigger = get_config('theme_boost_union', 'navigationmegamenutrigger');
    $templatecontext['megamenutriggerhidden'] = ($trigger === THEME_BOOST_UNION_SETTING_MEGAMENUTRIGGER_HIDDEN);

    // When editing and the corresponding setting is enabled, show the regular navbar instead of the mega menu trigger,
    // so that editing the page (including the mega menu block region) is more convenient.
    $showwhenediting = get_config('theme_boost_union', 'navigationmegamenushowwhenediting');
    $templatecontext['megamenushownavbar'] = ($PAGE->user_is_editing()
            && $showwhenediting === THEME_BOOST_UNION_SETTING_SELECT_YES);
}

// Process the secondary navigation position.
$secondarynavposition = get_config('theme_boost_union', 'secondarynavigationposition');
if (empty($secondarynavposition)) {
    $secondarynavposition = THEME_BOOST_UNION_SETTING_SECONDARYNAV_DEFAULT;
}
// Expose a wrapper class and boolean flags for the templates.
$templatecontext['secondarynavposclass'] = 'theme-secondarynav-' . $secondarynavposition;
$templatecontext['secondarynavactionmenu'] =
        ($secondarynavposition === THEME_BOOST_UNION_SETTING_SECONDARYNAV_ACTIONMENU);
// The 'below navbar' position only makes sense in the default (top navbar) navigation layout.
$templatecontext['secondarynavbelownavbar'] =
        ($secondarynavposition === THEME_BOOST_UNION_SETTING_SECONDARYNAV_BELOWNAVBAR && $templatecontext['navlayoutdefault']);
// The content needs an additional top offset only when the 'below navbar' bar is actually shown.
$templatecontext['secondarynavbelownavbaractive'] =
        (!empty($templatecontext['secondarynavbelownavbar']) && !empty($templatecontext['secondarymoremenu']));
