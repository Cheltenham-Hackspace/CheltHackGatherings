<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 15/06/15
 * Time: 19:27
 */
class DatabaseModel
{

    /**
     * @var string - The hostname on which the database is operating
     */
    private $hostname;
    /**
     * @var string - The user with which to login to the database.
     */
    private $user;
    /**
     * @var string - The password associated with the user passed.
     */
    private $password;
    /**
     * @var string - The name of the database with which to connect.
     */
    private $database;
    /**
     * @var int - The port on which the database is running.
     */
    private $port;
    /**
     * @var int - The amount of seconds required to pass before a timeout occurs.
     */
    private $timeout;
    /**
     * @var string - The identifier for the error if an error has occurred. Full messages can be determined through
     * DatabaseModel::GetError(string)
     */
    private $error;
    /**
     * @var string - An optional custom error message to be reported when DatabaseModel::GetError(string)
     */
    private $customError;
    /**
     * @var bool - Whether to store the password in the class or clear it when a connection has been made.
     */
    private $cachePass;
    /**
     * @var mysqli - Stores the instance of the mysqli object when connected to a database.
     */
    private $mysqli;

    private $errorMessage = array(
        "ERR-NO-AUTH-FILE" => "When trying to open the authentication file the server encountered an file-not-found 
        error. Please try again later. If the error persists please report it to the webmaster.",
        "ERR-WRONG-AUTH-LINES" => "The authentication file specified by the server did not contain enough lines to 
        get proper authentication details from. If the error persists please report it to the webmaster.",
        "ERR-AUTH-OPEN-FAILED" => "When trying to open the authentication file the server encountered an unknown
        error. Please try again later. If the error persists please report it to the webmaster.",
        "ERR-AUTH-SIZE-FAILED" => "When trying to retrieve the size of the file the server encountered an unspecified
         error. Please try again later. If the error persists please report it to the webmaster.",
        "ERR-AUTH-READ-FAILED" => "When trying to read from the authentication file the server encountered an unknown
         error. Please try again later. If the error persists please report it to the webmaster.",
        "ERR-AUTH-CLOSE-FAILED" => "When trying to close the authentication file the server encountered an unknown
        error. Please try again later. If the error persists please report it to the webmaster.",
        "ERR-CONNECT-NO-DETAILS" => "When trying to connect to the database no details were provided. This is usually
         caused by a developer not calling one of the INIT functions. Please report this to the webmaster.",
        "ERR-STATEMENT-PREPARE-FAILED" => "When trying to prepare our database query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster.",
        "ERR-STATEMENT-RESULT-FAILED" => "When trying to get the result of the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster.",
        "ERR-STATEMENT-EXECUTE-FAILED" => "When trying to execute the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster.",
        "ERR-STATEMENT-BIND-FAILED" => "When trying to bind parameters to the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster."
    );

    /**
     * This function will return the human readable error string to send to the client from an error ID.
     * @param $errorID string - The ID of the error
     * @return string - The human readable and understandable error.
     */
    public function GetErrorString($errorID)
    {
        if (DatabaseModel::getCustomError() != null && DatabaseModel::getCustomError()) {
            $err = DatabaseModel::getCustomError();
            DatabaseModel::setCustomError(null);
            return $err;
        }
        if (in_array($errorID, DatabaseModel::getErrorMessage())) {
            return DatabaseModel::getErrorMessage()[$errorID];
        } else {
            return "The specified error ID was not found in the error messages.";
        }
    }

    /**
     * @return string
     */
    protected function getCustomError()
    {
        return $this->customError;
    }

    /**
     * @param string $customError
     */
    protected function setCustomError($customError)
    {
        $this->customError = $customError;
    }

    /**
     * @return array
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param array $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * This function will create an instance of the DatabaseModel from the supplied database information.
     * @param $hostname string - The hostname on which the database is operating
     * @param $user string - The user with which to login to the database.
     * @param $password string - The password associated with the user passed.
     * @param $database string - The name of the database with which to connect.
     * @param $port int - The port on which the database is running.
     * @param $timeout int - The amount of seconds required to occur before a timeout occurs.
     * @param $cachePass bool - Whether to store the supplied password. If false then a password will be required
     * upon re-authentication or re-connect.
     *
     * @returns DatabaseModel
     */
    public function init($hostname, $user, $password, $database, $port = 2206, $timeout = 10, $cachePass =
    false)
    {
        //If the user has requested the storing of the password then store it, otherwise just skip over it leaving
        // $password as null.
        if ($cachePass) $this->setPassword($password);
        //Set given details
        $this->setDatabase($database);
        $this->setHostname($hostname);
        $this->setTimeout($timeout);
        $this->setPort($port);
        $this->setUser($user);
    }

