<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 27/05/15
 * Time: 22:41
 */
class Gathering
{

    private $acceptTimeout;
    private $active;
    private $attending;
    private $created;
    private $createdBy;
    private $description;
    private $id;
    private $locationAddress;
    private $locationLatitude;
    private $locationLongitude;
    private $name;
    private $notAttending;
    private $occurring;
    private $concluding;

    private $attendingUserList;
    private $notAttendingUserList;
    private $occurringDate;
    private $createdByUser;
    private $createdDate;

    private function __construct()
    {
    }

    public static function createFromValues($acceptTimeout, $active, $attending, $created, $createdBy, $description,
                                            $id, $locationAddress, $locationLatitude, $locationLongitude, $name,
                                            $notAttending, $occurring, $concluding)
    {
        $instance = new Gathering();

        $instance->acceptTimeout = $acceptTimeout;
        $instance->active = $active;
        $instance->attending = $attending;
        $instance->created = $created;
        $instance->createdBy = $createdBy;
        $instance->description = $description;
        $instance->id = $id;
        $instance->locationAddress = $locationAddress;
        $instance->locationLatitude = $locationLatitude;
        $instance->locationLongitude = $locationLongitude;
        $instance->name = $name;
        $instance->notAttending = $notAttending;
        $instance->occurring = $occurring;
        $instance->concluding = $concluding;

        return $instance;
    }

    /**
     * @param int $id
     * @param MySQLConnection $mysqlConnection
     * @return array
     */
    public static function doesGatheringExist($id, $mysqlConnection){
        if (!$mysqlConnection->getConnected()) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NOT_CONNECTED'], "Not connected", false);
        }

        $select = "SELECT COUNT(*) AS `count` FROM `gatherings` WHERE `id`=?";

        $preparedStatement = mysqli_prepare($mysqlConnection->getMysqli(), $select);
        $preparedStatement->bind_param("i", $id);

        $result = $preparedStatement->execute();

        if (!$result) {
            return array(false, $preparedStatement->errno, $preparedStatement->error, false);
        }

        $rows = $preparedStatement->get_result();

