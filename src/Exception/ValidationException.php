<?php
namespace MyDate\Exception;

class ValidationException extends Exception {

    // message is not optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
?>