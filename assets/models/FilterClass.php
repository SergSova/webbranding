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
        private $_isGeoView;

        /**
         * Filter constructor.
         */
        public function __construct()
        {
            $this->excluded = Excluded::getInstance();
            $this->exceptions = Exceptions::getInstance();
            $this->geo_location = GeoLocation::getInstance();

            $this->_isGeoView = $_SESSION['is_geo_data'] ?? $_POST['is_geo_data'];
            $_SESSION['is_geo_data'] = $this->_isGeoView == '#' ? false : $this->_isGeoView;
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
            if (!$res && $this->_isGeoView) {
                if (isset($_SESSION['include_geo']) && is_array($_SESSION['include_geo'])) {
                    $res = !in_array($word, $_SESSION['include_geo']) && $this->isGeo($word);
                } elseif (!$this->_isGeoView) {
                    $res = in_array($word, $this->geo_location->words);
                }
            }


            return $res;
        }

        public
        function isGeo(
            $word
        ) {
            $res = in_array($word, $this->geo_location->words);

            return $res;
        }
    }