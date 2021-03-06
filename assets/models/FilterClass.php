<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 19.01.2018
     * Time: 10:38
     */

    namespace models;

    /**
     * Class Filter
     *
     * @property $excluded     Excluded
     * @property $exceptions   Exceptions
     * @property $geo_location GeoLocation
     */
    class Filter
    {
        public $excluded;
        public $exceptions;
        public $geo_location;
        private $_isGeoView = true;

        /**
         * Filter constructor.
         */
        public function __construct()
        {
            $this->excluded = Excluded::getInstance();
            $this->exceptions = Exceptions::getInstance();
            $this->geo_location = GeoLocation::getInstance();

            $this->_isGeoView = $_POST['is_geo_data'] == 'on' ? true : false;
        }

        /**
         * @param $word
         *
         * @return bool
         */
        public function filteredWord($word)
        {
            $res = in_array($word, $this->exceptions->words);
            if (!$res && isset($_SESSION['excluded_words']) && is_array($_SESSION['excluded_words'])) {
                $res = in_array($word, $_SESSION['excluded_words']);
            }
            if (!$res && !$this->_isGeoView) {
                if (!$this->_isGeoView) {
                    $res = $this->isGeo($word);
                }
            }
	        if (!$res && $this->isGeo($word) && isset($_SESSION['include_geo']) && is_array($_SESSION['include_geo'])) {
		        $res = !in_array($word, $_SESSION['include_geo']) && $this->isGeo($word);
	        }

            return $res;
        }

        public function isGeo($word)
        {
            $res = in_array($word, $this->geo_location->words);

            return $res;
        }

        public function getGeoFilter()
        {
            return $this->geo_location->getFilters();
        }
    }