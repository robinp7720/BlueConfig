<?php

namespace Blue;
use Blue\mysql;

require_once("drivers/mysql.php");

class config
{
    private $method = "";
    private $all_methods = ["mysql","file"];

    private $driver;

    public function setType($type) {
        if (in_array($type,$this->all_methods)){
            $this->method = $type;
        }
    }

    public function setup($info) {
        $this->driver = new $this->method($info);
        return $this;
    }

    private function setValue($option, $value){
        $this->driver->setValue($option,$value);
    }

    private function setArray($array,$path = ""){
        foreach ($array as $key => $value) {
            if (gettype($value)=="array") {
                $this->setArray($value,$path.$key.".");
            }else{
                $this->setValue($path.$key,$value);
            }
        }
    }

    public function set($option, $value = null) {
        if (gettype($option) == "array") {
            $this->setArray($option);
        }elseif (gettype($option) == "string") {
            $this->setValue($option,$value);
        }
        return $this;
    }

    public function get($option="") {
        return $this->driver->get($option);
    }
}