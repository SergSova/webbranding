<?php

    class db
    {

        static private $HOST = 'localhost';
        static private $DB_NAME = 'test';
        static private $USER = 'root';
        static private $PASS = '';
        protected $link;

        protected static $instance = null;
        protected function __construct()
        {
            $this->link = mysqli_connect(db::$HOST, db::$USER, db::$PASS, db::$DB_NAME);
            $this->link->set_charset("utf8");
        }

        public function __destruct()
        {
            if ($this->link) {
                $this->link->close();
            }
        }


    }

