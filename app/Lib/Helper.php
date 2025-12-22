<?php

namespace App\Lib;
use App\Exceptions\ClassException;

trait Helper{
    public function get(string $var)
    {
        if(property_exists(get_called_class(), $var)){
            return $this->$var;
        } else {
            return false;
        }
    }

    public function set(string $var, $value)
    {
        if(property_exists(get_called_class(), $var)){
            $this->$var = $value;
            return $this;
        } else {
            return false;
        }
    }

    public static function displayError($errorCode): string {
        if(!property_exists(get_called_class(), "errorArray"))
            throw new ClassException("Property doesn't exist");
        if(array_key_exists($errorCode, static::$errorArray)) {
            return static::$errorArray[$errorCode];
        } else {
            throw new ClassException("Key doesn't exist");
        }
    }
}