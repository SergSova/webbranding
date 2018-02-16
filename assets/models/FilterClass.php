<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 19.01.2018
     * Time: 10:38
     */

    /**
     * Class Filter
     * @property $excluded Excluded
     * @property $exceptions Exceptions
     * @property $geo_location GeoLocation
     */
    class Filter
    {
        public $excluded;
        public $exceptions;
        public $geo_location;
        private $_isGeoView;

        /**
         * Filter constructor.
         */
        public function __construct()
        {
            $this->excluded = Excluded::getInstance();
            $this->exceptions = Exceptions::getInstance();
            $this->geo_location = GeoLocation::getInstance();
            $this->_isGeoView = $_SESSION['is_geo_data'];
        }

        public function filteredWord($word)
        {
            if (!$this->_isGeoView) {
                $is_geo = in_array($word, $this->geo_location->words);
            }

            return in_array($word, $this->exceptions->words) || in_array($word, $_SESSION['excluded_words']) || $is_geo;
        }

        public function isGeo($word)
        {
            return in_array($word, $this->geo_location->words);
        }
    }