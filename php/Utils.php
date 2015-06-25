<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 20/06/15
 * Time: 19:50
 */
class Utils
{
    /**
     * This function will open, get the size of, read and close a file checking every time whether it worked and output
     * an error if it did not. It will echo the JSON encoded error in the correct style before returning 'false' on failure.
     *
     * @param $path string The path to the file
     * @return bool|string The files data on success or false on error.
     */
    public static function correctlyLoadFile($path)
    {
        $handle = fopen($path, "r");
        if ($handle === false) {
            return array(false, "Opening the file failed.");
        }

        $size = filesize($path);
        if ($handle === false) {
            return array(false, "Getting the size of the file failed.");
        }

        $data = fread($handle, $size);
        if ($handle === false) {
            return array(false, "Reading the file failed.");
        }

        $result = fclose($handle);
        if ($result === false) {
            return array(false, "Closing the file failed.");
        }

        return $data;
    }
}