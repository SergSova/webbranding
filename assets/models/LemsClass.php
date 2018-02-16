<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:13
     */

    class Lems
    {
        public $id;
        public $word;
        public $lema;
        public $morph;
        public $count;
        public $text;

        /**
         * Lems constructor.
         */
        public function __construct()
        {
            $this->count = 0;
            $this->text = array();
        }

        public function addText($text)
        {
            array_push($this->text, $text);
        }
    }