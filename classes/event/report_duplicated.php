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

namespace block_configurable_reports\event;

defined('MOODLE_INTERNAL') || die();

use core\event\base;
use moodle_url;

/**
 *
 *
 * @package
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_duplicated extends base {
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'block_configurable_reports';
    }

    public static function get_name(): string {
        return get_string('event:reportduplicated', 'block_configurable_reports');
    }

    public function get_description(): string {
        $sourceid = $this->other['sourceid'] ?? 0;
        $sourcename = $this->other['sourcename'] ?? '';
        return "User with id '{$this->userid}' duplicated report '{$sourcename}' (id {$sourceid}) to '{$this->other['reportname']}' (id {$this->objectid}).";
    }

    public function get_url(): moodle_url {
        return new moodle_url('/blocks/configurable_reports/editreport.php', ['id' => $this->objectid]);
    }
}

