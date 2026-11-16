<?php

use block_configurable_reports\local\plugin\org_filter_base;

class plugin_orgjobcode extends org_filter_base {

    public function init(): void {
        parent::init();

        $this->reporttypes = [ 'sql', 'users' ];
    }

    protected static function get_name(): string {
        return 'orgjobcode';
    }

    public function print_filter(MoodleQuickForm $mform, $formdata = false): void {
        self::add_level_selector($mform, 'jobcode');
    }
}
