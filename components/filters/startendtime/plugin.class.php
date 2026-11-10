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
 * Configurable Reports a Moodle block for creating customizable reports
 *
 * @copyright  2020 Juan Leyva <juan@moodle.com>
 * @package    block_configurable_reports
 * @author     Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/configurable_reports/filter.class.php');

/**
 * Class plugin_startendtime
 *
 * @package   block_configurable_reports
 * @author    Juan leyva <http://www.twitter.com/jleyvadelgado>
 */
class plugin_startendtime extends filter_base {

    /**
     * Init
     *
     * @return void
     */
    public function init(): void {
        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('startendtime', 'block_configurable_reports');
        $this->reporttypes = ['sql', 'timeline', 'users', 'courses'];
    }

    /**
     * Summary
     *
     * @param object $data
     * @return string
     */
    public function summary(object $data): string {
        return get_string('filterstartendtime_summary', 'block_configurable_reports');
    }

    /**
     * Execute
     *
     * @param string $finalelements
     * @return array|string|string[]
     */
    public function execute($finalelements) {
        return $finalelements;
    }

    #[\Override]
    function execute_for_sql_report(string $sql, ?\stdClass $data = null): array {
        global $CFG;

        if ($CFG->version < 2011120100) {
            $filterstarttime = optional_param('filter_starttime', 0, PARAM_RAW);
            $filterendtime = optional_param('filter_endtime', 0, PARAM_RAW);
        } else {
            $filterstarttime = optional_param_array('filter_starttime', 0, PARAM_RAW);
            $filterendtime = optional_param_array('filter_endtime', 0, PARAM_RAW);
        }

        if (!$filterstarttime || !$filterendtime) {
            return [$sql, []];
        }

        $filterstarttime = make_timestamp(
            $filterstarttime['year'],
            $filterstarttime['month'],
            $filterstarttime['day'],
            $filterstarttime['hour'],
            $filterstarttime['minute']
        );
        $filterendtime = make_timestamp(
            $filterendtime['year'],
            $filterendtime['month'],
            $filterendtime['day'],
            $filterendtime['hour'],
            $filterendtime['minute']
        );

        $operators = ['<', '>', '<=', '>='];

        $params = [];

        // Every placeholder gets its own parameter, so a query can use each one more than once.
        $placeholders = ['STARTTIME' => $filterstarttime, 'ENDTIME' => $filterendtime];
        foreach ($placeholders as $name => $value) {
            $count = 0;
            $sql = preg_replace_callback(
                '/%%FILTER_' . $name . ':([^%]+)%%/i',
                function (array $output) use ($name, $value, $operators, &$params, &$count): string {
                    [$field, $operator] = array_pad(preg_split('/:/', $output[1]), 2, '');
                    if (!in_array($operator, $operators)) {
                        throw new moodle_exception('nosuchoperator');
                    }
                    // The first one keeps the old name, in case anything relies on it.
                    $paramname = 'filter' . strtolower($name) . ($count ? $count : '');
                    $count++;
                    $params[$paramname] = $value;
                    return ' AND ' . $field . ' ' . $operator . ' :' . $paramname;
                },
                $sql
            );
        }

        $sql = str_replace('%%STARTTIME%%', $filterstarttime, $sql);
        $sql = str_replace('%%ENDTIME%%', $filterendtime, $sql);

        return [$sql, $params];
    }

    /**
     * Print filter
     *
     * @param MoodleQuickForm $mform
     * @param bool|object $formdata
     * @return void
     */
    public function print_filter(MoodleQuickForm $mform, $formdata = false): void {

        $mform->addElement('date_time_selector', 'filter_starttime', get_string('starttime', 'block_configurable_reports'));
        $mform->setDefault('filter_starttime', time() - 3600 * 24);
        $mform->addElement('date_time_selector', 'filter_endtime', get_string('endtime', 'block_configurable_reports'));
        $mform->setDefault('filter_endtime', time() + 3600 * 24);
    }

}