        if ($rows->num_rows > 1) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_TOO_MANY_RESULTS'], "Too many results", false);
        }

        $record = $rows->fetch_array(MYSQLI_BOTH);
        $count = $record['count'];

        return array(true, 200, "Success", $count >= 1);
    }

    /**
     * @param int $id
     * @param MySQLConnection $mysqlConnection
     * @return array
     */
    public static function createFromID($id, $mysqlConnection)
    {
        if (!$mysqlConnection->getConnected()) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NOT_CONNECTED'], "Not connected", null);
        }

        $select = "SELECT * FROM `gatherings` WHERE `id`=?";

        $preparedStatement = mysqli_prepare($mysqlConnection->getMysqli(), $select);
        $preparedStatement->bind_param("i", $id);

        $result = $preparedStatement->execute();

        if (!$result) {
            return array(false, $preparedStatement->errno, $preparedStatement->error, null);
        }

        $rows = $preparedStatement->get_result();

        if ($rows->num_rows > 1) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_TOO_MANY_RESULTS'], "Too many results", null);
        }

        if($rows->num_rows == 0){
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NO_RESULTS'], "No results returned", null);
        }

        $record = $rows->fetch_array(MYSQLI_BOTH);

        $instance = new Gathering();

        $instance->acceptTimeout = $record['accept_timeout'];
        $instance->active = $record['active'];
        $instance->attending = $record['attending'];
        $instance->created = $record['created'];
        $instance->createdBy = $record['created_by'];
        $instance->description = \Michelf\Markdown::defaultTransform($record['description']);
        $instance->id = $record['id'];
        $instance->locationAddress = $record['location_address'];
        $instance->locationLatitude = $record['location_latitude'];
        $instance->locationLongitude = $record['location_longitude'];
        $instance->name = \Michelf\Markdown::defaultTransform($record['name']);
        $instance->notAttending = $record['not_attending'];
        $instance->occurring = $record['occurring'];
        $instance->concluding = $record['concluding'];

        $instance = Gathering::generateUserList($instance, $mysqlConnection);

        $userRequest = User::createFromID($instance->createdBy, $mysqlConnection);
        if($userRequest[0]){
            $instance->createdByUser = $userRequest[3];
        }

        return array(true, 200, "Success", $instance);
    }


    /**
     * @param Gathering $instance
     * @param MySQLConnection $mysqlConnection
     * @returns Gathering
     */
    private static function generateUserList($instance, $mysqlConnection)
    {
        $intUserList = $instance->getAttending();
        if ($intUserList == null) {
            $instance->setAttendingUserList(array());
        } else {
            $attendingUserList = array();
            $intUserList = explode(',', $intUserList);

            for ($i = 0; $i < count($intUserList); $i++) {
                $requestResponse = User::createFromID($intUserList[$i], $mysqlConnection);
                if ($requestResponse[0]) {
                    array_push($attendingUserList, $requestResponse[3]);
                }
            }
        $instance->setAttendingUserList($attendingUserList);
        }


        $notIntUserList = $instance->getNotAttending();
        if ($notIntUserList == null) {
            $instance->setNotAttendingUserList(array());
        } else {
            $notAttendingUserList = array();
            $notIntUserList = explode(',', $notIntUserList);

            for ($i = 0; $i < count($notIntUserList); $i++) {
                $requestResponse2 = User::createFromID($notIntUserList[$i], $mysqlConnection);
                if ($requestResponse2[0]) {
                    array_push($notAttendingUserList, $requestResponse2[3]);
                }
            }

            $instance->setNotAttendingUserList($notAttendingUserList);
        }

        return $instance;
    }

    /**
     * @return mixed
     */
    public function getAttending()
    {
        return $this->attending;
    }

    /**
     * @param mixed $attending
     */
    public function setAttending($attending)
    {
        $this->attending = $attending;
    }

    /**
     * @return mixed
     */
    public function getNotAttending()
    {
        return $this->notAttending;
    }

    /**
     * @param mixed $notAttending
     */
    public function setNotAttending($notAttending)
    {
        $this->notAttending = $notAttending;
    }

    /**
     * @return mixed
     */
    public function getAcceptTimeout()
    {
        return $this->acceptTimeout;
    }

    /**
     * @param mixed $acceptTimeout
     */
    public function setAcceptTimeout($acceptTimeout)
    {
        $this->acceptTimeout = $acceptTimeout;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLocationAddress()
    {
        return $this->locationAddress;
    }

    /**
     * @param mixed $locationAddress
     */
    public function setLocationAddress($locationAddress)
    {
        $this->locationAddress = $locationAddress;
    }

    /**
     * @return mixed
     */
    public function getLocationLatitude()
    {
        return $this->locationLatitude;
    }

    /**
     * @param mixed $locationLatitude
     */
    public function setLocationLatitude($locationLatitude)
    {
        $this->locationLatitude = $locationLatitude;
    }

    /**
     * @return mixed
     */
    public function getLocationLongitude()
    {
        return $this->locationLongitude;
    }

    /**
     * @param mixed $locationLongitude
     */
    public function setLocationLongitude($locationLongitude)
    {
        $this->locationLongitude = $locationLongitude;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getOccurring()
    {
        return $this->occurring;
    }

    /**
     * @param mixed $occurring
     */
    public function setOccurring($occurring)
    {
        $this->occurring = $occurring;
    }

    /**
     * @return mixed
     */
    public function getAttendingUserList()
    {
        return $this->attendingUserList;
    }

    /**
     * @param mixed $attendingUserList
     */
    public function setAttendingUserList($attendingUserList)
    {
        $this->attendingUserList = $attendingUserList;
    }

    /**
     * @return mixed
     */
    public function getOccurringDate()
    {
        return $this->occurringDate;
    }

    /**
     * @param mixed $occurringDate
     */
    public function setOccurringDate($occurringDate)
    {
        $this->occurringDate = $occurringDate;
    }

    /**
     * @return User
     */
    public function getCreatedByUser()
    {
        return $this->createdByUser;
    }

    /**
     * @param mixed $createdByUser
     */
    public function setCreatedByUser($createdByUser)
    {
        $this->createdByUser = $createdByUser;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param mixed $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return mixed
     */
    public function getNotAttendingUserList()
    {
        return $this->notAttendingUserList;
    }

    /**
     * @param mixed $notAttendingUserList
     */
    public function setNotAttendingUserList($notAttendingUserList)
    {
        $this->notAttendingUserList = $notAttendingUserList;
    }

    /**
     * @return mixed
     */
    public function getConcluding()
    {
        return $this->concluding;
    }

    /**
     * @param mixed $concluding
     */
    public function setConcluding($concluding)
    {
        $this->concluding = $concluding;
    }



}