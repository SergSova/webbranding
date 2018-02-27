<?php
    namespace models;
    class DbConnect
    {

        static private $HOST = 'localhost';
        static private $DB_NAME = 'test';
        static private $USER = 'root';
        static private $PASS = '';
        protected $link;

        protected static $instance = null;
        protected function __construct()
        {
            $this->link = mysqli_connect(self::$HOST, self::$USER, self::$PASS, self::$DB_NAME);
            $this->link->set_charset("utf8");
        }

        public function __destruct()
        {
            if ($this->link) {
                $this->link->close();
            }
        }


    }

