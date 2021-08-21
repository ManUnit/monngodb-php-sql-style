<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-7-3
 */


namespace Nantaburi\Mongodb\MongoNativeDriver;

use Nantaburi\Mongodb\MongoNativeDriver\Config;
use Nantaburi\Mongodb\MongoNativeDriver\Connection;  
class DB extends Connection {  //   defind class for repeater
 //class DB  {  //   defind class for repeater
/* purpose of Class for maintain format as double colon  "::" DB::collection()  proper for user remember how to use
* and still  style using  Model::query()->where bar bar bar 
* going to be DB::collection('collectionName')->where()  bar bar bar 
*/
   //protected $collection = "users" ;
   //protected $database = "gtradeadmin";
  
    /*
    * @override 
    *  replace $collection was in  Model  
    */ 
    protected static $app; 

    public function noncollection($test) { 
        $this->collection = $test ; 
        $this->database = (String) Config::getDb()  ;
        return $this ;
    }

    private  function setCollection(String $setCollection) 
    {     
        //  $conn = new Connection ; 
         $this->database = (String) Config::getDb()  ; 
         $this->collection =  $setCollection ;
         
    }
    public function getColl () {
        return $this->collection  ;
    }


}
