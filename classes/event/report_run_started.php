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

use coding_exception;
use context;
use core\event\base;
use moodle_url;
use stdClass;

/**
 * Event triggered before a configurable report is run.
 *
 * This is logged before the report query executes, so that a report which
 * crashes the site while running (for example an excessively large report)
 * remains traceable even though it never finishes and shows its output.
 *
 * @package    block_configurable_reports
 * @copyright  2026 Gold Coast Health
 * @author     Yucheng Zhu
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_run_started extends base {
    /**
     * Initialises the event metadata for a report run start event.
     *
     * @return void Sets the CRUD action, education level, and source table for the event.
     */
    protected function init(): void {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = static::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'block_configurable_reports';
    }

    /**
     * Creates a report run started event from a report record.
     *
     * @param context $context The context where the report is being run.
     * @param stdClass $report The report record containing the report ID and name.
     * @return static The created report run started event instance.
     */
    public static function create_from_report(context $context, stdClass $report): base {
        return static::create([
            'context' => $context,
            'objectid' => $report->id,
            'other' => [
                'reportname' => format_string($report->name),
            ],
        ]);
    }

    /**
     * Gets the localised name of the event.
     *
     * @return string The localised event name.
     */
    public static function get_name(): string {
        return get_string('event:reportrunstarted', 'block_configurable_reports');
    }

    /**
     * Gets the localised description of the started report run event.
     *
     * @return string The localised event description.
     */
    public function get_description(): string {
        $data = new stdClass();
        $data->userid = $this->userid;
        $data->reportname = $this->other['reportname'];
        $data->objectid = $this->objectid;
        return get_string('event:reportrunstarted:desc', 'block_configurable_reports', $data);
    }

    /**
     * Validates that the event contains the required report data.
     *
     * @return void Validates the event data before the event is triggered.
     * @throws coding_exception If the report ID or report name is missing from the event data.
     */
    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->objectid)) {
            throw new coding_exception('The \'objectid\' must be set.');
        }
        if (!isset($this->other['reportname'])) {
            throw new coding_exception('The \'reportname\' must be set in other.');
        }
    }

    /**
     * Gets the report URL related to the event.
     *
     * @return moodle_url The URL of the report view page for this event.
     */
    public function get_url(): moodle_url {
        return new moodle_url('/blocks/configurable_reports/viewreport.php', ['id' => $this->objectid]);
    }
}
