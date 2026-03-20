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

namespace theme_boost_union\local;

/**
 * Theme Boost Union - Moodle Workplace Extension Library.
 *
 * @package    theme_boost_union
 * @copyright  2026 Alexander Bias <bias@alexanderbias.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mwp {
    /**
     * Helper function to check if we are currently on Moodle Workplace and if the Boost Union for Workplace extension is present.
     * If yes, the Boost Union for Workplace extension is required and can be used.
     *
     * @return bool True if we are on Moodle Workplace and the Moodle Workplace extension is present, false otherwise.
     */
    public static function extension_present(): bool {
        global $CFG;

        // Use static variable to cache the result of this function,
        // as it may be called multiple times during a request.
        static $result = null;
        if ($result !== null) {
            return $result;
        }

        // If tool_tenant is not present, we are not on Workplace.
        if (!file_exists($CFG->dirroot . '/admin/tool/tenant/version.php')) {
            $result = false;
            return $result;
        }

        // If local_boost_union_mwp is present, we can enable the Workplace extensions.
        if (file_exists($CFG->dirroot . '/local/boost_union_mwp/version.php')) {
            // And inform the caller.
            $result = true;
            return $result;

            // Otherwise.
        } else {
            $result = false;
            return $result;
        }
    }
}
