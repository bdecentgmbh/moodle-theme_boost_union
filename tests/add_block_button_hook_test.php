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
 * Theme Boost Union - Tests for the hook replacing the "Add a block" button.
 *
 * @package    theme_boost_union
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_union;

use theme_boost_union\hook\output\add_block_button;

/**
 * Theme Boost Union - Tests for the hook replacing the "Add a block" button.
 *
 * @package    theme_boost_union
 * @copyright  2026 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_boost_union\output\core_renderer::addblockbutton
 * @covers     \theme_boost_union\hook\output\add_block_button
 */
final class add_block_button_hook_test extends \advanced_testcase {
    /**
     * Boost Union's renderer for a course page, as an admin who may edit blocks.
     *
     * @param bool $editing Whether editing mode is on.
     * @return \theme_boost_union\output\core_renderer
     */
    private function get_renderer(bool $editing): \theme_boost_union\output\core_renderer {
        global $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $USER->editing = $editing ? 1 : 0;

        $course = $this->getDataGenerator()->create_course();
        $page = new \moodle_page();
        $page->set_course($course);
        $page->set_pagetype('course-view-topics');
        $page->set_pagelayout('course');
        $page->set_url(new \moodle_url('/course/view.php', ['id' => $course->id]));
        $page->force_theme('boost_union');

        return new \theme_boost_union\output\core_renderer($page, RENDERER_TARGET_GENERAL);
    }

    /**
     * Without a callback changing it, the button is Moodle core's.
     */
    public function test_button_unchanged_without_callback(): void {
        $renderer = $this->get_renderer(true);
        $given = null;
        $this->redirectHook(add_block_button::class, function (add_block_button $hook) use (&$given) {
            $given = $hook->get_html();
        });

        $html = $renderer->addblockbutton('side-pre');

        $this->assertStringContainsString('data-key="addblock"', $html);
        $this->assertSame($given, $html);
    }

    /**
     * Plugins can replace the button, and learn the region it is for.
     */
    public function test_callback_replaces_button(): void {
        $renderer = $this->get_renderer(true);
        $this->redirectHook(add_block_button::class, function (add_block_button $hook) {
            $hook->set_html('<button>' . $hook->region . '</button>');
        });

        $this->assertSame('<button>side-pre</button>', $renderer->addblockbutton('side-pre'));
    }

    /**
     * Without the button, as outside editing mode, the hook is not dispatched.
     */
    public function test_no_dispatch_without_button(): void {
        $renderer = $this->get_renderer(false);
        $called = false;
        $this->redirectHook(add_block_button::class, function () use (&$called) {
            $called = true;
        });

        $this->assertSame('', $renderer->addblockbutton('side-pre'));
        $this->assertFalse($called);
    }
}
