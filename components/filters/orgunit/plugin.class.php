<?php

global $CFG;

use tool_organisation\persistent\hierarchy;
use tool_organisation\persistent\level;

require_once($CFG->dirroot.'/blocks/configurable_reports/plugin.class.php');

/** @noinspection PhpUnused */
class plugin_orgunit extends plugin_base {

    /**
     * Initialise filter.
     *
     * @return void
     * @throws coding_exception
     */
    public function init(): void {
        $this->form = false;
        $this->unique = true;
        $this->fullname = get_string('filterorgunit', 'block_configurable_reports');
        $this->reporttypes = [ 'sql', 'users' ];
    }

    /**
     * Get summary of the filter.
     *
     * @param stdClass $data Filter data.
     * @return string Summary of the filter.
     * @throws coding_exception
     */
    public function summary($data): string {
        return get_string('filterorgunit_summary', 'block_configurable_reports');
    }

    /**
     * Execute the filter.
     *
     * @param array|string $final_elements Content to be filtered.
     * @param stdClass $data Filter data
     * @return array|string Filtered content.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function execute($final_elements, stdClass $data) {
        $filter_value = self::get_form_value();
        if (empty($filter_value)) {
            return $final_elements;
        }

        if ($this->report->type === 'sql') {
            return self::execute_sql($final_elements, $data, $filter_value);
        }

        return self::execute_users($final_elements, $data, $filter_value);
    }

    /**
     * Output filter fields to report form.
     *
     * @param MoodleQuickForm $mform Report form.
     * @param stdClass $data Filter data.
     * @return void
     * @throws coding_exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function print_filter(MoodleQuickForm $mform, stdClass $data): void {
        $hierarchy = hierarchy::get_record([ 'idnumber' => 'unit' ]);
        if (!$hierarchy) {
            $mform->addElement('static', 'filter_orgunit', '', get_string('filterorgunit_nohierarchy', 'block_configurable_reports'));

            return;
        }

        $levels = level::get_records(
            [ 'hierarchyid' => $hierarchy->get('id') ],
            'name'
        );
        if (empty($levels)) {
            $mform->addElement('static', 'filter_orgunit', '', get_string('filterorgunit_nolevels', 'block_configurable_reports'));

            return;
        }

        $level_options = [];
        foreach ($levels as $level) {
            $level_options[$level->get('id')] = $level->get('name') . ' (' . $level->get('idnumber') . ')';
        }

        $mform->addElement(
            'autocomplete',
            'filter_orgunit',
            get_string('filterorgunit', 'block_configurable_reports'),
            $level_options,
            [ 'multiple' => true ]
        );
    }

    /**
     * Get submitted filter form values.
     *
     * @return int[] Filter form values.
     * @throws coding_exception
     */
    private static function get_form_value(): array {
        // Workaround to ignore empty autocomplete field since we don't have form data.
        $raw_value = $_POST['filter_orgunit'] ?? $_GET['filter_orgunit'] ?? null;
        if (
            !$raw_value ||
            $raw_value === '_qf__force_multiselect_submission'
        ) {
            return [];
        }

        return optional_param_array('filter_orgunit', [], PARAM_INT);
    }

    /**
     * Execute filter for SQL reports.
     *
     * @param string $final_elements Content to be filtered.
     * @param stdClass $data Filter data.
     * @param int[] $filter_value Filter values.
     * @return string Filtered content.
     * @noinspection PhpUnusedParameterInspection
     */
    private static function execute_sql(string $final_elements, stdClass $data, array $filter_value): string {
        preg_match("/%%FILTER_ORGUNIT:([^%]+)%%/i", $final_elements, $matches);
        if ($matches) {
            $values = implode(',', $filter_value);
            $replace = " AND $matches[1] IN ($values)";

            return str_replace(
                "%%FILTER_ORGUNIT:$matches[1]%%",
                $replace,
                $final_elements
            );
        }

        return $final_elements;
    }

    /**
     * Execute filter for user reports.
     *
     * @param int[] $final_elements Content to be filtered.
     * @param stdClass $data Filter data.
     * @param int[] $filter_value Filter values.
     * @return int[] Filtered content.
     * @throws coding_exception
     * @throws dml_exception
     * @noinspection PhpUnusedParameterInspection
     */
    private static function execute_users(array $final_elements, stdClass $data, array $filter_value): array {
        global $DB;

        [ $user_sql, $user_params ] = $DB->get_in_or_equal($final_elements);
        [ $level_sql, $level_params ] = $DB->get_in_or_equal($filter_value);

        return $DB->get_fieldset_sql(
            "
                select  users.id
                from    {user} users
                        join {tool_organisation_assign} assignments on
                            assignments.userid = users.id
                        join {tool_organisation_positions} positions on
                            positions.id = assignments.positionid
                        join {tool_organisation_level_data} level_data on
                            level_data.positionid = positions.id or
                            level_data.assignid = assignments.id
                where   users.id $user_sql and
                        level_data.levelid $level_sql
            ",
            array_merge(
                $user_params,
                $level_params,
            )
        );
    }
}
