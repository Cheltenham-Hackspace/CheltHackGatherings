<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 29/05/15
 * Time: 00:21
 */

class Permission {

    private $id;
    private $name;
    private $description;

    /**
     * @return array Permission An array of permissions.
     */
    public static function getDefaultPermissions()
    {
        return array(
            'PERM_ALL_ADMIN' => Permission::createFromValues(1, "PERM_ALL_ADMIN", "All permissions. Should only be given to admins and developers."),
            'PERM_GATHERING_CREATE' => Permission::createFromValues(2, "PERM_GATHERING_CREATE", "The permissions to create gatherings on the gatherings system."),
            'PERM_GATHERING_EDIT' => Permission::createFromValues(3, 'PERM_GATHERING_EDIT', "The permission to edit the details of a gathering"),
            'PERM_GATHERING_REMOVE' => Permission::createFromValues(4, 'PERM_GATHERING_REMOVE', "The permission to remove a gathering from the system."),
            'PERM_GATHERING_MODIFY_ALL' => Permission::createFromValues(5, 'PERM_GATHERING_MODIFY_ALL', "A group permission containing [2=PERM_GATHERING_CREATE, 3=PERM_GATHERING_EDIT, 4=PERM_GATHERING_REMOVE, 5=PERM_GATHERING_MODIFY_ALL]"),
            'PERM_USER_CREATE' => Permission::createFromValues(6, 'PERM_USER_CREATE', "The permission to create a user manually using the online form."),
            'PERM_USER_EDIT' => Permission::createFromValues(7, 'PERM_USER_EDIT', "The permission to edit the details of a user (Not personal details such as emails, phone numbers and addresses)"),
            'PERM_USER_REMOVE' => Permission::createFromValues(8, 'PERM_USER_REMOVE', "The permission to remove a user from the system"),
            'PERM_USER_MODIFY_ALL' => Permission::createFromValues(9, 'PERM_USER_MODIFY_ALL', "A group permission containing [6=PERM_USER_CREATE, 7=PERM_USER_EDIT, 8=PERM_USER_REMOVE]"),
            'PERM_USER_EDIT_ADMIN' => Permission::createFromValues(10, 'PERM_USER_EDIT_ADMIN', "The permission to edit the details of a user (Including emails, phone number, addresses and to reset passwords)"),
        );
    }

    public static function createFromValues($id, $name, $description){
        $instance = new Permission();
        $instance->setId($id);
        $instance->setName($name);
        $instance->setDescription($description);
        return $instance;
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

        $select = "SELECT * FROM `permissions` WHERE `id`=?";

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

        $instance = new Permission();
        $instance->setId($record['id']);
        $instance->setName($record['name']);
        $instance->setDescription($record['description']);

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



}