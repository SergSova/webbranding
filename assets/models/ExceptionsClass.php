<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:16
     */
    namespace models;
    class Exceptions extends Base
    {
        public $words;
        const DB_NAME = 'exceptions_lems';
        const className = 'exceptions';

        protected static
            $instance = null;
    }