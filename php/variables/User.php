<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 27/05/15
 * Time: 21:30
 */

include_once("Permission.php");
use \Michelf\Markdown;

class User
{

    /**
     * @var int This stores the database ID of the user.
     */
    private $id;
    /**
     * @var string The username of the user.
     */
    private $username;
    /**
     * @var string The users first name
     */
    private $nameFirst;
    /**
     * @var string The users middle name
     */
    private $nameMiddle;
    /**
     * @var string The users last name
     */
    private $nameLast;
    /**
     * @var string The 'encrypted' copy of the users email address
     */
    private $contactEmail;
    /**
     * @var string The 'encrypted' copy of the users telephone number.
     */
    private $contactTelephone;
    /**
     * @var string The 'encrypted' copy of the users address
     */
    private $contactAddress;
    /**
     * @var string The description that the user has supplied about themselves.
     */
    private $descriptionPersonal;
    /**
     * @var string The description that the user has supplied about what they would offer to the hackspace
     */
    private $descriptionOffer;
    /**
     * @var string The description that the user has supplied about what they would use the hackspace to achieve.
     */
    private $descriptionUse;
    /**
     * @var string The profile image of the user (Set to null if they don't have one)
     */
    private $imageProfile;
    /**
     * @var string The header image of the user (Set to null if they don't have one)
     */
    private $imageHeader;
    /**
     * @var string The permissions the user currently possesses.
     */
    private $permissions;
    /**
     * @var bool Whether the user wants their full name to remain private
     */
    private $privateFullName;
    /**
     * @var bool Whether the user wants their email to remain private
     */
    private $privateEmail;
    /**
     * @var bool Whether the user wants their phone number to remain private
     */
    private $privatePhone;
    /**
     * @var bool Whether the user wants their address to remain private
     */
    private $privateAddress;
    /**
     * @var string The users hash containing the hash salt and iterations of their password.
     */
    private $hash;

    /**
     * @var string The plaintext copy of their password. This should only be populated from {@link User::createFromID()} on signup.
     */
    private $password;

    /**
     * @var array(Permission) The array of permission objects.
     */
    private $permissionsArray;
    /**
     * @var string the 'decrypted' copy of the users email address
     */
    private $decryptedEmail;
    /**
     * @var string the 'decrypted' copy of the users telephone number
     */
    private $decryptedTelephone;
    /**
     * @var string the 'decrypted' copy of the users address
     */
    private $decryptedAddress;

    /**
     * @var bool whether the users account is active.
     */
    private $active;

    /**
     * The default constructor is set to private so it can't be instantiated from another class.
     */
    private function __construct()
    {
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

        $select = "SELECT * FROM `users` WHERE `id`=?";

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

        $instance = new User();
        $instance->id = $record['id'];
        $instance->username = $record['username'];
        $instance->nameFirst = $record['name_first'];
        $instance->nameMiddle = $record['name_middle'];
        $instance->nameLast = $record['name_last'];
        $instance->contactEmail = $record['contact_email'];
        $instance->contactTelephone = $record['contact_telephone'];
        $instance->contactAddress = $record['contact_address'];
        $instance->descriptionPersonal = $record['description_personal'];
        $instance->descriptionOffer = $record['description_offer'];
        $instance->descriptionUse = $record['description_use'];
        $instance->imageProfile = $record['image_profile'];
        $instance->imageHeader = $record['image_header'];
        $instance->permissions = $record['permissions'];
        $instance->hash = $record['hash'];
        $instance->privateFullName = $record['private_full_name'];
        $instance->privateEmail = $record['private_email'];
        $instance->privatePhone = $record['private_phone'];
        $instance->privateAddress = $record['private_address'];
        $instance->active = $record['active'] == 1;

        $instance = User::generatePermissions($instance, $mysqlConnection);
        $instance = User::stripHTML($instance);
        $deobfuscatedData = SecurityUtils::deobfuscateUserDetails($instance);

        $instance->decryptedEmail = $deobfuscatedData[0];
        $instance->decryptedAddress = $deobfuscatedData[1];
        $instance->decryptedTelephone = $deobfuscatedData[2];

        return array(true, 200, "Success", $instance);
    }

