<?php

use block_configurable_reports\local\plugin\org_filter_base;

/** @noinspection PhpUnused */
class plugin_orgstream extends org_filter_base {

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
        return 'orgstream';
    }

    /**
     * @inheritDoc
     */
    public function print_filter(MoodleQuickForm $mform, stdClass $data): void {
        self::add_level_selector($mform, 'paypoint', 1, true);
    }
}