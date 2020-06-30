<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-6-7
 */


namespace Nantaburi\Mongodb\MongoNativeDriver;

use Nantaburi\Mongodb\MongoNativeDriver\Config;
use Nantaburi\Mongodb\MongoNativeDriver\Connection;  
class Model extends Connection {  //   defind class for repeater 
   public function __construct()  // Override all extends class __construct()
    { 
      parent::__construct() ; // cascade method  __construct() on super class again 
       // Override Clear all  master class extends  function  __construct()  to be nothing 
    }

}