    /**
     * @param User $instance The instance of the user class
     * @return User the HTML injection safe instance of the User class
     */
    private static function stripHTML($instance){
        $instance->id = htmlentities($instance->id);
        $instance->username = htmlentities($instance->username);
        $instance->nameFirst = htmlentities($instance->nameFirst);
        $instance->nameMiddle = htmlentities($instance->nameMiddle);
        $instance->nameLast = htmlentities($instance->nameLast);
        $instance->contactEmail = htmlentities($instance->contactEmail);
        $instance->contactTelephone = htmlentities($instance->contactTelephone);
        $instance->contactAddress = htmlentities($instance->contactAddress);
        $instance->descriptionPersonal = htmlentities($instance->descriptionPersonal);
        $instance->descriptionOffer = htmlentities($instance->descriptionOffer);
        $instance->descriptionUse = htmlentities($instance->descriptionUse);
        $instance->imageProfile = htmlentities($instance->imageProfile);
        $instance->imageHeader = htmlentities($instance->imageHeader);
        $instance->permissions = htmlentities($instance->permissions);
        $instance->privateFullName = htmlentities($instance->privateFullName);
        $instance->privateEmail = htmlentities($instance->privateEmail);
        $instance->privatePhone = htmlentities($instance->privatePhone);
        $instance->privateAddress = htmlentities($instance->privateAddress);

        $instance->descriptionUse = MarkDown::defaultTransform($instance->descriptionUse);
        $instance->descriptionOffer = MarkDown::defaultTransform($instance->descriptionOffer);
        $instance->descriptionPersonal = MarkDown::defaultTransform($instance->descriptionPersonal);

        return $instance;
    }


