<?php

/**
 * Get and validate command line input
 *
 * @param string $prompt        The text to prompt the user with
 * @param string $response_type Type of response required
 * @param string $default       Default value
 * @param array  $options       Array of valid reponse options
 *
 * @return mixed validated response
 **/
function getInput($question,$response_type = "string", $default = "", $options = array()) {

    $valid_response = false;
    $valid_responses = "";

    switch ($response_type) {

        case "string":
            $valid_responses = implode(",",$options);
            break;

        case "boolean":
            $valid_responses = "yes,no";
            break;

        case "integer":
            $valid_responses = "Any whole number";
            break;
    }

    while(!$valid_response) {
        echo "\n$question\n";
        $handle = fopen("php://stdin","r");
        $response = trim(fgets($handle));
        fclose($handle);

        if ($response == "" && $default != "")
            $response = $default;

        if($response_type == "string" && strlen($response) > 1) {

            if(!empty($options)) {
                if(in_array($response,$options)){
                    $valid_response = true;
                }
            } else {
                $valid_response = true;
            }

        } else if ($response_type == "boolean" && ($response == "yes" || $response == "no")) {

            if($response == "yes")
                $response = true;
            else 
                $response = false;

            $valid_response = true;

        } else if ($response_type == "integer" && is_numeric($response)) {
            $valid_response = true;
        }
        
        if(!$valid_response) {
            echo "Invalid response: $response, please try again\n";
            if($valid_responses != "") {
                echo "Valid reponses: $valid_responses \n";
            }
        }
    }
    return $response;
}