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
 * Helper functions for Configurable Reports events.
 *
 * @package     block_configurable_reports
 * @copyright   2025 Gold Coast Health
 * @author      Jonas Sajonas
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_configurable_reports\event\report_viewed;
use block_configurable_reports\event\report_exported;
use block_configurable_reports\event\report_created;
use block_configurable_reports\event\report_updated;
use block_configurable_reports\event\report_deleted;
use block_configurable_reports\event\report_imported;
use block_configurable_reports\event\report_duplicated;

function cr_log_report_viewed($context, stdClass $report, string $source = 'ui'): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_viewed::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $report);
    $event->trigger();
}

function cr_log_report_exported($context, stdClass $report, string $format, string $source = 'ui'): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_exported::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'format' => $format,
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $report);
    $event->trigger();
}

function cr_log_report_created($context, stdClass $report, string $source = 'ui'): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_created::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $report);
    $event->trigger();
}

/**
 * Convenience wrapper to log creation when only new id + data are available.
 *
 * @param context $context
 * @param int $reportid
 * @param stdClass $data
 */
function cr_log_report_created_from_data($context, int $reportid, stdClass $data): void {
    $report = clone $data;
    $report->id = $reportid;
    cr_log_report_created($context, $report);
}

function cr_log_report_updated($context, stdClass $report, string $change, string $source = 'ui', ?stdClass $snapshot = null
    ): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_updated::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'change' => $change,
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $snapshot ?? $report);
    $event->trigger();
}

function cr_log_report_deleted($context, stdClass $report, string $source = 'ui'): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_deleted::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $report);
    $event->trigger();
}

function cr_log_report_imported($context, stdClass $report, string $source): void {
    if (
        empty($report) ||
        empty($report->id)
    ) {
        return;
    }

    $event = report_imported::create([
        'context' => $context,
        'objectid' => $report->id,
        'other' => [
            'reportname' => format_string($report->name ?? ''),
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $report);
    $event->trigger();
}

/**
 * Convenience wrapper to log import when only id is available.
 *
 * @param context $context
 * @param int $reportid
 * @param string $source
 */
function cr_log_report_imported_by_id($context, int $reportid, string $source): void {
    global $DB;

    if ($newreport = $DB->get_record('block_configurable_reports', ['id' => $reportid])) {
        cr_log_report_imported($context, $newreport, $source);
    }
}

function cr_log_report_duplicated($context, stdClass $newreport, stdClass $sourcereport, string $source = 'ui'): void {
    if (
        empty($newreport) ||
        empty($newreport->id)
    ) {
        return;
    }

    $event = report_duplicated::create([
        'context' => $context,
        'objectid' => $newreport->id,
        'other' => [
            'reportname' => format_string($newreport->name ?? ''),
            'sourcename' => format_string($sourcereport->name ?? ''),
            'sourceid' => $sourcereport->id ?? 0,
            'source' => $source,
        ],
    ]);
    $event->add_record_snapshot('block_configurable_reports', $newreport);
    $event->trigger();
}

/**
 * Helper to log duplication using ids.
 *
 * @param context $context
 * @param int $newreportid
 * @param stdClass $newreportdata (without id)
 * @param stdClass $sourcereport
 */
function cr_log_report_duplicated_from_ids($context, int $newreportid, stdClass $newreportdata, stdClass $sourcereport): void {
    $newreport = clone $newreportdata;
    $newreport->id = $newreportid;
    cr_log_report_duplicated($context, $newreport, $sourcereport);
}

/**
 * Prepare a duplicate report object for insertion.
 *
 * @param stdClass $report
 * @return stdClass
 */
function cr_prepare_report_duplicate(stdClass $report): stdClass {
    $duplicate = clone $report;
    unset($duplicate->id);

    return $duplicate;
}

/**
 * Log visibility change as a report update.
 *
 * @param context $context
 * @param stdClass $report
 * @param bool $visible
 */
function cr_log_report_visibility_change($context, stdClass $report, bool $visible): void {
    $updatedreport = clone $report;
    $updatedreport->visible = $visible ? 1 : 0;
    $changemsg = $visible
        ? get_string('event:changevisibilityshown', 'block_configurable_reports')
        : get_string('event:changevisibilityhidden', 'block_configurable_reports');
    cr_log_report_updated($context, $updatedreport, $changemsg, 'ui', $updatedreport);
}

/**
 * Build a change summary for report updates.
 *
 * @param stdClass $original
 * @param stdClass $newdata
 * @return array [string $change, stdClass $updatedreport]
 */
function cr_build_report_change_summary(stdClass $original, stdClass $newdata): array {
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
        if (!isset($newdata->$field) || !property_exists($original, $field)) {
            continue;
        }
        $oldvalue = $original->$field;
        $newvalue = $newdata->$field;
        if ($meta['numeric']) {
            $haschanged = ((int) $newvalue !== (int) $oldvalue);

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

    $change = !empty($changes) ? implode(', ', $changes) : get_string('event:changegeneric', 'block_configurable_reports');

    return [$change, $updatedreport];
}

/**
 * Log a component change message.
 *
 * @param stdClass $report
 * @param string $message
 * @param mixed $param
 */
function cr_log_component_change(stdClass $report, string $messagekey, $param = null): void {
    $context = ($report->courseid == SITEID)
        ? context_system::instance()
        : context_course::instance($report->courseid);
    $change = ($param === null)
        ? get_string($messagekey, 'block_configurable_reports')
        : get_string($messagekey, 'block_configurable_reports', $param);
    cr_log_report_updated($context, $report, $change);
}

/**
 * Log component reorder or delete events.
 *
 * @param stdClass $report
 * @param string $componentname
 * @param string $pluginname
 * @param bool $delete
 */
function cr_log_component_reorder_or_delete(stdClass $report, string $componentname, string $pluginname, bool $delete): void {
    $messagekey = $delete ? 'event:changecomponentdeleted' : 'event:changecomponentreordered';
    $param = $delete ? $pluginname : $componentname;
    cr_log_component_change($report, $messagekey, $param);
}

/**
 * Log SQL change event for custom SQL components.
 *
 * @param stdClass $reportconfig
 */
function cr_log_sql_change(stdClass $reportconfig): void {
    $context = ($reportconfig->courseid == SITEID)
        ? context_system::instance()
        : context_course::instance($reportconfig->courseid);
    cr_log_report_updated($context, $reportconfig, get_string('event:changesqlquery', 'block_configurable_reports'));
}

/**
 * Compare old and new SQL config and log when changed.
 *
 * @param stdClass $reportconfig
 * @param stdClass $data
 * @param array $components
 */
function cr_log_sql_change_if_needed(stdClass $reportconfig, stdClass $data, array $components): void {
    $oldsql = $components['customsql']['config']->querysql ?? '';
    if (
        isset($data->querysql) &&
        $data->querysql !== $oldsql
    ) {
        cr_log_sql_change($reportconfig);
    }
}
