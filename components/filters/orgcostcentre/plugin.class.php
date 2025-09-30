<?php

use block_configurable_reports\local\plugin\org_filter_base;

class plugin_orgcostcentre extends org_filter_base {

    public function init(): void {
        parent::init();

        $this->reporttypes = [ 'sql', 'users' ];
    }

    protected static function get_name(): string {
        return 'orgcostcentre';
    }

    public function print_filter(MoodleQuickForm $mform, $formdata = false): void {
        self::add_level_selector($mform, 'costcentre');
    }
}