    /**
     * @param User $instance
     * @param MySQLConnection $mysqlConnection
     * @return User
     */
    private static function generatePermissions($instance, $mysqlConnection){
        $permissionsAsInts = explode(",", $instance->getPermissions());
        $permissionsAsObjects = array();
        if($permissionsAsInts == null || count($permissionsAsInts) <= 0){
            $instance->setPermissionsArray(array());
            return $instance;
        }

        foreach($permissionsAsInts as $permission){
            $request = Permission::createFromID($permission, $mysqlConnection);
            if($request[0]) {
                array_push($permissionsAsObjects, $request[3]);
            }
        }

        $instance->setPermissionsArray($permissionsAsObjects);
        return $instance;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getNameFirst()
    {
        return $this->nameFirst;
    }

    /**
     * @param string $nameFirst
     */
    public function setNameFirst($nameFirst)
    {
        $this->nameFirst = $nameFirst;
    }

    /**
     * @return string
     */
    public function getNameMiddle()
    {
        return $this->nameMiddle;
    }

    /**
     * @param string $nameMiddle
     */
    public function setNameMiddle($nameMiddle)
    {
        $this->nameMiddle = $nameMiddle;
    }

    /**
     * @return string
     */
    public function getNameLast()
    {
        return $this->nameLast;
    }

    /**
     * @param string $nameLast
     */
    public function setNameLast($nameLast)
    {
        $this->nameLast = $nameLast;
    }

    /**
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * @param string $contactEmail
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
    }

    /**
     * @return string
     */
    public function getContactTelephone()
    {
        return $this->contactTelephone;
    }

    /**
     * @param string $contactTelephone
     */
    public function setContactTelephone($contactTelephone)
    {
        $this->contactTelephone = $contactTelephone;
    }

    /**
     * @return string
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * @param string $contactAddress
     */
    public function setContactAddress($contactAddress)
    {
        $this->contactAddress = $contactAddress;
    }

    /**
     * @return string
     */
    public function getDescriptionPersonal()
    {
        return $this->descriptionPersonal;
    }

    /**
     * @param string $descriptionPersonal
     */
    public function setDescriptionPersonal($descriptionPersonal)
    {
        $this->descriptionPersonal = $descriptionPersonal;
    }

    /**
     * @return string
     */
    public function getDescriptionOffer()
    {
        return $this->descriptionOffer;
    }

    /**
     * @param string $descriptionOffer
     */
    public function setDescriptionOffer($descriptionOffer)
    {
        $this->descriptionOffer = $descriptionOffer;
    }

    /**
     * @return string
     */
    public function getDescriptionUse()
    {
        return $this->descriptionUse;
    }

    /**
     * @param string $descriptionUse
     */
    public function setDescriptionUse($descriptionUse)
    {
        $this->descriptionUse = $descriptionUse;
    }

    /**
     * @return string
     */
    public function getImageProfile()
    {
        return $this->imageProfile;
    }

    /**
     * @param string $imageProfile
     */
    public function setImageProfile($imageProfile)
    {
        $this->imageProfile = $imageProfile;
    }

    /**
     * @return string
     */
    public function getImageHeader()
    {
        return $this->imageHeader;
    }

    /**
     * @param string $imageHeader
     */
    public function setImageHeader($imageHeader)
    {
        $this->imageHeader = $imageHeader;
    }

    /**
     * @return bool
     */
    public function isPrivateFullName()
    {
        return $this->privateFullName;
    }

    /**
     * @param bool $privateFullName
     */
    public function setPrivateFullName($privateFullName)
    {
        $this->privateFullName = $privateFullName;
    }

    /**
     * @return bool
     */
    public function isPrivateEmail()
    {
        return $this->privateEmail;
    }

    /**
     * @param bool $privateEmail
     */
    public function setPrivateEmail($privateEmail)
    {
        $this->privateEmail = $privateEmail;
    }

    /**
     * @return bool
     */
    public function isPrivatePhone()
    {
        return $this->privatePhone;
    }

    /**
     * @param bool $privatePhone
     */
    public function setPrivatePhone($privatePhone)
    {
        $this->privatePhone = $privatePhone;
    }

    /**
     * @return boolean
     */
    public function isPrivateAddress()
    {
        return $this->privateAddress;
    }

    /**
     * @param boolean $privateAddress
     */
    public function setPrivateAddress($privateAddress)
    {
        $this->privateAddress = $privateAddress;
    }

    /**
     * @return string
     */
    public function getDecryptedEmail()
    {
        return $this->decryptedEmail;
    }

    /**
     * @param string $decryptedEmail
     */
    public function setDecryptedEmail($decryptedEmail)
    {
        $this->decryptedEmail = $decryptedEmail;
    }

    /**
     * @return string
     */
    public function getDecryptedTelephone()
    {
        return $this->decryptedTelephone;
    }

    /**
     * @param string $decryptedTelephone
     */
    public function setDecryptedTelephone($decryptedTelephone)
    {
        $this->decryptedTelephone = $decryptedTelephone;
    }

    /**
     * @return string
     */
    public function getDecryptedAddress()
    {
        return $this->decryptedAddress;
    }

    /**
     * @param string $decryptedAddress
     */
    public function setDecryptedAddress($decryptedAddress)
    {
        $this->decryptedAddress = $decryptedAddress;
    }

    /**
     * @return string
     */
    public function getRealUserProfile(){
        return $this->getImageProfile() == null ? "/CHG/res/images/base/user/profile.png" : $this->getImageProfile();
    }

    /**
     * @return string
     */
    public function getRealUserHeader(){
        return $this->getImageHeader() == null ? "/CHG/res/images/base/user/header.png" : $this->getImageHeader();
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
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
     * @param MySQLConnection $mysqlConnection
     * @return array
     */
    public function insertUserIntoDatabase($mysqlConnection){
        if (!$mysqlConnection->getConnected()) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NOT_CONNECTED'], "Not connected");
        }

        //Create the insert statement to add a gathering with each value set to ? so we can bind_params later
        $insert = "INSERT INTO `users` ( `contact_address`, `contact_email`, `contact_telephone`, `description_offer`, `description_personal`, `description_use`, `image_header`, `image_profile`, `name_first`, `name_last`, `name_middle`, `private_email`, `private_full_name`, `private_phone`, `username`, `hash`)
VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );";

        //Prepare the statement using the connected copy of mysqli
        $preparedStatement = mysqli_prepare($mysqlConnection->getMysqli(), $insert);
        //Bind the parameters in the order they are listed in $insert.
        $preparedStatement->bind_param("sssssssssssiiiss", $this->getContactAddress(), $this->getContactEmail(), $this->getContactTelephone(), $this->getDescriptionOffer(), $this->getDescriptionPersonal(), $this->getDescriptionUse(), $this->getImageHeader(), $this->getImageProfile(), $this->getNameFirst(), $this->getNameLast(), $this->getNameMiddle(), $this->isPrivateEmail(), $this->isPrivateFullName(), $this->isPrivatePhone(), $this->getUsername(), create_hash($this->getPassword()));

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
     * @param int $username
     * @param MySQLConnection $mysqlConnection
     * @return array
     */
    public static function getUserID($username, $mysqlConnection){
        if (!$mysqlConnection->getConnected()) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NOT_CONNECTED'], "Not connected", null);
        }

        $select = "SELECT `id` FROM `users` WHERE `username`=?";

        $preparedStatement = mysqli_prepare($mysqlConnection->getMysqli(), $select);
        $preparedStatement->bind_param("s", $username);

        $result = $preparedStatement->execute();

        if (!$result) {
            return array(false, $preparedStatement->errno, $preparedStatement->error, null);
        }

        $rows = $preparedStatement->get_result();

        if ($rows->num_rows > 1) {
            return array(false, $mysqlConnection->getErrorCodes()['ERR_TOO_MANY_RESULTS'], "Too many results", null);
        }

        if($rows->num_rows == 0){
            return array(false, $mysqlConnection->getErrorCodes()['ERR_NO_RESULTS'], "No results", null);
        }

        $record = $rows->fetch_array(MYSQLI_BOTH);
        return array(true, 200, "Success", $record['id']);
    }

