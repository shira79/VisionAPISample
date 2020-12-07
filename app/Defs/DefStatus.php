<?php
namespace App\Defs;

/**
 * Class DefPart
 * @package App\Defs
 */
class DefStatus {
    /**
     * 未対応
     */
    const UNPROCESSED_STATUS_CODE = "01";
    const UNPROCESSED_STATUS_NAME = "未対応";

    /**
     * 処理済
     */
    const PROCESSED_STATUS_CODE = "02";
    const PROCESSED_STATUS_NAME = "処理済";


    const STATUS_LIST = [
        self::UNPROCESSED_STATUS_CODE   =>self::UNPROCESSED_STATUS_NAME,
        self::PROCESSED_STATUS_CODE =>self::PROCESSED_STATUS_NAME,
    ];

    public static function getStatusName($code){
        return self::STATUS_LIST[$code];
    }
}

