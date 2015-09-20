<?php

class mysql
{
    private $mysqli;
    private $info = [];

    public function __Construct($info)
    {
        if (!isset($info["port"])) $info["port"] = 3366;
        $this->mysqli = new mysqli($info["host"], $info["username"], $info["password"], $info["database"], $info["port"]);
        $this->info["table"] = $info["table"];
    }

    public function configExist($option)
    {
        $mysqli = $this->mysqli;
        $stmt = $mysqli->stmt_init();
        if ($stmt->prepare("SELECT count(*) FROM {$this->info["table"]} WHERE `option` = ?")) {
            $stmt->bind_param("s", $option);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
        }
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function setValue($option, $value)
    {
        $value = json_encode($value);
        $mysqli = $this->mysqli;
        $stmt = $mysqli->stmt_init();

        /* Update or Insert new config? */
        if ($this->configExist($option))
            $query = "UPDATE {$this->info["table"]} SET `value`=? WHERE `option`=?";
        else
            $query = "INSERT INTO {$this->info["table"]} (`option`,`value`) VALUES (?, ?)";

        if ($stmt->prepare($query)) {
            /* bind parameters for markers */
            if ($this->configExist($option))
                $stmt->bind_param("ss", $value, $option);
            else
                $stmt->bind_param("ss", $option, $value);
            $stmt->execute();
            $stmt->close();
            return true;
        }
        return false;
    }

    public function get($option="")
    {
        $mysqli = $this->mysqli;
        $stmt = $mysqli->stmt_init();

        $query = "SELECT value, `option` FROM {$this->info["table"]} WHERE `option` LIKE ?";
        $search = "$option%";
        if ($stmt->prepare($query)) {
            $stmt->bind_param("s", $search);
            $stmt->execute();
            $result = $stmt->get_result();
            $output = [];

            if ($option=="") {

            }else {
                $option = $option . ".";
            }

            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                $key = substr($row["option"], strlen($option), strlen($row["option"]) - strlen($option) + 1);

                if ($key=="") {
                    $output[0] = json_decode($row["value"]);
                    break;
                }else {
                    set_nested_array_value($output, $key, json_decode($row["value"]), ".");
                }
            }
            $stmt->close();
            if (count($output)===1) {
                return $output[0];
            }else{
                return $output;
            }
        }
        return false;
    }
}