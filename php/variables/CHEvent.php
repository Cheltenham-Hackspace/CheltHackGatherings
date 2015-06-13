<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 12/06/15
 * Time: 19:21
 */

class CHEvent {

    private $id;
    private $timestamp;
    private $message;
    private $description;
    private $icon;
    private $user;

    private function __construct()
    {
    }

    /**
     * @param int $id
     * @param MySQLConnection $mysqlConnection
     * @returns array(bool, int, string, CHEvent)
     */
    public static function createFromID($id, $mysqlConnection){
        $stmt = $mysqlConnection->getMysqli()->prepare("SELECT * FROM `events` WHERE `id`=?;");
        $stmt->bind_param("i", $id);

        $result = $stmt->execute();
        if(!$result){
            return array(false, $stmt->errno, $stmt->error, null);
        }

        $rows = $stmt->get_result();

        if($rows->num_rows == 0){
            return array(false, $mysqlConnection->getErrorCodes()["ERR_NO_RESULTS"], null);
        }
        if($rows->num_rows > 1){
            return array(false, $mysqlConnection->getErrorCodes()["ERR_TOO_MANY_RESULTS"], "Too many results
            returned!", null);
        }

        $event = $rows->fetch_array(MYSQLI_NUM)[0];

        $instance = new CHEvent();
        $instance->setId($event['id']);
        $instance->setTimestamp($event['timestamp']);
        $instance->setMessage($event['message']);
        $instance->setDescription($event['description']);
        $instance->setIcon($event['icon']);
        $instance->setUser($event['user']);

        return array(true, 200, "Success", $instance);
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
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }



}