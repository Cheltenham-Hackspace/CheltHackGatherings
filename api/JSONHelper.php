<?php

/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 16/06/15
 * Time: 19:59
 */
class JSONHelper
{

    /**
     * @param DatabaseModel $model
     * @returns string JSON data
     */
    public static function convertErrorToJSON($model)
    {
        $data = array(
            "success" => false,
            "error" => array(
                "code" => $model->getError(),
                "message" => $model->GetErrorString($model->getError())
            )
        );
        return json_encode($data);
    }

}