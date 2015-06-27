<?php

include("MySQLConnection.php");

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 27/05/15
 * Time: 19:12
 */
class GatheringMySQLConnection extends MySQLConnection
{

    /**
     * @var string $depthToGlobal The string of ../ that it takes to reach the global folder (containing php/, pages/
     * etc..
     * @return GatheringMySQLConnection
     */
    public static function createDefault($depthToGlobal)
    {
        $dbDetails = SecurityUtils::getDatabaseDetails($depthToGlobal);
        if ($dbDetails === false) return false;
        return new GatheringMySQLConnection($dbDetails[0], $dbDetails[1], $dbDetails[2], $dbDetails[3]);
    }

    public function getAllGatherings(){
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected", array());
        }

        //Create the insert statement to add a gathering with each value set to ? so we can bind_params later
        $insert = "SELECT `id` FROM `gatherings`;";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $insert);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The select failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error, array());
        }

        $rows = $preparedStatement->get_result();
        $gatherings = array();

        for($i = 0; $i < $rows->num_rows; $i++){
            $res = $rows->fetch_row()[0];
            $gatheringResult = Gathering::createFromID($res, $this);
            if($gatheringResult[0]) {
                array_push($gatherings, $gatheringResult[3]);
            }
        }

        //Otherwise return true with a success message.
        return array(true, 200, "Success", $gatherings);
    }

    /**
     * This method will add a gathering to the database.
     * @param int $accept_timeout: When to stop accepting RSVP requests (Marked in hours. To stop one hour before use -1. 30 mins before -0.5). [Optional = true, Default = 0]
     * @param bool $active: Whether the gathering is active (Whether it has been cancelled). [Optional = true, Default = true]
     * @param array $attending: The list of attendees (Should be an integer list of user IDs). [Optional = true, Default = null]
     * @param string $description: The description of the event [Optional = false]
     * @param string $name: The name of the event [Optional = false]
     * @param string $address: The address at which the gathering will be occurring [Optional = false]
     * @param double $lat: The latitude of the location at which the gathering will be occurring [Optional = true, Default = null]
     * @param double $lon: The longitude of the location at which the gathering will be occurring [Optional = true, Default = null]
     * @param int $occurring: The UNIX timestamp at which the event will be occurring [Optional = false]
     * @param int $createdBy: The User ID of the user who registered the event.
     * @return array array(bool, int, string):
     * <ul>
     *   <li>
     *       <strong>bool</strong>: Success
     *   </li>
     *   <li>
     *       <strong>int</strong>: Error Code (200 on success)
     *   </li>
     *   <li>
     *       <strong>string</strong>: Error message ('Success' on success)
     *   </li>
     * </ul>
     */
    public function addGathering($description, $name, $address, $occurring, $createdBy, $lat = null, $lon = null, $accept_timeout = 0, $active = true, $attending = null)
    {
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected");
        }

        //Create the insert statement to add a gathering with each value set to ? so we can bind_params later
        $insert = "INSERT INTO `gatherings`
                    ( `accept_timeout`, `active`, `attending`, `created`, `description`, `location_address`, `location_latitude`, `location_longitude`, `name`, `occurring`, `created_by`)
                    VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $insert);
        //Bind the parameters in the order they are listed in $insert.
        $preparedStatement->bind_param("iissssssssi", $accept_timeout, $active, implode(',', $attending), time(), $description, $address, $lat, $lon, $name, $occurring, $createdBy);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error);
        }

        //Otherwise return true with a success message.
        return array(true, 200, "Success");
    }

    /**
     * This method will add the given array of attendees to the already existing array of attendees obtained through
     * {@link GatheringMySQLConnection::getAttendees()}.
     * @param $gatheringId int: The ID for the gathering in the database.
     * @param $newAttendees array: The array of User IDs to add to the list of attendees.
     * @return array array array(bool, int, string):
     * <ul>
     *   <li>
     *       <strong>bool</strong>: Success
     *   </li>
     *   <li>
     *       <strong>int</strong>: Error Code (200 on success)
     *   </li>
     *   <li>
     *       <strong>string</strong>: Error message ('Success' on success)
     *   </li>
     * </ul>
     */
    public function addAttendees($gatheringId, $newAttendees)
    {
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected");
        }

        //Create the update statement.
        $update = "UPDATE `gatherings` SET
	                  `attending` = ?
                    WHERE `id`=?";
        //Merge the two arrays.
        $attendeesArray = array_merge($this->getAttendees($gatheringId)[3], $newAttendees);

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $update);
        //Bind the parameters in the order they are listed in $update
        $implodedData = implode(',', $attendeesArray);
        if (strlen($implodedData) > 0) {
            if (substr($implodedData, 0, 1) == ",") {
                $implodedData = substr($implodedData, 1);
            }
        }
        $preparedStatement->bind_param("si", $implodedData, $gatheringId);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error);
        }

        //Otherwise return true with a success message.
        return array(true, 200, "Success");
    }

    /**
     * This method will retrieve the list of attendees for the given gathering in an int array
     * @param $gatheringId int: The ID for the gathering in the database.
     * @return array array(bool, int, string, object):
     * <ul>
     *   <li>
     *       <strong>bool</strong>: Success
     *   </li>
     *   <li>
     *       <strong>int</strong>: Error Code (200 on success)
     *   </li>
     *   <li>
     *       <strong>string</strong>: Error message ('Success' on success)
     *   </li>
     *   <li>
     *       <strong>object</strong>: array object (null on fail)
     *   </li>
     * </ul>
     */
    public function getAttendees($gatheringId)
    {
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected", null);
        }

        //Create the select statement
        $select = "SELECT `attending` FROM `gatherings` WHERE `id`=?;";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $select);
        //Bind the parameters in the order they are listed in $select
        $preparedStatement->bind_param("i", $gatheringId);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error, null);
        }

        //Fetch the results with the OO system.
        $row = $preparedStatement->get_result();

        $row = $row->fetch_array(MYSQLI_NUM);

        //If there are 0 records (no gathering with that ID)
        if (count($row) == 0) {
            //Then return a failed array.
            return array(false, $this->getErrorCodes()['ERR_NO_RESULTS'], "No results were returned from the database for id [" . $gatheringId . "]", null);
        }

        //Otherwise get the first result and explode it into an array delimited by , (Created using implode preferably).
        $arrayResponse = explode(',', $row[0]);

        //Otherwise return true with a success message.
        return array(true, 200, "Success", $arrayResponse);
    }

    /**
     * This method will retrieve the list of not attendees for the given gathering in an int array
     * @param $gatheringId int: The ID for the gathering in the database.
     * @return array array(bool, int, string, object):
     * <ul>
     *   <li>
     *       <strong>bool</strong>: Success
     *   </li>
     *   <li>
     *       <strong>int</strong>: Error Code (200 on success)
     *   </li>
     *   <li>
     *       <strong>string</strong>: Error message ('Success' on success)
     *   </li>
     *   <li>
     *       <strong>object</strong>: array object (null on fail)
     *   </li>
     * </ul>
     */
    public function getNotAttendees($gatheringId)
    {
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected", null);
        }

        //Create the select statement
        $select = "SELECT `not_attending` FROM `gatherings` WHERE `id`=?;";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $select);
        //Bind the parameters in the order they are listed in $select
        $preparedStatement->bind_param("i", $gatheringId);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error, null);
        }

        //Fetch the results with the OO system.
        $row = $preparedStatement->get_result()->fetch_array(MYSQLI_NUM);

        //If there are 0 records (no gathering with that ID)
        if (count($row) == 0) {
            //Then return a failed array.
            return array(false, $this->getErrorCodes()['ERR_NO_RESULTS'], "No results were returned from the database for id [" . $gatheringId . "]", null);
        }

        //Otherwise get the first result and explode it into an array delimited by , (Created using implode preferably).
        $arrayResponse = explode(',', $row[0]);

        //Otherwise return true with a success message.
        return array(true, 200, "Success", $arrayResponse);
    }


    /**
     * This method will add the given array of attendees to the already existing array of attendees obtained through
     * {@link GatheringMySQLConnection::getAttendees()}.
     * @param $gatheringId int: The ID for the gathering in the database.
     * @param $newAttendees array: The array of User IDs to add to the list of attendees.
     * @return array array array(bool, int, string):
     * <ul>
     *   <li>
     *       <strong>bool</strong>: Success
     *   </li>
     *   <li>
     *       <strong>int</strong>: Error Code (200 on success)
     *   </li>
     *   <li>
     *       <strong>string</strong>: Error message ('Success' on success)
     *   </li>
     * </ul>
     */
    public function addNotAttendees($gatheringId, $newAttendees){
        //If we are not currently connected to a database
        if (!$this->getConnected()) {
            //Then return an error using the error code for not being connected.
            return array(false, $this->getErrorCodes()["ERR_NOT_CONNECTED"], "Not connected");
        }

        //Create the update statement.
        $update = "UPDATE `gatherings` SET
	                  `not_attending` = ?
                    WHERE `id`=?";
        //Merge the two arrays.
        $attendeesArray = array_merge($this->getAttendees($gatheringId)[3], $newAttendees);

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($this->getMysqli(), $update);
        //Bind the parameters in the order they are listed in $update
        $implodedData = implode(',', $attendeesArray);
        if(strlen($implodedData) > 0){
            if(substr($implodedData, 0, 1) == ","){
                $implodedData = substr($implodedData, 1);
            }
        }
        $preparedStatement->bind_param("si", $implodedData, $gatheringId);

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            return array(false, $preparedStatement->errno, $preparedStatement->error);
        }

        //Otherwise return true with a success message.
        return array(true, 200, "Success");
    }

}