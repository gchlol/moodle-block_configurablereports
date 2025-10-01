<?php

use block_configurable_reports\local\plugin\org_filter_base;

class plugin_orgunit extends org_filter_base {

    public function init(): void {
        parent::init();

        $this->reporttypes = [ 'sql', 'users' ];
    }

    protected static function get_name(): string {
        return 'orgunit';
    }

    public function print_filter(MoodleQuickForm $mform, $formdata = false): void {
        static::add_level_selector($mform, 'unit');
    }
}
