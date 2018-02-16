<?php

    spl_autoload_register(
        function ($class_name) {
            include 'assets/models/'.$class_name.'Class.php';
        }
    );