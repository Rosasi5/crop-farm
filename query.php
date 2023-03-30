<?php
include "connection.php";
if (isset($_GET["selectedCounty"])) {
    // mysqli_real_escape_string() -> escape all sql-unfliendly characters so that the sql query will not resturn error(s)
    $selectedCounty = mysqli_real_escape_string($connection, $_GET["selectedCounty"]);
    if ($selectedCounty != "default") {
        $query = "SELECT constituency_name from constituency WHERE county_id = (SELECT county_id FROM county WHERE county_name = '$selectedCounty')";
        $result = mysqli_query($connection, $query);
        $constituencyArray = mysqli_fetch_all($result);
        echo json_encode($constituencyArray);
    }
}
if (isset($_GET["counties"])) {
    $query = "SELECT county_name FROM county";
    $result = mysqli_query($connection, $query);
    $countyArray = mysqli_fetch_all($result);
    echo json_encode($countyArray);
}

if (isset($_GET["county"]) || isset($_GET["constituency"]) || isset($_GET["rainfall"]) || isset($_GET["temperature"]) || isset($_GET["humidity"])) {
    $county = mysqli_real_escape_string($connection, $_GET["county"]);
    $constituency = mysqli_real_escape_string($connection, $_GET["constituency"]);
    $rainfallValue = mysqli_real_escape_string($connection, $_GET["rainfall"]);
    $temperatureValue = mysqli_real_escape_string($connection, $_GET["temperature"]);
    $humidityValue = mysqli_real_escape_string($connection, $_GET["humidity"]);
    $hasWhere = false;
    $locationCanGrow = false;

    $final_query = "SELECT crop_name FROM crops";
    $subQuerySoil = " soil_id = ";

    /** Soil Type */
    if ($constituency != "") {
        $query = "SELECT soil_type_id FROM constituency WHERE constituency_name = '$constituency'";
        $result = mysqli_query($connection, $query);
        $arrayResult = mysqli_fetch_all($result);
        foreach ($arrayResult as $key => $value) {
            $consttituency_soil_id = $value[0];
        }
        $query = "SELECT soil_id FROM crops";
        $result = mysqli_query($connection, $query);
        $arrayResult = mysqli_fetch_all($result);
        $soilIdArray = array();
        $soilTypeArray = array();
        $firstSoilId = true;
        foreach ($arrayResult as $key => $value) {
            $soilIdArray = str_split($value[0], 1);
            for ($i = 0; $i < $value[0]; $i++) {
                if (in_array($consttituency_soil_id, $soilIdArray)) {
                    $soil_id = $value[0];
                    array_push($soilTypeArray, $soil_id);
                    if (!$hasWhere) {
                        $final_query = $final_query . " WHERE (" . $subQuerySoil . "'$soil_id'";
                        $hasWhere = true;
                        $firstSoilId = false;
                    } else {
                        // TODO: check this
                        if ($firstSoilId) {
                            $final_query = $final_query . " AND (" . $subQuerySoil . "'$soil_id'";
                            $firstSoilId = false;
                        } else {
                            $final_query = $final_query . " OR " . $subQuerySoil . "'$soil_id'";
                        }
                    }
                    $locationCanGrow = true;
                    break;
                }
            }
        }
        if ($locationCanGrow) {
            // echo json_encode("$arrayResult");
            $final_query = $final_query . ")";
        }
    }
    /** Rainfall Range */
    if ($rainfallValue != "") {
        $query = "SELECT rainfall_range FROM rainfall_distribution";
        $result = mysqli_query($connection, $query);
        $arrayResult = mysqli_fetch_all($result);
        $lowerBound = 0;
        $upperBound = 0;
        foreach ($arrayResult as $key => $value) {
            $rangeString = $value[0];
            $buffer = ""; // iterator/buffer
            $arrList = array(); // hold the ranges
            $numDigits = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

            for ($i = 0; $i < strlen($rangeString); $i++) {
                if (in_array($rangeString[$i], $numDigits)) {
                    $buffer = $buffer . $rangeString[$i];
                } else {
                    if ($buffer != "") {
                        array_push($arrList, $buffer);
                        // lower bound calculations
                        $lowerBound = $rainfallValue - $buffer;
                        $buffer = "";
                    }
                }
                if ($i == strlen($rangeString) - 1) {
                    array_push($arrList, $buffer);
                    $upperBound = $buffer - $rainfallValue;
                }
            }
            if ($upperBound >= 0 && $lowerBound >= 0) {
                // get range rainfall_id
                $query = "SELECT rainfall_id FROM rainfall_distribution WHERE rainfall_range = '$rangeString'";
                $result = mysqli_query($connection, $query);
                $arrayResult = mysqli_fetch_all($result);
                $rainfall_id = $arrayResult[0][0];
            }
        }

        if (!$hasWhere) {
            $final_query = $final_query . " WHERE " . " rainfall_id = " . $rainfall_id;
            $hasWhere = true;
        } else {
            $final_query = $final_query . " AND " . " rainfall_id = " . $rainfall_id;
        }
    }
    /** Temperature Range */
    if ($temperatureValue != "") {
        $query = "SELECT temperature_range FROM temperatures";
        $result = mysqli_query($connection, $query);
        $arrayResult = mysqli_fetch_all($result);

        $lowerBound = 0;
        $upperBound = 0;
        foreach ($arrayResult as $key => $value) {
            $rangeString = $value[0];
            $buffer = ""; // iterator/buffer
            $arrList = array();
            $numDigits = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

            for ($i = 0; $i < strlen($rangeString); $i++) {
                if (in_array($rangeString[$i], $numDigits)) {
                    $buffer = $buffer . $rangeString[$i];
                } else {
                    if ($buffer != "") {
                        array_push($arrList, $buffer);
                        // lower bound calculations
                        $lowerBound = $temperatureValue - $buffer;
                        $buffer = "";
                    }
                }
                if ($i == strlen($rangeString) - 1) {
                    array_push($arrList, $buffer);
                    $upperBound = $buffer - $temperatureValue;
                }
            }
            if ($upperBound >= 0 && $lowerBound >= 0) {
                // get range temperature_id
                if ($rangeString != "") {
                    $query = "SELECT temperature_id FROM temperatures WHERE temperature_range = '$rangeString'";
                    $result = mysqli_query($connection, $query);
                    $arrayResult = mysqli_fetch_all($result);
                    $temperature_id = $arrayResult[0][0];
                }
            }
        }
        // this condition will ocuur if you try to query temperature range not specified in the database
        // if ($temperature_id != "") {
        if (!$hasWhere) {
            $final_query = $final_query . " WHERE " . " temperature_id = " . $temperature_id;
            $hasWhere = true;
        } else {
            $final_query = $final_query . " AND " . " temperature_id = " . $temperature_id;
        }
    } else {
        // }
        // echo json_encode($temperature_id);
    }
    /** Humidity Range */
    if ($humidityValue != "") {
        $query = "SELECT humidity_range FROM humidity";
        $result = mysqli_query($connection, $query);
        $arrayResult = mysqli_fetch_all($result);

        $lowerBound = 0;
        $upperBound = 0;
        foreach ($arrayResult as $key => $value) {
            $rangeString = $value[0];
            $buffer = ""; // iterator/buffer
            $arrList = array();
            $numDigits = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

            for ($i = 0; $i < strlen($rangeString); $i++) {
                if (in_array($rangeString[$i], $numDigits)) {
                    $buffer = $buffer . $rangeString[$i];
                } else {
                    if ($buffer != "") {
                        array_push($arrList, $buffer);
                        // lower bound calculations
                        $lowerBound = $humidityValue - $buffer;
                        $buffer = "";
                    }
                }
                if ($i == strlen($rangeString) - 1) {
                    array_push($arrList, $buffer);
                    $upperBound = $buffer - $humidityValue;
                }
            }
            if ($upperBound >= 0 && $lowerBound >= 0) {
                // get range temperature_id
                $query = "SELECT humidity_id FROM humidity WHERE humidity_range = '$rangeString'";
                $result = mysqli_query($connection, $query);
                $arrayResult = mysqli_fetch_all($result);
                $humidity_id = $arrayResult[0][0];
            }
        }
        if (!$hasWhere) {
            $final_query = $final_query . " WHERE " . " humidity_id = " . $humidity_id;
            $hasWhere = true;
        } else {
            $final_query = $final_query . " AND " . " humidity_id = " . $humidity_id;
        }
    }
    if ($constituency != "" && !$locationCanGrow) {
        echo json_encode("");
    } else {
        $final_query = $final_query . " ORDER BY crop_name ASC";
        // echo json_encode($final_query);
        $result = mysqli_query($connection, $final_query);
        $arrayResult = mysqli_fetch_all($result);
        $cropsArray = array();
        foreach ($arrayResult as $key => $value) {
            array_push($cropsArray, $value[0]);
        }
        echo json_encode($cropsArray);
    }
}