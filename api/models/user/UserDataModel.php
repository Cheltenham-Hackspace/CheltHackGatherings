<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 15/06/15
 * Time: 19:26
 */
class UserDataModel extends DatabaseModel
{


    //region SELECT BASED QUERIES
    public function getAllUsers()
    {
        $sql = "SELECT * FROM `users`;";
        $stmt = $this->getMysqli()->prepare($sql);

        if ($stmt === false) {
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        $result = $stmt->execute();

        if ($result === false) {
            $this->setError("MQI:[" . $stmt->errno . "]: " . $stmt->error);
            return false;
        }

        $records = $stmt->get_result();

        if ($records === false) {
            $this->setCustomError("When trying to get the result of the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-RESULT-FAILED");
            return false;
        }

        $users = array();

        foreach ($records as $row) {
            $user = User::createFromValues(
                $row['username'],
                $row['name_first'],
                $row['name_middle'],
                $row['name_last'],
                $row['description_personal'],
                $row['description_offer'],
                $row['description_use'],
                $row['image_profile'],
                $row['image_header'],
                $row['permissions'],
                $row['private_full_name'],
                $row['private_email'],
                $row['private_phone'],
                $row['private_address'],
                null,
                SecurityUtils::deobfuscateString($row['contact_email']),
                SecurityUtils::deobfuscateStringComplete($row['contact_telephone'], $row['username'], $row['name_first'],
                    $row['name_last']),
                SecurityUtils::deobfuscateStringComplete($row['contact_address'], $row['username'], $row['name_first'],
                    $row['name_last']),
                $row['active'],
                $row['unique_id']
            );
            $user->setId($row['id']);
            $user->setContactEmail($row['contact_email']);
            $user->setContactTelephone($row['contact_telephone']);
            $user->setContactAddress($row['contact_address']);
            $user->setHash($row['hash']);
            $user = $this->generatePermissions($user, $this->getMysqli());
            array_push($users, $user);
        }

        return $users;
    }

    public function getUserByID($id)
    {
        $sql = "SELECT * FROM `users` WHERE `id`=?;";
        $stmt = $this->getMysqli()->prepare($sql);

        if ($stmt === false) {
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        if ($stmt->bind_param("i", $id) === false) {
            $this->setError("ERR-STATEMENT-BIND-FAILED");
            return false;
        }

        $result = $stmt->execute();

        if ($result === false) {
            $this->setError("MQI:[" . $stmt->errno . "]: " . $stmt->error);
            return false;
        }

        $records = $stmt->get_result();

        if ($records === false) {
            $this->setCustomError("When trying to get the result of the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-RESULT-FAILED");
            return false;
        }

        if ($records->num_rows == 1) {
            $row = $records->fetch_array();
            $user = User::createFromValues(
                $row['username'],
                $row['name_first'],
                $row['name_middle'],
                $row['name_last'],
                $row['description_personal'],
                $row['description_offer'],
                $row['description_use'],
                $row['image_profile'],
                $row['image_header'],
                $row['permissions'],
                $row['private_full_name'],
                $row['private_email'],
                $row['private_phone'],
                $row['private_address'],
                null,
                SecurityUtils::deobfuscateString($row['contact_email']),
                SecurityUtils::deobfuscateStringComplete($row['contact_telephone'], $row['username'], $row['name_first'],
                    $row['name_last']),
                SecurityUtils::deobfuscateStringComplete($row['contact_address'], $row['username'], $row['name_first'],
                    $row['name_last']),
                $row['active'],
                $row['unique_id']
            );
            $user->setId($row['id']);
            $user->setContactEmail($row['contact_email']);
            $user->setContactTelephone($row['contact_telephone']);
            $user->setContactAddress($row['contact_address']);
            $user->setHash($row['hash']);
            return $this->generatePermissions($user, $this->getMysqli());
        } else {
            $this->setCustomError("When executing the query not enough or too many results were returned by the
            server. This is usually due to the ID of a user not existing. Please try again later. If the problem
            persists please report it to the webmaster.");
            $this->setError("ERR-CUSTOM-RESULT-SET-INVALID-COUNT");
            return false;
        }
    }

    public function getUsers($conditions)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("active", "contact_address", "contact_email", "contact_telephone", "description_offer",
            "description_personal", "description_use", "hash", "id", "image_header", "image_profile", "name_first",
            "name_last", "name_middle", "permissions", "private_address", "private_email", "private_full_name",
            "private_phone", "unique_id", "username");

        $conditions = $this->filterExplicitHashData($conditions, $columns);

        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            //Then just return an empty array
            return array();
        }

        //Create the base SQL string (ignore the error - PhpStorm is trying to be smart with SQL suggestions and this
        // is not a valid SQL query.)
        $sqlBase = "SELECT * FROM `users` WHERE ";

        $stmt = $this->bindArrayBasedParams($conditions, $sqlBase);
        if ($stmt === false) return false;

        /** @var $stmt mysqli_stmt */
        $result = $stmt->execute();
        if (!$result) {
            $this->setCustomError("When trying to execute the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
            return false;
        }

        $records = $stmt->get_result();
        if ($records === false) {
            $this->setCustomError("When trying to get the result of the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-RESULT-FAILED");
            return false;
        }

        $users = array();

        for ($i = 0; $i < $records->num_rows; $i++) {
            $row = $records->fetch_array(MYSQLI_BOTH);
            $user = User::createFromValues(
                $row['username'],
                $row['name_first'],
                $row['name_middle'],
                $row['name_last'],
                $row['description_personal'],
                $row['description_offer'],
                $row['description_use'],
                $row['image_profile'],
                $row['image_header'],
                $row['permissions'],
                $row['private_full_name'],
                $row['private_email'],
                $row['private_phone'],
                $row['private_address'],
                null,
                SecurityUtils::deobfuscateString($row['contact_email']),
                SecurityUtils::deobfuscateStringComplete($row['contact_telephone'], $row['username'], $row['name_first'],
                    $row['name_last']),
                SecurityUtils::deobfuscateStringComplete($row['contact_address'], $row['username'], $row['name_first'],
                    $row['name_last']),
                $row['active'],
                $row['unique_id']
            );
            $user->setId($row['id']);
            $user->setContactEmail($row['contact_email']);
            $user->setContactTelephone($row['contact_telephone']);
            $user->setContactAddress($row['contact_address']);
            $user->setHash($row['hash']);
            $user = $this->generatePermissions($user, $this->getMysqli());
            array_push($users, $user);
        }

        return $users;
    }
    //endregion

    //region INSERT BASED QUERIES
    public function createUserFromDetails($data)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("active", "contact_address", "contact_email", "contact_telephone", "description_offer",
            "description_personal", "description_use", "hash", "id", "image_header", "image_profile", "name_first",
            "name_last", "name_middle", "permissions", "private_address", "private_email", "private_full_name",
            "private_phone", "unique_id", "username");

        $conditions = $this->filterExplicitHashData($data, $columns);
        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            $this->setError("ERR-CUSTOM-FILTER-NO-CONDITIONS");
            //Then just return an empty array
            return false;
        }

        $fieldsString = "";
        $keys = array_keys($data);
        for ($i = 0; $i < count($keys); $i++) {
            $fieldsString .= "`" . $keys[$i] . "`, ";
        }
        $fieldsString = substr($fieldsString, 0, strlen($fieldsString) - 2);

        $base = "INSERT INTO `users` (" . $fieldsString . ") VALUES (";
        $stmt = $this->bindArrayBasedParamsWithAppend($data, $base, "?, ", 2, ");");

        if ($stmt === false) {
            $this->setCustomError("When trying to prepare the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $this->getMysqli()->errno . ".<br>Error: '" . $this->getMysqli()->error . "'.");
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        /** @var $stmt mysqli_stmt */
        $result = $stmt->execute();
        if (!$result) {
            $this->setCustomError("When trying to execute the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
        }
        return $result;
    }

    /**
     * This function will create a user in the database.
     * @param User $user
     * @returns bool If the insert completed successfully.
     */
    public function createUser($user)
    {
        $insert = "INSERT INTO `users` ( `active`, `contact_address`, `contact_email`, `contact_telephone`, `description_offer`,
`description_personal`, `description_use`, `hash`, `image_header`, `image_profile`, `name_first`, `name_last`,
`name_middle`, `permissions`, `private_address`, `private_email`, `private_full_name`, `private_phone`, `unique_id`,
`username`)
VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = $this->getMysqli()->prepare($insert);
        //Bind the parameters in the order they are listed in $insert.
        $preparedStatement->bind_param("isssssssssssssiiiiss",
            $user->getActive(),
            $user->getContactAddress(),
            $user->getContactEmail(),
            $user->getContactTelephone(),
            $user->getDescriptionOffer(),
            $user->getDescriptionPersonal(),
            $user->getDescriptionUse(),
            create_hash($user->getPassword()),
            $user->getImageHeader(),
            $user->getImageProfile(),
            $user->getNameFirst(),
            $user->getNameLast(),
            $user->getNameMiddle(),
            $user->getPermissions(),
            $user->isPrivateAddress(),
            $user->isPrivateEmail(),
            $user->isPrivateFullName(),
            $user->isPrivatePhone(),
            $user->getUniqueID(),
            $user->getUsername()
        );

        //Execute the query and store the result in $result
        $result = $preparedStatement->execute();

        //If the result was false (The insert failed)
        if (!$result) {
            //Then return false with the prepared statements error and errno
            $this->setCustomError("When trying to get the result of the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $preparedStatement->errno . ".<br>Error: '" . $preparedStatement->error . "'.");
            $this->setError("ERR-STATEMENT-RESULT-FAILED");
            return false;
        }

        //Otherwise return true with a success message.
        return true;
    }
    //endregion

    //region UPDATE BASED QUERIES
    public function updateUser($updates, $conditions)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("active", "contact_address", "contact_email", "contact_telephone", "description_offer",
            "description_personal", "description_use", "hash", "id", "image_header", "image_profile", "name_first",
            "name_last", "name_middle", "permissions", "private_address", "private_email", "private_full_name",
            "private_phone", "unique_id", "username");

        $conditions = $this->filterExplicitHashData($conditions, $columns);
        $updates = $this->filterExplicitHashData($updates, $columns);

        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            //Then just return an empty array
            return false;
        }

        //If the number of conditions is 0 after we remove malicious ones
        if (count($updates) == 0) {
            //Then just return an empty array
            return false;
        }

        $base = "UPDATE `users` SET %1 WHERE %2;";
        $stmt = $this->bindArrayBasedParamsTwice($updates, $conditions, $base);

        if ($stmt === false) return false;

        /** @var $stmt mysqli_stmt */
        $result = $stmt->execute();
        if (!$result) $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
        return $result;
    }
    //endregion

    //region DELETE BASED QUERIES
    public function deleteUsers($conditions)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("active", "contact_address", "contact_email", "contact_telephone", "description_offer",
            "description_personal", "description_use", "hash", "id", "image_header", "image_profile", "name_first",
            "name_last", "name_middle", "permissions", "private_address", "private_email", "private_full_name",
            "private_phone", "unique_id", "username");

        $conditions = $this->filterExplicitHashData($conditions, $columns);

        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            //Then just return an empty array
            return false;
        }

