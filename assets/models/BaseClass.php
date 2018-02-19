<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 16.02.2018
     * Time: 15:15
     */

    class Base extends db
    {
        const DB_NAME = '';
        const className = '';
        const fields = array('id', 'word');
        const filter_fields = '';
        const DB_FILTER_NAME = '';


        protected static
            $instance;

        /**
         * Base constructor.
         */
        protected function __construct()
        {
            parent::__construct();
            $this->words = array();
//            $this->link = db::getLink();
            $res = mysqli_query($this->link, "SELECT ".join(',', self::fields)." FROM ".static::DB_NAME);
            for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
                $res->data_seek($row_no);
                $row = $res->fetch_assoc();
                $this->words[$row[self::fields[0]]] = mb_strtolower($row[self::fields[1]]);
            }
            $_SESSION[static::className] = serialize($this);

            if (static::DB_FILTER_NAME) {
                $filt_res = mysqli_query($this->link, "SELECT ".join(',', static::filter_fields)." FROM ".static::DB_FILTER_NAME);
                for ($row_no = $filt_res->num_rows - 1; $row_no >= 0; $row_no--) {
                    $filt_res->data_seek($row_no);
                    $row = $filt_res->fetch_assoc();
                    $this->_filters[$row['parent']][] = $row['geo_id'];
                }
            }
        }


        /**
         * @return static
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            } elseif ($_SESSION[static::className]) {
                static::$instance = unserialize($_SESSION[static::className]);
            }

            if ($reg_excl = $_POST[static::DB_NAME]) {
                if ($reg_excl == '#') {
                    unset($_SESSION[static::DB_NAME]);
                } else {
                    $_SESSION[static::DB_NAME] = $reg_excl;
                    /*заменить значения из чекбоксов на слова*/
                    array_walk(
                        $_SESSION[static::DB_NAME],
                        function (&$item, $key) {
                            $item = static::$instance->words[$key];
                        }
                    );
                }
            }

            return static::$instance;
        }

    }