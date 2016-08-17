<?php

namespace SWD\Order;


class Item
{
    public $itemId;
    public $orderId;
    public $itemDescription;
    public $serialNumber;
    public $estimate;
    public $cost;
    public $comments;


    public function add_to_mysql_storageTable($pdoConnection, $tablename = 'items'){

        if (! $this->validate_info()){throw new \Exception('invalid info on pre-create storage check for item');}

        if (!is_string($tablename) || strpos($tablename, ' ') !== false){
            throw new \Exception('Invalid table name for saving order item');
        }

        $sql = 'INSERT INTO '.$tablename.' (order_id,item_description,serial_number,estimate,cost,comments) 
          VALUES(:orderId,:itemDescription,:serialNumber,:estimate,:cost,:comments)';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(
            ':orderId'=>$this->orderId,
            ':itemDescription'=>$this->itemDescription,
            'serialNumber'=>$this->serialNumber,
            ':estimate'=>$this->estimate,
            ':cost'=>$this->cost,
            ':comments'=>$this->comments
        ));

        if ($success == false){
            throw new \Exception('unable to save item information the database');
        }

        return true;

    }

    public function update_to_mysql_storageTable($pdoConnection,$tableName = 'items' ){

        if(!$this->validate_info()){throw new \Exception('invalid info on pre-update storage check for item');}

        $sql = 'UPDATE '.$tableName.' SET 
            order_id = :orderId,
            item_description = :itemDescription
            serial_number = :serialNumber,
            estimate = :estimate,
            cost = :cost,
            comments = :comments
            WHERE item_id = :itemId
        ';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(
            ':orderId'=>$this->orderId,
            ':itemDescription'=>$this->itemDescription,
            ':serialNumber'=>$this->serialNumber,
            ':estimate'=>$this->estimate,
            ':cost'=>$this->cost,
            ':comments'=>$this->comments,
            ':itemId'=>$this->itemId
        ));

        if (!$success){throw new \Exception('unable to update mysql database for this item');}
        return $success;

    }

    private function validate_info(){

        if(
            !is_string($this->comments)
            || !is_string($this->serialNumber)
        ){
            return false;
        }

        return true;
    }

    public function classify_object($dbRecord){
        $this->itemId = $dbRecord->itemId;
        $this->orderId = $dbRecord->orderId;
        $this->itemDescription = $dbRecord->itemDescription;
        $this->serialNumber = $dbRecord->serialNumber;
        $this->estimate = $dbRecord->estimate;
        $this->cost = $dbRecord->cost;
        $this->comments = $dbRecord->comments;

        if ($this->validate_info()){ return true;}
        return false;
    }

    public static function create_mysql_storage_table($pdoConnection, $tablename = 'items'){
        $sql = 'CREATE TABLE `'.$tablename.'` (
  `item_id` int(11) DEFAULT NULL AUTO_INCREMENT,
  `item_description` varchar(128) DEFAULT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `serial_number` varchar(128) DEFAULT NULL,
  `cost` decimal(6,2) DEFAULT NULL,
  `estimate` decimal(6,2) DEFAULT NULL,
  `comments` text,
  PRIMARY KEY (`item_id`)
) DEFAULT CHARSET=utf8;';

        $query = $pdoConnection->prepare($sql);
        return $query->execute();

    }

}