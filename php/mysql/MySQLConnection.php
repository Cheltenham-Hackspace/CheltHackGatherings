<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 27/05/15
 * Time: 19:00
 */
class MySQLConnection
{

    private $mysqli;
    private $hostname;
    private $port;
    private $database;
    private $username;
    private $password;
    private $timeout;
    private $connected;

    private $errorCodes = array(
        "ERR_NOT_CONNECTED" => 1,
        "ERR_NO_RESULTS" => 2,
        "ERR_TOO_MANY_RESULTS" => 3,
        "ERR_TIME_OUT" => 4
    );

    function __construct($hostname, $database, $username, $password, $port = 3306, $timeout = 10)
    {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    /**
     * @var string $depthToGlobal The string of ../ that it takes to reach the global folder (containing php/, pages/
     * etc..
     * @return MySQLConnection
     */
    public static function createDefault($depthToGlobal)
    {
        $dbDetails = SecurityUtils::getDatabaseDetails($depthToGlobal);
        return new MySQLConnection($dbDetails[0], $dbDetails[1], $dbDetails[2], $dbDetails[3]);
    }

    public function connect()
    {
        try{
            $tempDatabase = mysqli_init();
            $tempDatabase->set_opt(MYSQLI_OPT_CONNECT_TIMEOUT, $this->getTimeout());

            $tempDatabase->connect($this->getHostname(), $this->getUsername(), $this->getPassword(), $this->getDatabase(), $this->getPort());

            if($tempDatabase->connect_error){
                return array(false, $tempDatabase->connect_errno, $tempDatabase->connect_error);
            }

            $tempDatabase->set_charset('utf8');

            if (!$tempDatabase) {
                return array(false, $tempDatabase->errno, $tempDatabase->error);
            }

            $this->setMysqli($tempDatabase);
            $this->setConnected(true);

            return array(true, 200, "Success");
        }catch (Exception $e){
            return array(false, $this->getErrorCodes()["ERR_TIME_OUT"], "Error - connection timed out.");
        }
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param mixed $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return array
     */
    public function getErrorCodes()
    {
        return $this->errorCodes;
    }

    /**
     * @param array $errorCodes
     */
    public function setErrorCodes($errorCodes)
    {
        $this->errorCodes = $errorCodes;
    }

    public function disconnect()
    {
        return $this->getMysqli()->close();
    }

    /**
     * @return mysqli
     */
    public function getMysqli()
    {
        return $this->mysqli;
    }

    /**
     * @param mixed $mysqli
     */
    public function setMysqli($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /**
     * @param $id
     * @return array
     */
    public function getUserHash($id){
        if (!$this->getConnected()) {
            return array(false, $this->getErrorCodes()['ERR_NOT_CONNECTED'], "Not connected", null);
        }

        $select = "SELECT `hash` FROM `users` WHERE `id`=?;";

        $preparedStatement = mysqli_prepare($this->getMysqli(), $select);
        $preparedStatement->bind_param("i", $id);

        $result = $preparedStatement->execute();

        if (!$result) {
            return array(false, $preparedStatement->errno, $preparedStatement->error, null);
        }

        $rows = $preparedStatement->get_result();

        if ($rows->num_rows > 1) {
            return array(false, $this->getErrorCodes()['ERR_TOO_MANY_RESULTS'], "Too many results", null);
        }

        $record = $rows->fetch_array(MYSQLI_BOTH);
        return array(true, 200, "Success", $record['hash']);
    }

    /**
     * @return mixed
     */
    public function getConnected()
    {
        return $this->connected;
    }

    /**
     * @param mixed $connected
     */
    public function setConnected($connected)
    {
        $this->connected = $connected;
    }




}