    /**
     * This function will attempt to create
     * @param $authFile
     * @return bool
     */
    public function initFromAuthFile($authFile)
    {
        //Try and open, get the size, read and close the file. If the process fails anywhere return false.
        $fileHandle = fopen($authFile, "r");
        if ($fileHandle === false) {
            $this->error = "ERR-AUTH-OPEN-FAILED";
            return false;
        }

        $fileSize = filesize($authFile);
        if ($fileSize === false) {
            $this->error = "ERR-AUTH-SIZE-FAILED";
            return false;
        }

        $fileData = fread($fileHandle, $fileSize);
        if ($fileData === false) {
            $this->error = "ERR-AUTH-READ-FAILED";
            return false;
        }


        if (fclose($fileHandle) === false) {
            $this->error = "ERR-AUTH-CLOSE-FAILED";
            return false;
        }

        $fileLines = explode("\n", $fileData);

        if (count($fileLines) < 4) {
            $this->error = "ERR-WRONG-AUTH-LINES";
            return false;
        }

        $this->setHostname($fileLines[0]);
        $this->setDatabase($fileLines[1]);
        $this->setUser($fileLines[2]);
        $this->setPassword($fileLines[3]);

        if (count($fileLines) >= 5) {
            if (is_numeric($fileLines[4])) {
                $this->setPort($fileLines[4]);
            } else {
                $this->setPort(3306);
            }
        } else {
            $this->setPort(3306);
        }

        if (count($fileLines) >= 6) {
            if (is_numeric($fileLines[5])) {
                $this->setTimeout($fileLines[5]);
            } else {
                $this->setTimeout(10);
            }
        } else {
            $this->setTimeout(10);
        }

        if (count($fileLines) >= 7) {
            if (is_bool($fileLines[6])) {
                $this->setCachePass($fileLines[6]);
            } else {
                $this->setCachePass(false);
            }
        } else {
            $this->setCachePass(false);
        }

        return true;
    }

