<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 17/06/15
 * Time: 19:07
 */
class GatheringsDataModel extends DatabaseModel
{
    const GATHERINGS = "gatherings";
    const PAST_GATHERINGS = "past_gatherings";

    //region SELECT BASED QUERIES
    /**
     * This function will retrieve all the gatherings in the database and return them.
     *
     * @param $table string The table to execute the query on (Use the constants)
     * @returns mixed Returns an array of Gathering on success or false on error. Errors are available from DatabaseModel::GetErrorMessage().
     */
    public function getAllGatherings($table)
    {
        $sql = "SELECT * FROM " . mysqli_real_escape_string($this->getMysqli(), $table) . ";";
        $stmt = $this->getMysqli()->prepare($sql);
        if ($stmt === false) {
            $this->setCustomError("When attempting to prepare the statement the server encountered an error. Please try again later. If the error persists please report it to the webmaster with the following details: Code: " . $this->getMysqli()->errno . " Error: " . $this->getMysqli()->error);
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        $result = $stmt->execute();
        if ($result === false) {
            $this->setCustomError("When attempting to execute the statement the server encountered an error. Please try again later. If the error persists please report it to the webmaster with the following details: Code: " . $stmt->errno . " Error: " . $stmt->error);
            $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
            return false;
        }

        $records = $stmt->get_result();
        if ($records === false) {
            $this->setCustomError("When attempting to get the results from the statement the server encountered an error. Please try again later. If the error persists please report it to the webmaster with the following details: Code: " . $stmt->errno . " Error: " . $stmt->error);
            $this->setError("ERR-STATEMENT-RESULT-FAILED");
            return false;
        }

        $gatherings = array();
        for ($i = 0; $i < $records->num_rows; $i++) {
            $row = $records->fetch_array();

            $gathering = Gathering::createFromValues(
                $row['accept_timeout'],
                $row['active'],
                $row['attending'],
                $row['created'],
                $row['created_by'],
                $row['description'],
                $row['id'],
                $row['location_address'],
                $row['location_latitude'],
                $row['location_longitude'],
                $row['name'],
                $row['not_attending'],
                $row['occurring'],
                $row['concludes'],
                $row['recurring']
            );

            array_push($gatherings, $gathering);
        }

        return $gatherings;
    }

    public function getGatherings($conditions, $table)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array('accept_timeout', 'active', 'attending', 'created', 'created_by', 'description', 'id',
            'location_address', 'location_latitude', 'location_longitude', 'name', 'not_attending', 'occurring', 'recurring');

        $conditions = $this->filterExplicitHashData($conditions, $columns);

        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            //Then just return an empty array
            return array();
        }

        //Create the base SQL string (ignore the error - PhpStorm is trying to be smart with SQL suggestions and this
        // is not a valid SQL query.)
        $sqlBase = "SELECT * FROM " . mysqli_real_escape_string($this->getMysqli(), $table) . " WHERE ";

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

        $gatherings = array();

        for ($i = 0; $i < $records->num_rows; $i++) {
            $row = $records->fetch_array(MYSQLI_BOTH);
            $gathering = Gathering::createFromValues(
                $row['accept_timeout'],
                $row['active'],
                $row['attending'],
                $row['created'],
                $row['created_by'],
                $row['description'],
                $row['id'],
                $row['location_address'],
                $row['location_latitude'],
                $row['location_longitude'],
                $row['name'],
                $row['not_attending'],
                $row['occurring'],
                $row['concludes'],
                $row['recurring']
            );

            array_push($gatherings, $gathering);
        }

