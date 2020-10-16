<?php
namespace Nantaburi\Mongodb\MongoNativeDriver ;

abstract class Compatible {  // Method compatible control class
   // Control the extender class can not change you mine later 
   protected $collection = 'dummyString';
   protected $database = 'dummyString' ;
   protected $schema = array() ;
   protected $timezone = 'Asia/Bangkok' ; 
   protected $dateformat = 'Y-m-d H:i:s.u' ;
   // Head function 
  
  // abstract  public static  function update(); //

   // absolute no need stack function
   abstract  public function insert(array $arrVals); //
   abstract  public function select(...$fields) ; //  Array values with 3dot prefix ...$argv
   abstract  public function from(String $collection = '') ; //  Array values with 3dot prefix ...$argv
   abstract  public function where(String $Key='',String $Operation ='', $Value , $boolean = 'mostleft' );
   abstract  public function orwhere(String $Key ,String $Operation ,String $Value);
   abstract  public function andwhere(String $Key ,String $Operation ,String $Value);
   abstract  public function update(...$param ) ;
   abstract  public function andupdate(...$param) ; 
   
  // Middle Function 

   abstract  public function join(String $collection , String $localField , String  $foreignField , String $as = "" );  // <--
   abstract  public function leftjoin(String $collection , String $localField , String  $foreignField , String $as = ""   ); // <--
   abstract  public function limit(int $limit,int $skip); // <--
   abstract  public function groupby(String ...$fieldGroup) ;
   abstract  public function orderby(...$parameters);
   abstract  public function getModifySequence(String $autoIncName);
   abstract  public function first() ;  // 
   abstract  public function get() ; 
   

}
