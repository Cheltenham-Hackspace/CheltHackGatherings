<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 13/06/15
 * Time: 13:38
 */

class TimelineEvent {

    private $id;
    private $timestamp;
    private $name;
    private $description;
    private $icon;
    private $color;
    private $author;
    private $side;

    private function __construct()
    {
    }


    /**
     * @param int $id
     * @param MySQLConnection $mysqlConnection
     * @returns array(bool, int, string, TimelineEvent)
     */
    public static function generateFromID($id, $mysqlConnection){
        $stmt = $mysqlConnection->getMysqli()->prepare("SELECT * FROM `timeline` WHERE `id`=?;");
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

        $record = $rows->fetch_array(MYSQLI_NUM)[0];

        $instance = new TimelineEvent();
        $instance->setId($record['id']);
        $instance->setTimestamp($record['timestamp']);
        $instance->setName($record['name']);
        $instance->setDescription($record['description']);
        $instance->setIcon($record['icon']);
        $instance->setColor($record['color']);
        $instance->setAuthor($record['author']);
        $instance->setSide($record['side']);

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
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getSide()
    {
        return $this->side;
    }

    /**
     * @param mixed $side
     */
    public function setSide($side)
    {
        $this->side = $side;
    }



}