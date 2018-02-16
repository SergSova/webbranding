<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:15
     */

    class Base
    {
        const DB_NAME = '';
        const className = '';
        const fields = array('id', 'word');
        const filter_fields = '';
        const DB_FILTER_NAME = '';

        protected $link;

        protected static
            $instance = null;

        /**
         * Base constructor.
         */
        protected function __construct()
        {
            $this->words = array();
            $db_conf = new db();
            $this->link = mysqli_connect($db_conf->host, $db_conf->user, $db_conf->pass, $db_conf->db_name);
            $this->link->set_charset("utf8");
            $res = mysqli_query($this->link, "SELECT ".join(',', self::fields)." FROM ".self::DB_NAME);
            for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
                $res->data_seek($row_no);
                $row = $res->fetch_assoc();
                $this->words[$row[self::fields[0]]] = mb_strtolower($row[self::fields[1]]);
            }
            $_SESSION[self::className] = serialize($this);

            if (self::DB_FILTER_NAME) {
                $filt_res = mysqli_query($this->link, "SELECT ".join(',', self::filter_fields)." FROM ".self::DB_FILTER_NAME);
                for ($row_no = $filt_res->num_rows - 1; $row_no >= 0; $row_no--) {
                    $filt_res->data_seek($row_no);
                    $row = $filt_res->fetch_assoc();
                    $this->_filters[$row['parent']][] = $row['geo_id'];
                }
            }

        }

        public function __destruct()
        {
            if ($this->link) {
                $this->link->close();
            }
        }


        /**
         * @return self
         */
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new static();
            } elseif ($_SESSION[self::className]) {
                self::$instance = unserialize($_SESSION[static::className]);
            }
            if ($reg_excl = $_POST[self::DB_NAME]) {
                if ($reg_excl == '#') {
                    unset($_SESSION[self::DB_NAME]);
                } else {
                    $_SESSION[self::DB_NAME] = $reg_excl;
                    /*заменить значения из чекбоксов на слова*/
                    array_walk(
                        $_SESSION[self::DB_NAME],
                        function (&$item, $key) {
                            $item = self::$instance->words[$key];
                        }
                    );
                }
            }

            return self::$instance;
        }

    }