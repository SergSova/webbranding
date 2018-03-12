<?php
namespace models;
spl_autoload_register(
	function ($class_name) {
		$class_name = str_replace('\\', '/', $class_name);
		include __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$class_name.'Class.php';
	}
);
