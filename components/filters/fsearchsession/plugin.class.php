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

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/blocks/configurable_reports/plugin.class.php');

/**
 * Class plugin_fsearchsession
 *
 * @package    block_configurable_reports
 * @copyright  2025 Gold Coast Health
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_fsearchsession extends plugin_base {

    /**
     * Init
     *
     * @return void
     */
	public function init(): void {
		$this->form = true;
		$this->unique = true;
		$this->fullname = get_string('fsearchsession', 'block_configurable_reports');
        $this->reporttypes = ['users', 'sql'];
	}

    /**
     * Summary
     *
     * @param object $data
     * @return string
     */
	public function summary(object $data): string {
		return $data->field;
	}

    /**
     * Execute
     *
     * @param string $finalelements
     * @param object $data
     * @return array|int[]|mixed|string|string[]
     */
    public function execute($finalelements, $data) {
        if ($this->report->type == 'sql') {
            return $this->execute_sql($finalelements, $data);
        }

        return $this->execute_users($finalelements, $data);
    }

    /**
     * execute_sql
     *
     * @param string $finalelements
     * @param object $data
     * @return array|string|string[]
     */
	private function execute_sql($finalelements, $data) {
		$filterfsession = optional_param('filter_fsession_'.$data->field, 0, PARAM_TEXT);
        $filtermatch = preg_match("/%%FILTER_SESSION:([^%]+)%%/i", $finalelements, $output);

		if ($filterfsession && $filtermatch) {
			$replace = ' AND '.$output[1].' LIKE '. "'$filterfsession'";
            $finalelements = str_replace('%%FILTER_SESSION:'.$output[1].'%%', $replace, $finalelements);
		}

		return $finalelements;
	}

    /**
     * execute_users
     *
     * @param string $finalelements
     * @param object $data
     * @return int[]|mixed|string[]
     */
	private function execute_users($finalelements, $data) {
        global $remotedb;
		
		$filterfsession = optional_param('filter_fsession_'.$data->field, 0, PARAM_TEXT);
		if($filterfsession){
			$filter = $filterfsession;

            [$usql, $params] = $remotedb->get_in_or_equal($finalelements);
            $sql = "$data->field LIKE ? AND id $usql";
            $params = array_merge(["%$filter%"], $params);
            $elements = $remotedb->get_records_select('facetoface_signups', $sql, $params);
            $finalelements = array_keys($elements);
		}

		return $finalelements;
	}

    /**
     * Print filter
     *
     * @param MoodleQuickForm $mform
     * @param bool|object $formdata
     * @return void
     */
	public function print_filter(MoodleQuickForm $mform, $formdata = false): void {
        global $remotedb;
		
		$columns = $remotedb->get_columns('facetoface_signups');
		
		$usercolumns = array();
		foreach($columns as $c)
			$usercolumns[$c->name] = $c->name;

        if ($profile = $remotedb->get_records('user_info_field')) {
            foreach ($profile as $p) {
                $usercolumns['profile_' . $p->shortname] = $p->name;
            }
        }

        if (!isset($usercolumns[$formdata->field])) {
            throw new moodle_exception('nosuchcolumn');
        }

        $reportclassname = 'report_' . $this->report->type;
        $reportclass = new $reportclassname($this->report);

        if ($this->report->type == 'sql') {
            $userlist = array_keys($remotedb->get_records('user'));
        } else {
            $components = cr_unserialize($this->report->components);
            $conditions = array_key_exists('conditions', $components) ?
                $components['conditions'] :
                null;
            $userlist = $reportclass->elements_by_conditions($conditions);
        }

        $selectname = get_string('fsearchsession', 'block_configurable_reports');

        $mform->addElement('text', 'filter_fsession_' . $formdata->field, $selectname, ['size' => 20]);
        $mform->setType('filter_fsession_' . $formdata->field, PARAM_INT);
    }
}
