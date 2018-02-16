<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:15
     */

    class Excluded extends Base
    {
        public $words;
        const DB_NAME = 'excluded_words';
        const className = 'excluded';

        protected static
            $instance = null;

        public function getWord()
        {
            if ($_SESSION[self::DB_NAME]) {
                return array_intersect_key($this->words, $_SESSION[self::DB_NAME]);
            }

            return $this->words;
        }
    }