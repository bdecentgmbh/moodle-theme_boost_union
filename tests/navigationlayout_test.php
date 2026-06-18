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

namespace theme_boost_union;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
// Load the theme's lib.php (navigation layout constants) and locallib.php (the helper under test).
require_once($CFG->dirroot . '/theme/boost_union/lib.php');
require_once($CFG->dirroot . '/theme/boost_union/locallib.php');

/**
 * Theme Boost Union - Tests for the navigation layout helper.
 *
 * @package    theme_boost_union
 * @category   test
 * @copyright  2026 bdecent GmbH <info@bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class navigationlayout_test extends advanced_testcase {
    /**
     * The helper returns the default layout when the setting has never been configured.
     *
     * @covers ::theme_boost_union_get_navigationlayout
     * @return void
     */
    public function test_navigationlayout_defaults_when_unset(): void {
        $this->resetAfterTest();

        $this->assertEquals(
            THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_DEFAULT,
            theme_boost_union_get_navigationlayout()
        );
    }

    /**
     * The helper returns each supported layout when it is configured.
     *
     * @covers ::theme_boost_union_get_navigationlayout
     * @dataProvider supported_layouts_provider
     * @param string $layout The layout value to configure.
     * @return void
     */
    public function test_navigationlayout_returns_supported_value(string $layout): void {
        $this->resetAfterTest();

        set_config('navigationlayout', $layout, 'theme_boost_union');

        $this->assertEquals($layout, theme_boost_union_get_navigationlayout());
    }

    /**
     * Data provider listing all supported navigation layouts.
     *
     * @return array
     */
    public static function supported_layouts_provider(): array {
        return [
            'default' => [THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_DEFAULT],
            'sidebar' => [THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_SIDEBAR],
            'megamenu' => [THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_MEGAMENU],
        ];
    }

    /**
     * The helper falls back to the default layout when the setting holds an unknown value.
     *
     * @covers ::theme_boost_union_get_navigationlayout
     * @return void
     */
    public function test_navigationlayout_falls_back_on_invalid_value(): void {
        $this->resetAfterTest();

        set_config('navigationlayout', 'somethingbogus', 'theme_boost_union');

        $this->assertEquals(
            THEME_BOOST_UNION_SETTING_NAVIGATIONLAYOUT_DEFAULT,
            theme_boost_union_get_navigationlayout()
        );
    }

    /**
     * The sidebar icon helper maps well-known core navigation destinations to icons.
     *
     * @covers ::theme_boost_union_get_navsidebar_icon
     * @dataProvider navsidebar_icon_provider
     * @param string $url The navigation item URL.
     * @param string $expected The expected Font Awesome icon class.
     * @return void
     */
    public function test_navsidebar_icon_mapping(string $url, string $expected): void {
        $this->assertEquals($expected, theme_boost_union_get_navsidebar_icon($url));
    }

    /**
     * Data provider for the sidebar icon mapping.
     *
     * @return array
     */
    public static function navsidebar_icon_provider(): array {
        return [
            'dashboard' => ['https://example.com/my/', 'fa-gauge-high'],
            'my courses' => ['https://example.com/my/courses.php', 'fa-graduation-cap'],
            'course catalogue' => ['https://example.com/course/', 'fa-layer-group'],
            'calendar' => ['https://example.com/calendar/view.php', 'fa-calendar-days'],
            'grades' => ['https://example.com/grade/report/overview/index.php', 'fa-table-list'],
            'site administration' => ['https://example.com/admin/search.php', 'fa-screwdriver-wrench'],
            'site home (root)' => ['https://example.com/', 'fa-house'],
            'site home (index.php)' => ['https://example.com/index.php', 'fa-house'],
            'relative dashboard url' => ['/my/', 'fa-gauge-high'],
            'unmapped destination' => ['https://example.com/mod/forum/view.php?id=3', ''],
            'empty url' => ['', ''],
        ];
    }
}
