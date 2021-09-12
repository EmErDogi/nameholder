<head></head><title>Nameholder</title>
<link rel='Stylesheet' href='style.css'>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
<?php session_start(); ?>
</head>
<body>
<div id='wrapper'>
    <div id='banner'>
        <p id='bannerText'>Nameholder</p>
    </div>
    <div id='form'>
        <form method='get' action='code.php'>
            <input type='text' name='name' placeholder='Put the name here!' id='nameInput'></br>
            <a target='blank' id='optionsButton' selectable='false' onclick='hideshow(document.getElementById("filters"))'>Advanced search</a></br></br>
            <div id='filters' style='display:none;'>
                <label>Gender: </label>
                <select id='genderFilter' name='genderFilter'>
                    <option></option>
                    <option>M</option>
                    <option>F</option>
                </select></br>
                <label>Country: </label>
                <select id='countryFilter' name='countryFilter'>
                    <option></option>
                    <?php
                        $mysqli = new mysqli("127.0.0.1", "tobi", "tobi", "nameholder");
                        $countries = $mysqli->query("SELECT DISTINCT country from names");
                        while($row = $countries->fetch_assoc()) {
                            $country = $row["country"];
                            echo("<option>$country</option>");
                        }
                    ?>
                </select>
            </div>
            <button name='send' id='send'>Look it up!</button>
            <button name='popular' id='popular'>Most popular name</button>
            <button name='lucky' id='lucky'>I'm feelin' lucky</button>
        </form>
    </div>
    <?php
        if (isset($_SESSION["hasResults"])) {

            if (!isset($_GET["sorting"])) $sorting = 'occurences';
            else $sorting = $_GET["sorting"];

            function makeSubarrays($array, $key) {
                $new_array = array();

                foreach ($array as $val) {
                    if (!isset($new_array[$val[$key]])) $len = 0;
                    else $len = count($new_array[$val[$key]]);
                    $new_array[$val[$key]][$len] = $val;
                }

                return $new_array;
            }

            function array_sort($array, $on, $order=SORT_DESC) { // from phpdotnet on the php wiki
                $new_array = array();
                $sortable_array = array();
                
                if (count($array) > 0) {
                    foreach ($array as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $k2 => $v2) {
                                if ($k2 == $on) {
                                    $sortable_array[$k] = $v2;
                                }
                            }
                        } else {
                            $sortable_array[$k] = $v;
                        }
                    }
                    
                    switch ($order) {
                        case SORT_ASC:
                            asort($sortable_array);

                        break;
                        case SORT_DESC:
                            arsort($sortable_array);
                        break;
                    }
                    
                    foreach ($sortable_array as $k => $v) {
                        $new_array[$k] = $array[$k];
                    }
                }

                    return $new_array;
                }

            $rows = $_SESSION["rows"];

            // sorting the array of values depending on the sorting type
            switch ($sorting) {
                case 'country':
                    $rows = array_sort($rows, 'country');
                    $subarrays = makeSubarrays($rows, 'country');
                    $rows = array();
                    foreach ($subarrays as $subarray) {
                        $sortsubarray = array_sort($subarray, "occurences");
                        foreach($sortsubarray as $row) {
                            array_push($rows, $row);
                        }
                    }
                    break;

                case 'gender':
                    $rows = array_sort($rows, 'gender');
                    $subarrays = makeSubarrays($rows, 'gender');
                    $rows = array();
                    foreach ($subarrays as $subarray) {
                        $sortsubarray = array_sort($subarray, "occurences");
                        foreach($sortsubarray as $row) {
                            array_push($rows, $row);
                        }
                    }
                    break;
                
                default:
                    $rows = array_sort($rows, 'occurences');
                    break;
            }

            $tables = array();

            foreach ($rows as $currentRow) {
                /*foreach ($currentRow as $key => $val) {
                    echo("$key:$val;");
                }*/
                if (!isset($tables[$currentRow["name"]])) {
                    $nme = $currentRow["name"];
                    $tables[$currentRow["name"]]["totalOccurences"] = 0;
                    $tables[$currentRow["name"]]["string"] = "<table><tr><td colspan='3' class='headers'>$nme</td></tr><tr class='headers'><td>Gender</td><td>Occurences</td><td>Country</td></tr>";
                }
                $gndr = $currentRow["gender"];
                $occr = $currentRow["occurences"];
                $cntr = $currentRow["country"];
                $tables[$currentRow["name"]]["totalOccurences"] += intval($occr);
                $tables[$currentRow["name"]]["string"] = $tables[$currentRow["name"]]["string"] . "<tr><td>$gndr</td><td>$occr</td><td>$cntr</td></tr>";
            }

            $tableString = "";

            foreach ($tables as $table) {
                $totalOccurences = $table["totalOccurences"];
                $table["string"] = $table["string"] . "<tr><td colspan='3' class='totalOccurencesRow'>Total occurences:  <span class='totalOccurencesNum'>$totalOccurences</span></td></tr></table>";
                $tableString = $tableString . $table["string"];
            }

            echo("<div id='Results'>$tableString</div>");
        }
    ?>
</div>
<script>

        function hideshow(element) {
            if (element.style.display == 'none') element.style.display = 'block';
            else element.style.display = 'none';
        }

        function resort(sorting) {
            var HttpRequest = new XMLHttpRequest();
            HttpRequest.open("GET", `localhost/index.php?sorting=${sorting}`, false);
            HttpRequest.send();
            return HttpRequest.responseText;
        }

</script>
</body>
<!--

        IDEA 1:
            Random name from database - DONE
        IDEA 2:
            Most popular names - DONE
        IDEA 3:
            Table sorting - IN PROGRESS
        IDEA 4:
            Names starting with - TODO

    -->
