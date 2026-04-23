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

namespace block_configurable_reports\local\util;

use block_configurable_reports\event\report_updated;
use context;
use context_course;
use context_system;
use stdClass;

/**
 * Configurable Reports event utility class.
 *
 * @package     block_configurable_reports
 * @copyright   2025 Gold Coast Health
 * @author      Jonas Sajonas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class event_util {

    /**
     * Log visibility change as report update.
     *
     * @param context $context
     * @param stdClass $report
     * @param bool $visible
     * @return void
     */
    public static function log_report_visibility_change(context $context, stdClass $report, bool $visible): void {
        $changemsg = $visible
            ? get_string('event:changevisibilityshown', 'block_configurable_reports')
            : get_string('event:changevisibilityhidden', 'block_configurable_reports');
        $report->visible = $visible ? 1 : 0;
        report_updated::create_from_report($context, $report, $changemsg)->trigger();
    }

    /**
     * Build change summary for report updates.
     *
     * @param stdClass $original
     * @param stdClass $newdata
     * @return array [string $change, stdClass $updatedreport] — change is '' when nothing tracked changed
     */
    public static function build_report_change_summary(stdClass $original, stdClass $newdata): array {
        $changes = [];

        $fieldmap = [
            'name' => ['key' => 'event:changetitle', 'numeric' => false],
            'summary' => ['key' => 'event:changedescription', 'numeric' => false],
            'export' => ['key' => 'event:changeexport', 'numeric' => false],
            'jsordering' => ['key' => 'event:changejsorder', 'numeric' => true],
            'global' => ['key' => 'event:changescope', 'numeric' => true],
            'cron' => ['key' => 'event:changecron', 'numeric' => true],
            'displaytotalrecords' => ['key' => 'event:changetotalrecords', 'numeric' => true],
            'displayprintbutton' => ['key' => 'event:changeprintbutton', 'numeric' => true],
        ];

        foreach ($fieldmap as $field => $meta) {
            if (!isset($newdata->$field)) {
                continue;
            }
            $oldvalue = $original->$field;
            $newvalue = $newdata->$field;
            if ($meta['numeric']) {
                $haschanged = (intval($newvalue) !== intval($oldvalue));
            } else {
                $haschanged = ($newvalue !== $oldvalue);
            }

            if ($haschanged) {
                $changes[] = get_string($meta['key'], 'block_configurable_reports');
            }
        }

        $updatedreport = clone $original;
        foreach (array_keys($fieldmap) as $field) {
            if (isset($newdata->$field)) {
                $updatedreport->$field = $newdata->$field;
            }
        }

        return [implode(', ', $changes), $updatedreport];
    }

    /**
     * Log SQL change event for custom SQL components.
     *
     * @param stdClass $reportconfig
     * @return void
     */
    public static function log_sql_change(stdClass $reportconfig): void {
        $context = ($reportconfig->courseid == SITEID)
            ? context_system::instance()
            : context_course::instance($reportconfig->courseid);
        $change = get_string('event:changesqlquery', 'block_configurable_reports');
        report_updated::create_from_report($context, $reportconfig, $change)->trigger();
    }

    /**
     * Log tab-level change event (Filters, Permissions).
     *
     * @param stdClass $report
     * @param string $comp Component/tab name
     * @return void
     */
    public static function log_tab_change(stdClass $report, string $comp): void {
        $context = ($report->courseid == SITEID)
            ? context_system::instance()
            : context_course::instance($report->courseid);
        report_updated::create_from_report($context, $report, $comp)->trigger();
    }
}
