<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:17
     */

    /**
     * Class GeoLocation
     */
    class GeoLocation extends Base
    {
        public $words;
        const DB_NAME = 'geolocation';
        const DB_FILTER_NAME = 'geo_filters';
        const className = 'geolocation';
        const filter_fields = array('id', 'geo_id', 'parent');

        private $_filters = array();

        public function getFilters()
        {
            return $this->_filters;
        }
    }