<?php

use block_configurable_reports\local\plugin\org_filter_base;
use tool_organisation\persistent\level;

/** @noinspection PhpUnused */
class plugin_orgdivision extends org_filter_base {

    /**
     * @inheritDoc
     */
    public function init(): void {
        parent::init();

        $this->reporttypes = [ 'sql', 'users' ];
    }

    /**
     * @inheritDoc
     */
    protected static function get_name(): string {
        return 'orgdivision';
    }

    /**
     * @inheritDoc
     */
    public function print_filter(MoodleQuickForm $mform, stdClass $data): void {
        self::add_level_selector($mform, 'unit', null, true);
    }

    /**
     * @inheritDoc
     */
    protected static function get_levels_for_selector(int $hierarchy_id, ?int $depth): array {
        global $DB;

        $qh_unit = level::get_record([ 'idnumber' => '70071658' ]);
        $reporting_parents = explode(",", get_config('local_lol', 'repunitparents'));

        $parent_ids = $reporting_parents;
        $parent_ids[] = $qh_unit->get('id');
        if (empty($parent_ids)) {
            return [];
        }

        [ $parent_sql, $params ] = $DB->get_in_or_equal($parent_ids, SQL_PARAMS_NAMED);
        $params['hierarchy'] = $hierarchy_id;

        return level::get_records_select(
            "
                hierarchyid = :hierarchy and
                parent $parent_sql
            ",
            $params,
            'name'
        );
    }
}