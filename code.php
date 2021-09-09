<?php
    session_start();

    function get_rows($mysqli, $query) {
        $rows = array();
        $result = $mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            $nme = $row["fname"];
            $gndr = $row["gender"];
            $occr = $row["occurences"];
            $cntr = $row["country"];
            array_push($rows, array("name" => $nme,
                                    "gender" => $gndr, 
                                    "occurences" => $occr, 
                                    "country" => $cntr));
        }
        return $rows;
    }

    $mysqli = new mysqli("127.0.0.1", "tobi", "tobi", "imiona");
    if(isset($_GET['send'])){
        $i = 0;
        $names = $_GET['name'];
        $name = explode(",", str_replace(" ", "", $names));
        $gender = $_GET['genderFilter'];
        $country = $_GET['countryFilter'];
        $index = "";
        $nameString = "";

        if (!empty($names)) {

            if (!empty($gender)) {
                $genderString = " AND gender = '$gender'";
                $index = " USE INDEX (gender)";
            } else {
                $genderString = '';
            }

            if (!empty($country)) {
                $countryString = " AND country = '$country'";
                $index = " USE INDEX (country)";
            } else {
                $countryString = '';
            }

            if (!empty($country) && !empty($gender)) {
                $index = " USE INDEX (gencountry)";
            }

            $i = 0;
            $j = 0;

            /*foreach ($name as $index => $currentName) {
                $is_complete = true;

                foreach ($currentName as $key => $letter) {
                    if ($letter  == '*') {
                        $is_complete = false;
                        $name[] = substr($currentName, 0, $key);
                        break;
                    }
                }

                if ($is_complete) {*/
                    if ($i > 0) {
                        $nameString = $nameString . "|";
                    }
                    $nameString = $nameString . "$name";
                    $i++;/*
                } else {
                    if ($j > 0) {
                        $outNameString = $outNameString . "*|";
                    }
                    $outNameString = $outNameString . "$currentName";
                    $y++;
                }
            }*/

            $sql = "SELECT fname, gender, occurences, country FROM names$index WHERE fname REGEXP '^($nameString)$'$genderString$countryString";
            echo($sql);
            echo(str_contains($nameString, '*'));
        }

        if(isset($_GET['send'])&&!empty($names)) {
            $rows = get_rows($mysqli, $sql);
            $_SESSION["rows"] = $rows;
            $_SESSION["hasResults"] = true;
            $mysqli->close();
        }

        if (empty($names)) {
            session_unset();
        }
        //header("Location: index.php");
    }

    if (isset($_GET["popular"])) {
        if (!empty($_GET["genderFilter"])) {
            $gender = $_GET["genderFilter"];
            $genderString = " WHERE gender = '$gender'";
        } else $genderString = "";

        if (!empty($_GET["countryFilter"])) {
            $country = $_GET["countryFilter"];
            if (!empty($_GET["genderFilter"])) $prefix = "AND";
            else $prefix = "WHERE";
            $countryString = " $prefix country = '$country'";
        } else $countryString = "";

        $sql = "SELECT fname, gender, occurences, country FROM names$genderString$countryString LIMIT 1";
        echo($sql);
        $rows = get_rows($mysqli, $sql);
        $_SESSION["rows"] = $rows;
        $_SESSION["hasResults"] = true;
        header("Location: index.php");
    }

    if (isset($_GET["lucky"])) {
        $totalrows = $mysqli->query("SELECT COUNT(fname) FROM names");
        $totalrows = $totalrows->fetch_assoc();
        foreach ($totalrows as $totalrows);
        $randIndex = random_int(1, $totalrows);
        $offset = $randIndex - 1;
        $result = $mysqli->query("SELECT fname FROM names LIMIT $offset,1");
        $row = $result->fetch_assoc();
        $name = $row["fname"];
        $sql = "SELECT fname, gender, occurences, country FROM names WHERE fname LIKE('$name')";
        $rows = get_rows($mysqli, $sql);
        $_SESSION["rows"] = $rows;
        $_SESSION["hasResults"] = true;
        $mysqli->close();
        header("Location: index.php");
    }

   
?>