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
 * Theme Boost Union - Hook to replace the "Add a block" button of a block region.
 *
 * @package    theme_boost_union
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_union\hook\output;

/**
 * Hook to replace the "Add a block" button of a block region.
 *
 * Dispatched only when Moodle core shows the button, that is in editing mode for users who may edit
 * blocks. As long as no plugin calls set_html(), the button stays exactly as Moodle core renders it.
 *
 * @package    theme_boost_union
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to replace the "Add a block" button of a block region.')]
#[\core\attribute\tags('theme_boost_union', 'output')]
final class add_block_button {
    /**
     * Constructor.
     *
     * @param \moodle_page $page The page the button is shown on.
     * @param string $region The block region, or '' for the page's default region.
     * @param string $html The button as Moodle core renders it.
     */
    public function __construct(
        /** @var \moodle_page The page the button is shown on. */
        public readonly \moodle_page $page,
        /** @var string The block region, or '' for the page's default region. */
        public readonly string $region,
        /** @var string The button's HTML. */
        private string $html,
    ) {
    }

    /**
     * The button's HTML.
     *
     * @return string
     */
    public function get_html(): string {
        return $this->html;
    }

    /**
     * Replace the button's HTML.
     *
     * @param string $html
     */
    public function set_html(string $html): void {
        $this->html = $html;
    }
}
