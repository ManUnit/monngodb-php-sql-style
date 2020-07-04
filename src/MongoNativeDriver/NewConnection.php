<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-7-3
 */


namespace Nantaburi\Mongodb\MongoNativeDriver;

use Nantaburi\Mongodb\MongoNativeDriver\Connection;  
class NewConnection {  //   defind class use for slipt static object to new Statics 
    
    protected $connection ;
    public function __construct(){
         return  $this->connection =  new Connection ; 
     }

}
