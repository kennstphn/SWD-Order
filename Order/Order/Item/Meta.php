<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 8/31/16
 * Time: 11:23 AM
 */

namespace SWD\Order\Item;


class Meta
{

    public $metaId;
    public $itemId;
    public $metakey;
    public $metavalue;

    public function create_msql_storageTable($pdoConnection, $tableName = 'item_meta'){

        $sql = 'CREATE TABLE '.$tableName.' (
            `meta_id` INT (11) NOT NULL AUTO_INCREMENT,
            `item_id` INT (11) NOT NULL,
            `meta_key` varchar (128) NOT NULL,
            `meta_value` varchar (128) DEFAULT NULL,
            `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`meta_id`)
        ) AUTO_INCREMENT = 0 DEFAULT CHARSET=utf8;';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute();

        if($success){return true;}
        return false;

    }

    public function add_to_mysql_storageTable($pdoConnection, $tableName='meta'){

        $sql = 'INSERT INTO '.$tableName.' (item_id,meta_key,meta_value)
        VALUES (:itemId,:metaKey,:metaValue)';

        $query = $pdoConnection->prepare($sql);
        $success = $query->execute(array(
            ':itemId'=>$this->itemId,
            ':metaKey'=>$this->metakey,
            ':metaValue'=>$this->metavalue
        ));

        if($success){return true;}

        return false;

    }

    public function update_mysql_storageTable($pdoConnection, $tableName = 'meta'){

        $pdoConnection = new \PDO('','','');

        $sql = 'UPDATE '.$tableName.' SET 
            item_id = :itemId,
            meta_key = :metaKey, 
            meta_value = :metaValue
            WEHRE meta_id = :metaId';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(
            ':itemId'=>$this->itemId,
            ':metaKey'=>$this->metakey,
            ':metaValue'=>$this->metavalue,
            ':metaId'=>$this->metaId
        ));

        return $success;

    }



}