    /**
     * This function will establish a connection to the database from the details supplied from the INIT functions.
     * @return bool If the connection was successful
     */
    public function connect()
    {
        if (empty($this->hostname) || empty($this->user) || empty($this->password) || empty($this->database)) {
            $this->error = "ERR-CONNECT-NO-DETAILS";
            return false;
        }

        $tempDatabase = mysqli_init();
        $tempDatabase->set_opt(MYSQLI_OPT_CONNECT_TIMEOUT, $this->getTimeout());


        $tempDatabase->connect($this->getHostname(), $this->getUser(), $this->getPassword(), $this->getDatabase(),
            $this->getPort());

        if ($tempDatabase->connect_error) {
            $this->error = "MQI: [" . $tempDatabase->connect_errno . "]: " . $tempDatabase->connect_error;
            return false;
        }

        $tempDatabase->set_charset('utf8');

        if (!$tempDatabase) {
            $this->error = "MQI: [" . $tempDatabase->errno . "]: " . $tempDatabase->error;
            return false;
        }

        $this->setMysqli($tempDatabase);
        if (!$this->isCachePass()) {
            $this->setPassword(null);
        }

        return true;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return boolean
     */
    public function isCachePass()
    {
        return $this->cachePass;
    }

    /**
     * @param boolean $cachePass
     */
    public function setCachePass($cachePass)
    {
        $this->cachePass = $cachePass;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * This function will return remove all elements from the variable $data that are not in the array $allowedData.
     * This should be used before queries using arrays to avoid SQL injection on form based data.
     * @param $data array(string=>string) This is the hash of data. In ideal situations this should be in the form of
     * column_name => column data.
     * @param $allowedData array(string) The list of keys are are allowed in the $data array.
     * @return array(string => string) The new array with the explicit data removed
     */
    protected function filterExplicitHashData($data, $allowedData)
    {
        //Get the list of keys in the array so we can iterate through by integer IDs.
        $keys = array_keys($data);
        //From 0 to the last key id
        for ($i = 0; $i < count($keys); $i++) {
            //If the key is not a valid column
            if (!in_array($keys[$i], $allowedData)) {
                //Then remove it from the array.
                array_splice($data, $i, 1);
            }
        }

        return $data;
    }

    /**
     * This function will take the $data array in the form of column_name => column_data and create an executable SQL
     * query from the base SQL. The base query should end in 'WHERE '. It will complete the SQL statement and then
     * attempt to create a prepared statement and return that. On error it will return false after setting the
     * relevant error code in {@link DatabaseModel::setError(string)}
     * @param $data array() (string=>string) The hash of data in the form column_name => column_data to populate the
     * query with
     * @param $baseSQL string The base SQL query with the WHERE section left blank. Eg 'SELECT * FROM `table` WHERE '
     * @return mixed Will return a valid prepared statement on success or <code>FALSE</code> on fail with the error
     * being logged in {@link getError()}
     */
    protected function bindArrayBasedParams($data, $baseSQL)
    {
        //Create a string for the parameter types. These will all be strings as it is much easier to process than
        // trying to determine types or asking for another array of types.
        $paramTypes = '';
        //Get the new set of keys from the array
        $keys = array_keys($data);
        //And iterate through them
        for ($i = 0; $i < count($keys); $i++) {
            //Add the condition to the sql string in the following format: '`COLUMN`=? AND ' this means that we can
            // add queries after and use the prepared statement to run the query and prevent SQL injection.
            $baseSQL .= "`" . $keys[$i] . "`=? AND ";
            //And add an S to the types.
            $paramTypes .= "s";
        }

        //Remove the last 5 characters from the SQL string ' AND ' and add a ; to the end to make it a valid query.
        $baseSQL = substr($baseSQL, 0, strlen($baseSQL) - 5) . ";";

        //Create the parameter array
        $params = array();
        //with call_user_func_array, array params must be passed by reference
        $params[] = &$paramTypes;

        //Iterate through the key set of keys
        $keys = array_keys($data);
        for ($i = 0; $i < count($keys); $i++) {
            //And add the condition by reference.
            $params[] = &$data[$keys[$i]];
        }


        //region SQL Execution, Gathering and Processing
        $stmt = $this->getMysqli()->prepare($baseSQL);
        if ($stmt === false) {
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        if (call_user_func_array(array($stmt, 'bind_param'), $params) === false) {
            $this->setError("ERR-STATEMENT-BIND-FAILED");
            return false;
        }

        return $stmt;
    }

    /**
     * @return mysqli
     */
    public function getMysqli()
    {
        return $this->mysqli;
    }

    /**
     * @param mysqli $mysqli
     */
    public function setMysqli($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * This function will take the $data array in the form of column_name => column_data and create an executable SQL
     * query from the base SQL. The base query should end in 'WHERE '. It will complete the SQL statement and then
     * attempt to create a prepared statement and return that. On error it will return false after setting the
     * relevant error code in {@link DatabaseModel::setError(string)}
     * @param $data array() (string=>string) The hash of data in the form column_name => column_data to populate the
     * query with
     * @param $baseSQL string The base SQL query with the WHERE section left blank. Eg 'SELECT * FROM `table` WHERE '
     * @return mixed Will return a valid prepared statement on success or <code>FALSE</code> on fail with the error
     * being logged in {@link getError()}
     */
    protected function bindArrayBasedParamsWithAppend($data, $baseSQL, $append, $remove, $end)
    {
        //Create a string for the parameter types. These will all be strings as it is much easier to process than
        // trying to determine types or asking for another array of types.
        $paramTypes = '';
        //Get the new set of keys from the array
        $keys = array_keys($data);
        //And iterate through them
        for ($i = 0; $i < count($keys); $i++) {
            //Add the condition to the sql string in the following format: '`COLUMN`=? AND ' this means that we can
            // add queries after and use the prepared statement to run the query and prevent SQL injection.
            $baseSQL .= str_replace("{{K}}", $keys[$i], $append);
            //And add an S to the types.
            $paramTypes .= "s";
        }

        //Remove the last 5 characters from the SQL string ' AND ' and add a ; to the end to make it a valid query.
        $baseSQL = substr($baseSQL, 0, strlen($baseSQL) - $remove) . $end;

        //Create the parameter array
        $params = array();
        //with call_user_func_array, array params must be passed by reference
        $params[] = &$paramTypes;

        //Iterate through the key set of keys
        $keys = array_keys($data);
        for ($i = 0; $i < count($keys); $i++) {
            //And add the condition by reference.
            $params[] = &$data[$keys[$i]];
        }


        //region SQL Execution, Gathering and Processing
        $stmt = $this->getMysqli()->prepare($baseSQL);
        if ($stmt === false) {
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        if (call_user_func_array(array($stmt, 'bind_param'), $params) === false) {
            $this->setError("ERR-STATEMENT-BIND-FAILED");
            return false;
        }

        return $stmt;
    }


    /**
     * This function will take the $data array in the form of column_name => column_data and create an executable SQL
     * query from the base SQL. The base query should end in 'WHERE '. It will complete the SQL statement and then
     * attempt to create a prepared statement and return that. On error it will return false after setting the
     * relevant error code in {@link DatabaseModel::setError(string)}
     * @param $dataset1 array() (string=>string) The hash of data in the form column_name => column_data to populate the
     * query with
     * @param $baseSQL string The base SQL query with the WHERE section left blank. Eg 'SELECT * FROM `table` WHERE '
     * @return mixed Will return a valid prepared statement on success or <code>FALSE</code> on fail with the error
     * being logged in {@link getError()}
     */
    protected function bindArrayBasedParamsTwice($dataset1, $dataset2, $baseSQL)
    {
        //Create a string to store the current section of the string being built.
        $builder = "";
        //Create a string for the parameter types. These will all be strings as it is much easier to process than
        // trying to determine types or asking for another array of types.
        $paramTypes = '';
        //Get the new set of keys from the array
        $keys = array_keys($dataset1);
        //And iterate through them
        for ($i = 0; $i < count($keys); $i++) {
            //Add the condition to the sql string in the following format: '`COLUMN`=? AND ' this means that we can
            // add queries after and use the prepared statement to run the query and prevent SQL injection.
            $builder .= "`" . $keys[$i] . "`=?, ";
            //And add an S to the types.
            $paramTypes .= "s";
        }

        //Remove the last 5 characters from the SQL string ', '.
        $builder = substr($builder, 0, strlen($builder) - 2);
        $baseSQL = str_replace("%1", $builder, $baseSQL);
        $builder = "";

        //Get the new set of keys from the array
        $keys = array_keys($dataset2);
        //And iterate through them
        for ($i = 0; $i < count($keys); $i++) {
            //Add the condition to the sql string in the following format: '`COLUMN`=? AND ' this means that we can
            // add queries after and use the prepared statement to run the query and prevent SQL injection.
            $builder .= "`" . $keys[$i] . "`=? AND ";
            //And add an S to the types.
            $paramTypes .= "s";
        }

        //Remove the last 5 characters from the SQL string ' AND '.
        $builder = substr($builder, 0, strlen($builder) - 5);
        $baseSQL = str_replace("%2", $builder, $baseSQL);

        //Create the parameter array
        $params = array();
        //with call_user_func_array, array params must be passed by reference
        $params[] = &$paramTypes;

        //Iterate through the key set of keys
        $keys = array_keys($dataset1);
        for ($i = 0; $i < count($keys); $i++) {
            //And add the condition by reference.
            $params[] = &$dataset1[$keys[$i]];
        }

        //Iterate through the key set of keys
        $keys = array_keys($dataset2);
        for ($i = 0; $i < count($keys); $i++) {
            //And add the condition by reference.
            $params[] = &$dataset2[$keys[$i]];
        }

        //region SQL Execution, Gathering and Processing
        $stmt = $this->getMysqli()->prepare($baseSQL);
        if ($stmt === false) {
            $this->setCustomError("When trying to prepare the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $this->getMysqli()->errno . ".<br>Error: '" . $this->getMysqli()->error . "'.");
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        if (call_user_func_array(array($stmt, 'bind_param'), $params) === false) {
            $this->setError("ERR-STATEMENT-BIND-FAILED");
            return false;
        }

        return $stmt;
    }

}