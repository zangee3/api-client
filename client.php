<?php
/**
 * Hiring API Client
 *
 * @author Ahmed Shams
 * @since 2015.02.20
 * @copyright Ahmed Shams
 */
require_once('lib/HiringApiClient.class.php');
echo "\n\n";

// check if file path was provided
if(count($argv) < 2) {
    //if not, output message and exit
    die("Please enter a path to the api commands file \n\n");    
}

//read command file
$handle = fopen($argv[1], "r");
if ($handle) {
    $apiClient = new Hiring_API_Client();
    $lineNum = 1;
    while (($line = fgets($handle)) !== false) {

        // split the command string into an array
        $command_array = explode(' ', trim($line));
        // pop the first element of the array into a $command variable
        $command = array_shift($command_array);

        //'list' is a core php function, so replace it with another 'listKeys' so we can call the methods dynamically
        if('list' == $command) {
            $command = 'listKeys';
        }

        // validate command before executing it, if not valid, skip to next line
        try {
            $apiClient->validate_command($command, $command_array);
        } catch (Exception $e) {
            // output exception message and the command that caused it
            echo $e->getMessage(). "\n";
            continue;
        }

        // if command is 'auth' then api version is: v2
        if('auth' === $command) {
            $apiClient->set_api_version('v2');
        }

        // call the api client method
        $response = $apiClient->$command($command_array);
        echo "$response\n";
        $lineNum++;
    }

    fclose($handle);
} else {
    // if unable to read file, output message and exit
    die("Unable to read command file");
}
?>