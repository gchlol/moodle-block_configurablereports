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

require_once($CFG->libdir . '/formslib.php');

/**
 * Class fsearchsession_form
 *
 * @package    block_configurable_reports
 * @copyright  2025 Gold Coast Health
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fsearchsession_form extends moodleform {

    /**
     * Form definition
     */
    public function definition(): void {
        global $remotedb;

        $mform =& $this->_form;

        $mform->addElement('header', '', get_string('fsearchsession', 'block_configurable_reports'), '');

		$this->_customdata['compclass']->add_form_elements($mform, $this);
		
		$columns = $remotedb->get_columns('facetoface_signups');

        $usercolumns = [];
        foreach ($columns as $c) {
            $usercolumns[$c->name] = $c->name;
        }

        $mform->addElement('select', 'field', get_string('field', 'block_configurable_reports'), $usercolumns);

        // Buttons.
        $this->add_action_buttons(true, get_string('add'));
    }
}
