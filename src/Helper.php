<?php

namespace Rybel\backbone;

use PDO;

class Helper
{
    protected $config;
    protected $conn;
    protected $error;
    protected $log;

    /**
    * Setup the helper
    *
    * @param $input array The config array (contains config info, DB object, etc.)
    */
    public function __construct(array $input)
    {
        $this->config = $input;
        $this->conn = $input['dbo'];

        if (!isset($input['aws'])) {
            // Assume we are on an EC2 instance in IAD
            $input['aws'] = array(
                'region'  => 'us-east-1',
                'version' => 'latest'
            );
        }

        $this->log = new LogHelper($input['appName'], $input['aws'], $input['type']);
    }

    public function getLastInsertID() {
        return $this->conn->lastInsertId();
    }

    /**
    * Getter for the error message
    *
    */
    public function getErrorMessage()
    {
        return $this->error;
    }

    /**
     * Wrapper around PDO to handle MySQL queries with APM
     *
     * @param $statement string The un-prepared statement
     * @param ...$args string Variable list of args to be prepared into the statement
     * @return bool|array
     */
    public function query(string $statement, ...$args)
    {
        // Create the actual query
        $handle = $this->conn->prepare($statement);
        $args = func_get_args();
        for ($i=1; $i < count($args); $i++) {
            // null can't be passed by reference
            if ($args[$i] === null) {
                $handle->bindValue($i, null, PDO::PARAM_INT);
            } else {
                $handle->bindValue($i, $args[$i]);
            }
        }

        // Check if the query was successful
        if ($handle->execute()) {
            // Check if it was select or show
            $verb = explode(' ', trim($statement))[0];
            if ($verb == 'SELECT' || $verb == 'SHOW') {
                // Return the data
                $output = $handle->fetchAll(PDO::FETCH_ASSOC);

                // Handle only 1 row being returned
                if (strpos($statement, 'LIMIT 1') !== false) {
                    $output = $output[0];
                }
            } else {
                $output = true;
            }

            $logMessage = [
                "status" => "success",
                "query" => $statement,
                "params" => $args,
                "output" => $output
            ];
            $this->log->logMessage($logMessage);
        } else {
            // Get the error message from the PDO
            $this->error = $handle->errorInfo()[2];
            $this->log->logMessage($this->error, LogVal::error);
            $output = false;
        }

        return $output;
    }
}
