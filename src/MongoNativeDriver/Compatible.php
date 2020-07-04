<?php
namespace Nantaburi\Mongodb\MongoNativeDriver ;

abstract class Compatible {  // Method compatible control class
   // Control the extender class can not change you mine later 
   protected $collection = 'dummyString';
   protected $database = 'dummyString' ;

   
    // Head function 

 
   abstract  public static  function query();  // 
   abstract  public static  function update(); //

   // absolute no need stack function
   abstract  public static  function all();  //
   abstract  public static  function insert(array $arrVals); //
   
   // Middle Function 

   abstract  public function where(String $Key ,String $Operation ,String $Value); //
   abstract  public function orwhere(String $Key ,String $Operation ,String $Value); //
   abstract  public function andwhere(String $Key ,String $Operation ,String $Value); //
   abstract  public function join();
   abstract  public function jeftjoin();
   abstract  public function select();
   abstract  public function paginate();
   abstract  public function links();
   abstract  public function orderby();
   abstract  public function groupby();
   
   //End with display 
   abstract  public function first();
   abstract  public function get();

}
