<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:17
     */
    namespace models;
    /**
     * Class GeoLocation
     */
    class GeoLocation extends Base
    {
        protected static
            $instance = null;

        public $words;
        const DB_NAME = 'geolocation';
        const DB_FILTER_NAME = 'geo_filters';
        const className = 'geolocation';
        const filter_fields = array('id', 'geo_id', 'parent');

        protected $_filters = array();

        public function getFilters()
        {
            return $this->_filters;
        }
    }