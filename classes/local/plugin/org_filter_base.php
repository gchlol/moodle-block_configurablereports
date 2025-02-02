<?php

namespace block_configurable_reports\local\plugin;

global $CFG;

use coding_exception;
use dml_exception;
use MoodleQuickForm;
use plugin_base;
use stdClass;
use tool_organisation\persistent\hierarchy;
use tool_organisation\persistent\level;

require_once("$CFG->dirroot/blocks/configurable_reports/plugin.class.php");

abstract class org_filter_base extends plugin_base {

    /**
     * Get the condensed name of the filter.
     * Used for language strings, identifier, etc.
     *
     * @return string Name of the filter.
     */
    protected abstract static function get_name(): string;

    /**
     * Output filter fields to report form.
     *
     * @param MoodleQuickForm $mform Report form.
     * @param stdClass $data Filter data.
     * @return void
     * @throws coding_exception
     * @noinspection PhpUnusedParameterInspection
     */
    public abstract function print_filter(MoodleQuickForm $mform, stdClass $data): void;

    /**
     * Initialise filter.
     *
     * @return void
     * @throws coding_exception
     */
    public function init(): void {
        $this->unique = true;
        $this->fullname = static::get_string('name');
    }

    /**
     * Get summary of the filter.
     *
     * @param stdClass $data Filter data.
     * @return string Summary of the filter.
     * @throws coding_exception
     */
    public function summary($data): string {
        return static::get_string('summary');
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
        $filter_value = self::get_filter_value();
        if (empty($filter_value)) {
            return $final_elements;
        }

        if ($this->report->type === 'sql') {
            return static::execute_sql($final_elements, $data, $filter_value);
        }

        return static::execute_users($final_elements, $data, $filter_value);
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
    protected static function execute_sql(string $final_elements, stdClass $data, array $filter_value): string {
        $match_name = strtoupper(static::get_name());

        preg_match("/%%FILTER_$match_name:([^%]+)%%/i", $final_elements, $matches);
        if (!$matches) {
            return $final_elements;
        }

        $values = implode(',', $filter_value);
        $replace = " and $matches[1] in ($values)";

        return str_replace(
            "%%FILTER_$match_name:$matches[1]%%",
            $replace,
            $final_elements
        );
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
    protected static function execute_users(array $final_elements, stdClass $data, array $filter_value): array {
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

    /**
     * Get the identifier for the filter.
     *
     * @return string Filter identifier.
     */
    protected static function get_identifier(): string {
        return 'filter' . static::get_name();
    }

    /**
     * Get a language string for the filter.
     *
     * @param string $identifier Sub-identifier for the language string. E.g. 'name', 'summary', etc.
     * @return string Language string.
     * @throws coding_exception
     */
    protected static function get_string(string $identifier): string {
        return get_string(
            static::get_identifier() . "_$identifier",
            'block_configurable_reports'
        );
    }

    /**
     * Get the filter value from submitted data.
     *
     * @return int[] Filter level IDs.
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function get_filter_value(): array {
        global $DB;

        $form_value = self::get_form_value();
        if (empty($form_value)) {
            return [];
        }

        $include_children = optional_param(static::get_identifier() . '_include_children', false, PARAM_BOOL);
        if (!$include_children) {
            return $form_value;
        }

        [ $levels_sql, $levels_params ] = $DB->get_in_or_equal($form_value);
        $levels = level::get_records_select(
            "id $levels_sql",
            $levels_params
        );

        $level_ids = [];
        foreach ($levels as $level) {
            $level_ids[] = $level->get('id');

            $child_levels = $level->get_children();
            foreach ($child_levels as $child_level) {
                $level_ids[] = $child_level->get('id');
            }
        }

        return $level_ids;
    }

    /**
     * Get submitted filter form values.
     *
     * @return int[] Filter form values.
     * @throws coding_exception
     */
    protected static function get_form_value(): array {
        // Workaround to ignore empty autocomplete field since we don't have form data.
        $raw_value = $_POST[static::get_identifier()] ?? $_GET[static::get_identifier()] ?? null;
        if (
            !$raw_value ||
            $raw_value === '_qf__force_multiselect_submission'
        ) {
            return [];
        }

        return optional_param_array(static::get_identifier(), [], PARAM_INT);
    }

    /**
     * Add a general hierarchy level selector to a given form.
     *
     * @param MoodleQuickForm $mform Form to add the selector to.
     * @param string $hierarchy_idnumber Target hierarchy ID number.
     * @param int|null $depth Fixed level depth to fetch from. If null, all levels are fetched.
     * @param bool|null $include_children Optional override for including child levels. If null, option is added to the form.
     * @return void
     * @throws coding_exception
     */
    protected static function add_level_selector(MoodleQuickForm $mform, string $hierarchy_idnumber, ?int $depth = null, ?bool $include_children = null): void {
        $hierarchy = hierarchy::get_record([ 'idnumber' => $hierarchy_idnumber ]);
        if (!$hierarchy) {
            $mform->addElement(
                'static',
                static::get_identifier(),
                static::get_string('name'),
                static::get_string('nohierarchy')
            );

            return;
        }

        $levels = static::get_levels_for_selector($hierarchy->get('id'), $depth);
        if (empty($levels)) {
            $mform->addElement(
                'static',
                static::get_identifier(),
                static::get_string('name'),
                static::get_string('nolevels')
            );

            return;
        }

        $level_options = [];
        foreach ($levels as $level) {
            $level_options[$level->get('id')] = $level->get('name') . ' (' . $level->get('idnumber') . ')';
        }

        $mform->addElement(
            'autocomplete',
            static::get_identifier(),
            static::get_string('name'),
            $level_options,
            [ 'multiple' => true ]
        );

        // Select options submitted externally to the page. e.g. Via GET params.
        $form_value = static::get_form_value();
        if (!empty($form_value)) {
            $mform->setDefault(static::get_identifier(), $form_value);
        }

        $include_children_identifier = static::get_identifier() . '_include_children';
        if ($include_children !== null) {
            $mform->addElement(
                'hidden',
                $include_children_identifier,
                $include_children
            );
            $mform->setType($include_children_identifier, PARAM_BOOL);

            return;
        }

        $mform->addElement(
            'advcheckbox',
            $include_children_identifier,
            '',
            static::get_string('includechildren')
        );
    }

    /**
     * Get levels to be output in the level selector.
     *
     * @param int $hierarchy_id Parent hierarchy ID.
     * @param int|null $depth Fixed level depth to fetch from. If null, all levels are fetched.
     * @return level[] List of levels.
     * @throws coding_exception
     */
    protected static function get_levels_for_selector(int $hierarchy_id, ?int $depth): array {
        if ($depth === null) {
            return level::get_records(
                [ 'hierarchyid' => $hierarchy_id ],
                'name'
            );
        }

        $root_level = level::get_record([
            'hierarchyid' => $hierarchy_id,
            'parent' => 0,
        ]);

        $depth_levels = [ $root_level ];
        for ($i = 0; $i < $depth; $i++) {
            $new_levels = [];
            foreach ($depth_levels as $level) {
                $new_levels = array_merge(
                    $new_levels,
                    level::get_records([
                        'hierarchyid' => $hierarchy_id,
                        'parent' => $level->get('id'),
                    ])
                );
            }

            $depth_levels = $new_levels;
        }

        return $depth_levels;
    }
}