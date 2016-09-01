<?php

namespace SWD;

use SWD\Order\Item;

class Order
{

    public $order_id;

    //data fields
    public $orderdate;
    public $orderstatus;

    //customer info
    public $firstname;
    public $lastname;
    public $company;
    public $phone;
    public $email;

    //shipping info
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $country;

    //Children Objects
    public $itemList= array(

    );


    public function validate_fields(){

        if (
            !is_a($this->orderdate, 'DateTime')
            || (!is_string($this->orderstatus) && !is_null($this->orderstatus))
            || !is_string($this->firstname)
            || !is_string($this->lastname)
            || !is_string($this->company)
            || !is_string($this->phone)
            || !is_string($this->email)
            || !is_string($this->address1)
            || (!is_string($this->address2) && !is_null($this->address2))
            || !is_string($this->city)
            || !is_string($this->state)
            || !is_string($this->zip)
            || !is_string($this->country)
            || !is_array($this->itemList)
        ){
            return false;
        }

        return true;
    }

    public function update_to_mysql_storageTable($pdoConnection ,$tablename='orders'){

        if ($this->validate_fields() === false){ throw new \Exception('unable to update order. Invalid fields');}

        $sql = 'UPDATE '.$tablename.' 
                SET orderdate = :orderdate,
                    order_status = :orderstatus,
                    company = :company,
                    firstname = :firstname,
                    lastname = :lastname,
                    phone = :phone,
                    email = :email,
                    address1 = :address1,
                    address2 = :address2,
                    city = :city,
                    state = :state,
                    zip = :zip,
                    country = :country
                WHERE order_id = :order_id';
        $query = $pdoConnection->prepare($sql);
        $success = $query->execute(array(
            ':orderdate'=>$this->orderdate->format('Y-m-d H:i:s'),
            ':orderstatus'=>$this->orderstatus,
            ':company' => $this->company,
            ':firstname'=>$this->firstname,
            ':lastname'=>$this->lastname,
            ':phone'=>$this->phone,
            ':email'=>$this->email,
            ':address1'=>$this->address1,
            ':address2'=>$this->address2,
            ':city'=>$this->city,
            ':state'=>$this->state,
            ':zip'=>$this->zip,
            ':country'=>$this->country,
            ':order_id'=>$this->order_id

        ));

        return $success;
    }

    public function add_to_mysql_storageTable($pdoConnection, $tablename='orders'){

        if(strpos($tablename,' ') !== false){throw new \Exception('Invalid table name to save Order');}

        $now = new \DateTime();



        $sql = 'INSERT INTO '.$tablename.' 
                (order_date,order_status,company,firstname,lastname,phone,email,address1,address2,city,state,zip,country) 
                VALUES 
                (:orderdate,:orderstatus,:company,:firstname,:lastname,:phone,:email,:address1,:address2,:city,:state,:zip,:country)';

        $query = $pdoConnection->prepare($sql);
        $success = $query->execute(array(
            ':orderdate'=>$now->format('Y-m-d H:i:s'),
            ':orderstatus'=>($this->orderstatus ? $this->orderstatus : 'pending' ),
            ':company'=>$this->company,
            ':firstname'=>$this->firstname,
            ':lastname'=>$this->lastname,
            ':phone'=>$this->phone,
            ':email'=>$this->email,
            ':address1'=>$this->address1,
            ':address2'=>$this->address2,
            ':city'=>$this->city,
            ':state'=>$this->state,
            ':zip'=> $this->zip,
            ':country'=>$this->country
        ));

        if (!$success ){ throw new \Exception('Unable to store order details');}

        $this->order_id = $pdoConnection->lastInsertId();



    }

    public function load_from_mysql_storageTable($pdoConnection,$orderId ,$tablename='orders'){

        $sql = 'SELECT * FROM '.$tablename.' WHERE order_id = :order_id LIMIT 1';

        $query = $pdoConnection->prepare($sql);
        $query->execute(array(':order_id'=>$orderId));

        $result = $query->fetch(\PDO::FETCH_ASSOC);

        if ($result === false){ return false;}

        $this->address1 = $result['address1'];
        $this->address2 = $result['address2'];
        $this->city = $result['city'];
        $this->state = $result['state'];
        $this->zip = $result['zip'];
        $this->country = $result['country'];
        $this->firstname = $result['firstname'];
        $this->lastname  = $result['lastname'];
        $this->phone = $result['phone'];
        $this->email = $result['email'];
        $this->company = $result['company'];
        $this->orderstatus = $result['order_status'];
        $this->orderdate = $result['order_date'];
        $this->order_id = $result['order_id'];

        if (! is_a($this->orderdate, 'DateTime')){
            $this->orderdate = new \DateTime($this->orderdate);
        }


        return $this->validate_fields();

    }

    public static function create_mysql_storageTable($pdoConnection,$tablename='orders'){

        if (!is_string($tablename) || strpos($tablename, ' ') !== false ){
            throw new \Exception('invalid table name for order table creationg');
        }

        $sql = 'CREATE TABLE `'.$tablename.'` (
          `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `order_date` datetime NOT NULL,
          `order_status` varchar(64) DEFAULT \'pending\',
          `company` varchar(128) NOT NULL,
          `firstname` varchar(64) NOT NULL,
          `lastname` varchar(64) NOT NULL,
          `phone` varchar(64) NOT NULL,
          `email` varchar(128) NOT NULL,
          `address1` varchar(64) NOT NULL,
          `address2` varchar(64) DEFAULT NULL,
          `city` varchar(64) NOT NULL,
          `state` varchar(64) NULL DEFAULT NULL,
          `zip` varchar(64) NOT NULL,
          `country` varchar(64) NOT NULL,
          PRIMARY KEY (`order_id`)
          ) DEFAULT CHARSET=utf8;';

        $query = $pdoConnection->prepare($sql);
        $success = $query->execute();
        return $success;
    }

    public function load_itemList($pdoConnection,$tableName='items'){


        $sql = 'SELECT * FROM '.$tableName.' WHERE order_id = :orderId';

        $query = $pdoConnection->prepare($sql);

        $success = $query->execute(array(':orderId'=>$this->order_id));

        if (! $success){ throw new \Exception('unable to find any orders for that id number');}

        $resultList = $query->fetchAll(\PDO::FETCH_OBJ);


        foreach ($resultList as $result){

            $item = new Item();
            $item->classify_object($result);

            $this->itemList[$result->item_id] = $item;

        }


    }

    public function get_current_estimate(){
        $runningTotal = 0;
        foreach ($this->itemList as $item){
            $runningTotal = $runningTotal+(float)$item->estimate;
        }

        return $runningTotal;
    }

    public function get_final_total(){
        $runningTotal = 0;
        foreach ($this->itemList as $item){

            $runningTotal = $runningTotal+$item->cost;

        }

        return $runningTotal;

    }

}
