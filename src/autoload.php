<?php
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'mydate\mydate' => '/MyDate.php',
                'mydate\date' => '/Date.php',
                'mydate\exception\exception' => '/Exception/Exception.php',
                'mydate\exception\validationexception' => '/Exception/ValidationException.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);