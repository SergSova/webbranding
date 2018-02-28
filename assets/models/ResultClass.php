<?php

    namespace models;

    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 18.01.2018
     * Time: 12:15
     */
    class Result
    {
        public $lems;
        public $geo;

        /**
         * Result constructor.
         */
        public function __construct()
        {
            if (isset($this->lems) || isset($this->geo)) {
                return $this;
            }
            if ($_SESSION['res_obj']) {
                $res_obj = $_SESSION['res_obj'];
                $this->lems = unserialize($res_obj['lems']);
                $this->geo = unserialize($res_obj['geo']);
            } else {
                $this->lems = array();
                $this->geo = array();
            }

            return $this;
        }

        public function saveInstance()
        {
            $_SESSION['res_obj'] = array(
                'lems' => serialize($this->lems),
                'geo'  => serialize($this->geo),
            );
        }

        public function toJSON()
        {
//            $this->jSorting($this->lems);
//            $this->jSorting($this->geo);

            return json_encode($this);
        }

        public function geo_exists($word)
        {
            return array_key_exists($word, $this->geo);
        }

        /**
         * @param $word
         *
         * @return Lems
         */
        public function getGeo($word)
        {
            if ($this->geo_exists($word)) {
                return $this->geo[$word];
            }

            return new Lems();
        }

        public function update_Geo($geo_obj)
        {
            if ($geo_obj && $geo_obj->word) {
                $this->geo[$geo_obj->word] = $geo_obj;
            }
        }

        public function lem_exists($word)
        {
            return array_key_exists($word, $this->lems);
        }

        /**
         * @param $word
         *
         * @return Lems
         */
        public function getLem($word)
        {
            if ($this->lem_exists($word)) {
                return $this->lems[$word];
            }

            return new Lems();
        }

        public function update_Lems($lem_obj)
        {
            if ($lem_obj && $lem_obj->word) {
                $this->lems[$lem_obj->word] = $lem_obj;
            }
        }

        public function updLem($word, $morph, $text)
        {
            $lema = $this->getLem($word);
            $lema->word = $word;
            if ($morph == null) {
                $morph = array();
            }
            $lema->morph = $morph;
            $lema->count++;
            $lema->addText($text);

            return $lema;

        }

        public function updGeo($word, $morph, $text)
        {
            $lema = $this->getGeo($word);
            $lema->word = $word;
            if ($morph == null) {
                $morph = array();
            }
            $lema->morph = $morph;
            $lema->count++;
            $lema->addText($text);

            return $lema;

        }

        protected function jSorting(&$arr)
        {
            usort(
                $arr,
                function ($a, $b) {
                    /**
                     * @var Lems $a
                     * @var Lems $b
                     */
                    return $a->count < $b->count;
                }
            );
        }

    }