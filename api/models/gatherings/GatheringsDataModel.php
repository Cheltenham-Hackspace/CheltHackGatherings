<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 17/06/15
 * Time: 19:07
 */
class GatheringsDataModel extends DatabaseModel
{
    /**
     * This function will retrieve all the gatherings in the database and return them.
     *
     * @returns mixed Returns an array of Gathering on success or false on error. Errors are available from DatabaseModel::GetErrorMessage().
     */
    public function getAllGatherings()
    {
        $sql = "SELECT * FROM `gatherings`;";
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
                $row['occurring']
            );

            array_push($gatherings, $gathering);
        }

        return $gatherings;
    }

    public function getGatherings($conditions)
    {
        //List of allowed columns. There could be a better way to do this but for now I am going to leave it.
        //TODO Research better ways of doing this.
        $columns = array('accept_timeout', 'active', 'attending', 'created', 'created_by', 'description', 'id',
            'location_address', 'location_latitude', 'location_longitude', 'name', 'not_attending', 'occurring');

        $conditions = $this->filterExplicitHashData($conditions, $columns);

        //If the number of conditions is 0 after we remove malicious ones
        if (count($conditions) == 0) {
            //Then just return an empty array
            return array();
        }

        //Create the base SQL string (ignore the error - PhpStorm is trying to be smart with SQL suggestions and this
        // is not a valid SQL query.)
        $sqlBase = "SELECT * FROM `gatherings` WHERE ";

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
                $row['occurring']
            );

            array_push($gatherings, $gathering);
        }

        return $gatherings;
        //endregion
    }
}