    /**
     * This function will create the user from a list of given values
     * @param string $username
     * @param string $nameFirst
     * @param string $nameMiddle
     * @param string $nameLast
     * @param string $descriptionPersonal
     * @param string $descriptionOffer
     * @param string $descriptionUser
     * @param string $imageProfile
     * @param string $imageHeader
     * @param bool $privateFullName
     * @param bool $privateEmail
     * @param bool $privateTelephone
     * @param bool $privateAddress
     * @param string $decryptedEmail
     * @param string $decryptedTelephone
     * @param string $decryptedAddress
     * @return User
     */
    public static function createFromValues($username, $nameFirst, $nameMiddle, $nameLast, $descriptionPersonal, $descriptionOffer, $descriptionUser, $imageProfile, $imageHeader, $privateFullName, $privateEmail, $privateTelephone, $privateAddress, $decryptedEmail, $decryptedTelephone, $decryptedAddress)
    {
        $instance = new User();
        $instance->username = $username;
        $instance->nameFirst = $nameFirst;
        $instance->nameMiddle = $nameMiddle;
        $instance->nameLast = $nameLast;
        $instance->descriptionPersonal = $descriptionPersonal;
        $instance->descriptionOffer = $descriptionOffer;
        $instance->descriptionUse = $descriptionUser;
        $instance->imageProfile = $imageProfile;
        $instance->imageHeader = $imageHeader;
        $instance->privateFullName = $privateFullName;
        $instance->privateEmail = $privateEmail;
        $instance->privatePhone = $privateTelephone;
        $instance->privateAddress = $privateAddress;
        $instance->decryptedEmail = $decryptedEmail;
        $instance->decryptedTelephone = $decryptedTelephone;
        $instance->decryptedAddress = $decryptedAddress;

        $obfuscatedData = SecurityUtils::obfuscateUserDetails($instance);
        $instance->contactEmail = $obfuscatedData[0];
        $instance->contactAddress = $obfuscatedData[1];
        $instance->contactTelephone = $obfuscatedData[2];
        return $instance;
    }

    /**
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return array
     */
    public function getPermissionsArray()
    {
        return $this->permissionsArray;
    }

    /**
     * @param array $permissionsArray
     */
    public function setPermissionsArray($permissionsArray)
    {
        $this->permissionsArray = $permissionsArray;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }



}