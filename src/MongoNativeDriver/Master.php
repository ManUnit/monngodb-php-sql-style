<?php
namespace Nantaburi\Mongodb\MongoNativeDriver ;

abstract class Master {

   protected $collection = "";
   protected $database = "";
   protected $fillable = array();
   
    // Head function 

   abstract  public static  function query();
   abstract  public static  function update();

   // absolute no need stack function
   abstract  public static  function all();
   abstract  public static  function insert(array $arrVals);
   
   // Middle Function 
   abstract  public function join();
   abstract  public function jeftjoin();
   abstract  public function where();
   abstract  public function orwhere();
   abstract  public function andwhere();
   abstract  public function select();
   abstract  public function paginate();
   abstract  public function links();
   abstract  public function orderby();
   abstract  public function groupby();
   
   //End with display 
   abstract  public function first();
   abstract  public function get();

}
