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

function cr_log_report_viewed($context, stdClass $report, string $source = 'ui'): void {
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_viewed::create([
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
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_exported::create([
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
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_created::create([
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

function cr_log_report_updated($context, stdClass $report, string $change, string $source = 'ui', ?stdClass $snapshot = null): void {
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_updated::create([
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
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_deleted::create([
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
    if (empty($report) || empty($report->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_imported::create([
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

function cr_log_report_duplicated($context, stdClass $newreport, stdClass $sourcereport, string $source = 'ui'): void {
    if (empty($newreport) || empty($newreport->id)) {
        return;
    }

    $event = \block_configurable_reports\event\report_duplicated::create([
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
