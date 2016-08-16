<?php

namespace SWD\Order;


class Item
{
    public $itemId;
    public $orderId;
    public $serialNumber;
    public $estimate;
    public $cost;
    public $comments;


    public function add_to_mysql_storageTable($pdoConnection, $tablename = 'items'){

        if (! $this->validate_info()){throw new \Exception('invalid info on pre-create storage check for item');}

        if (!is_string($tablename) || strpos($tablename, ' ') !== false){
            throw new \Exception('Invalid table name for saving order item');
        }

        $sql = 'INSERT INTO '.$tablename.' (order_id,serial_number,estimate,cost,comments) 
          VALUES(:orderId,:serialNumber,:estimate,:cost,:comments)';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(
            ':orderId'=>$this->orderId,
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
            serial_number = :serialNumber,
            estimate = :estimate,
            cost = :cost,
            comments = :comments
            WHERE item_id = :itemId
        ';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(
            ':orderId'=>$this->orderId,
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
            || (!is_string($this->cost) && !is_float($this->cost) && !is_int($this->cost))
            || (!is_string($this->estimate) && !is_float($this->estimate) && !is_int($this->estimate))
            || !is_int($this->orderId)

        ){
            return false;
        }

        return true;
    }

    public function classify_object($dbRecord){
        $this->itemId = $dbRecord->itemId;
        $this->orderId = $dbRecord->orderId;
        $this->serialNumber = $dbRecord->serialNumber;
        $this->estimate = $dbRecord->estimate;
        $this->cost = $dbRecord->cost;
        $this->comments = $dbRecord->comments;

        if ($this->validate_info()){ return true;}
        return false;
    }

}