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
use stdClass;

/**
 * Event triggered when a configurable report is imported.
 *
 * @package     block_configurable_reports
 * @copyright   2025 Gold Coast Health
 * @author      Jonas Sajonas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_imported extends base {
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'block_configurable_reports';
    }

    /**
     * Returns localized event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:reportimported', 'block_configurable_reports');
    }

    /**
     * Describes event in human-readable way.
     *
     * @return string
     */
    public function get_description(): string {
        $data = new stdClass();
        $data->userid = $this->userid;
        $data->reportname = $this->other['reportname'] ?? '';
        $data->objectid = $this->objectid;
        $data->source = $this->other['source'] ?? 'unknown';
        return get_string('event:reportimported:desc', 'block_configurable_reports', $data);
    }

    /**
     * Returns URL related to event context.
     *
     * @return moodle_url
     */
    public function get_url(): moodle_url {
        return new moodle_url('/blocks/configurable_reports/editreport.php', ['id' => $this->objectid]);
    }
}