        if (in_array("%", $conditions)) {
            $this->setCustomError("The attempted query cannot contain wildcard characters. This is to stop accidental
             deletion of an entire database from a bad use of wildcards. If you are an admin you can request the
             access to the raw database if you don't have it already and edit the data from there.");
            //This is not a valid error but it should cause the error to work. I don't know if it is needed but I am
            // just going to leave it here.
            $this->setError("x");
            return false;
        }

        //Create the base SQL string (ignore the error - PhpStorm is trying to be smart with SQL suggestions and this
        // is not a valid SQL query.)
        $sqlBase = "DELETE FROM `users` WHERE ";

        $stmt = $this->bindArrayBasedParams($conditions, $sqlBase);
        if ($stmt === false) return false;

        /** @var $stmt mysqli_stmt */
        $result = $stmt->execute();
        if (!$result) {
            $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
            return false;
        }

        return true;
    }
    //endregion

    //region UTILITY FUNCTIONS

    /**
     * @param User $instance
     * @param mysqli $mysqli
     * @return User
     */
    private function generatePermissions($instance, $mysqli)
    {
        $permissionsAsInts = explode(",", $instance->getPermissions());
        $permissionsAsObjects = array();
        if ($permissionsAsInts == null || count($permissionsAsInts) <= 0) {
            $instance->setPermissionsArray(array());
            return $instance;
        }

        foreach ($permissionsAsInts as $permission) {
            $sql = "SELECT * FROM `permissions` WHERE `id`=?;";
            $stmt = $mysqli->prepare($sql);
            if ($stmt === false) {
                continue;
            }

            if ($stmt->bind_param("i", $permission) === false) {
                continue;
            }

            $result = $stmt->execute();
            if (!$result) {
                continue;
            }

            $records = $stmt->get_result();
            if ($records === false) {
                continue;
            }

            $row = $records->fetch_array(MYSQLI_BOTH);
            if ($row == null) {
                continue;
            }

            $perm = Permission::createFromValues($row['id'], $row['name'], $row['description']);
            array_push($permissionsAsObjects, $perm);
        }

        $instance->setPermissionsArray($permissionsAsObjects);
        return $instance;
    }

    //endregion
}