        return $gatherings;
    }

    public function getGatheringByID($id, $table)
    {
        //Create the base SQL string (ignore the error - PhpStorm is trying to be smart with SQL suggestions and this
        // is not a valid SQL query.)
        $sqlBase = "SELECT * FROM " . mysqli_real_escape_string($this->getMysqli(), $table) . " WHERE `id`=?;";

        $stmt = $this->getMysqli()->prepare($sqlBase);
        if ($stmt === false) {
            $this->setCustomError("When trying to prepare the query the server encountered an error. Please try again
             later. If the error persists please report it to the webmaster and supply the following information:
             <br>Code: " . $this->getMysqli()->errno . "<br>Error: '" . $this->getMysqli()->error . "'.");
            return false;
        }

        $bind = $stmt->bind_param("i",
            $id
        );

        if (!$bind) {
            $this->setCustomError("When trying to bind parameters to the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-BIND-FAILED");
            return false;
        }

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

        if ($records->num_rows == 0) {
            $this->setCustomError("No gatherings could be found by the id '" . $id . "' in the table '" . $table . "'
            .");
            $this->setError("ERR-STATEMENT-CUSTOM-NO-RESULTS");
            return false;
        }

        $row = $records->fetch_array(MYSQLI_BOTH);
        $gathering = Gathering::createFromValues(
            $row['accept_timeout'],
            $row['active'],
            $row['attending'],
            $row['created'],
            $row['created_by'],
            $row['description'],
            $row['id'],
            $row['location_address'],
            $row['location_latitude'],
            $row['location_longitude'],
            $row['name'],
            $row['not_attending'],
            $row['occurring'],
            $row['concludes'],
            $row['recurring']
        );

        return $gathering;
    }
    //endregion

    //region INSERT BASED QUERIES
    public function createGatheringFromDetails($data, $table)
    {
        $columns = array("accept_timeout", "active", "attending", "concludes", "created", "created_by",
            "description", "id", "location_address", "location_latitude", "location_longitude", "name",
            "not_attending", "occurring", "recurring");

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

        $base = "INSERT INTO `" . mysqli_real_escape_string($this->getMysqli(), $table) . "` (" . $fieldsString . ")
        VALUES (";
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
     * @param $gathering Gathering
     */
    public function createGathering($gathering, $table)
    {
        $insert = "INSERT INTO `" . mysqli_real_escape_string($this->getMysqli(), $table) . "` ( `accept_timeout`,
        `active`, `attending`, `concludes`, `created`, `created_by`, `description`, `location_address`,
        `location_latitude`, `location_longitude`, `name`, `not_attending`, `occurring`, `recurring`) VALUES ( ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ? );";

        $stmt = $this->getMysqli()->prepare($insert);
        if ($stmt === false) {
            $this->setCustomError("When trying to prepare the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $this->getMysqli()->errno . ".<br>Error: '" . $this->getMysqli()->error . "'.");
            $this->setError("ERR-STATEMENT-PREPARE-FAILED");
            return false;
        }

        $bind = $stmt->bind_param("iisiiissssssis",
            $gathering->getAcceptTimeout(),
            $gathering->getActive(),
            $gathering->getAttending(),
            $gathering->getConcluding(),
            $gathering->getCreated(),
            $gathering->getCreatedBy(),
            $gathering->getDescription(),
            $gathering->getLocationAddress(),
            $gathering->getLocationLatitude(),
            $gathering->getLocationLongitude(),
            $gathering->getName(),
            $gathering->getNotAttending(),
            $gathering->getOccurring(),
            $gathering->getRecurring()
        );

        if (!$bind) {
            $this->setCustomError("When trying to bind parameters to the query the server encountered an error.
         Please try again later. If the error persists please report it to the webmaster and supply the following
         information: <br>Code: " . $stmt->errno . ".<br>Error: '" . $stmt->error . "'.");
            $this->setError("ERR-STATEMENT-BIND-FAILED");
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
    //endregion

    //region UPDATE BASED QUERIES
    public function updateGathering($updates, $conditions, $table)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("accept_timeout", "active", "attending", "concludes", "created", "created_by",
            "description", "id", "location_address", "location_latitude", "location_longitude", "name",
            "not_attending", "occurring", "recurring");

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

        $base = "UPDATE `" . mysqli_real_escape_string($this->getMysqli(), $table) . "` SET %1 WHERE %2;";
        $stmt = $this->bindArrayBasedParamsTwice($updates, $conditions, $base);

        if ($stmt === false) return false;

        /** @var $stmt mysqli_stmt */
        $result = $stmt->execute();
        if (!$result) $this->setError("ERR-STATEMENT-EXECUTE-FAILED");
        return $result;
    }
    //endregion

    //region DELETE BASED QUERIES
    public function deleteGatherings($conditions, $table)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array("accept_timeout", "active", "attending", "concludes", "created", "created_by",
            "description", "id", "location_address", "location_latitude", "location_longitude", "name",
            "not_attending", "occurring");

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
        $sqlBase = "DELETE FROM `" . mysqli_real_escape_string($this->getMysqli(), $table) . "` WHERE ";

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

    //region MISC FUNCTIONS
    public function switchTable($id, $from, $to)
    {
        $gatheringResponse = $this->getGatheringByID($id, $from);
        if ($gatheringResponse === false) {
            return false;
        }


        $deleteResponse = $this->deleteGatherings(array("id" => $id), $from);
        if ($deleteResponse === false) {
            return false;
        }

        $insertResponse = $this->createGathering($gatheringResponse, $to);
        if ($insertResponse === false) {
            return false;
        }

        return true;
    }
    //endregion

}