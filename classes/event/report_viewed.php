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
use core\context;
use core\event\base;
use moodle_url;
use stdClass;

/**
 * Event triggered when configurable report is viewed.
 *
 * @package     block_configurable_reports
 * @copyright   2025 Gold Coast Health
 * @author      Jonas Sajonas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed extends base {
    /**
     * Init event data.
     *
     * @return void
     */
    protected function init(): void {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'block_configurable_reports';
    }

    /**
     * Build event from a report record.
     *
     * @param context $context
     * @param stdClass $report
     * @param string $source UI or API source
     * @return \core\event\base
     */
    public static function create_from_report(context $context, stdClass $report, string $source = 'ui'): self {
        $event = self::create([
            'context' => $context,
            'objectid' => $report->id,
            'other' => [
                'reportname' => format_string($report->name),
                'source' => $source,
            ],
        ]);
        $event->add_record_snapshot('block_configurable_reports', $report);
        return $event;
    }

    /**
     * Returns localized event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:reportviewed', 'block_configurable_reports');
    }

    /**
     * Describes event.
     *
     * @return string
     */
    public function get_description(): string {
        $data = new stdClass();
        $data->userid = $this->userid;
        $data->reportname = $this->other['reportname'];
        $data->objectid = $this->objectid;
        return get_string('event:reportviewed:desc', 'block_configurable_reports', $data);
    }

    /**
     * Validate required data.
     *
     * @return void
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
     * Returns URL related to event context.
     *
     * @return moodle_url
     */
    public function get_url(): moodle_url {
        return new moodle_url('/blocks/configurable_reports/viewreport.php', ['id' => $this->objectid]);
    }
}
