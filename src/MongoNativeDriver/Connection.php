<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-6-7
 *  Class Connection 
 *  version 1.0
 *  revision 1.1
 */

namespace Nantaburi\Mongodb\MongoNativeDriver ;
use Nantaburi\Mongodb\MongoNativeDriver\Config ;
use Nantaburi\Mongodb\MongoNativeDriver\BuildConnect ;
use MongoDB\BSON\Regex; 


class Connection {  //defind the Class to be  master class
    // Public , Protected  non-static properties  of values $this 
    protected  $collection  ;  // use for end of Override
    protected  $database  ;    // use for end of Override\
    protected  $fillable = array() ; 
    
    /*  Static properties of self::$values 
     *
     */
    // protected static $dataBaseStatic ;  // transform non-static $this->database to static self::$dataBaseStatic   
    // protected static $collectStatic ;   // transform non-static $this->database to static self::$dataBaseStatic  
    
    // Private properties 
    
    private static $whereQuery = array() ;

    public  function __construct() { 

        // no use self::$dataBaseStatic = $this->database ;   //replicate  non-static to static zone  //  use ( new static) instead 
        // no use self::$collectStatic = $this->collection ;   //replicate non-static to static zone  //  use ( new static) instead 
    }



    private function whereConversion(String $Key ,String $Operation ,String $Value) {
        //  dd( $Key , $Operation , $Value  ) ;
        //  for where (or,and)  operations
          if ( $Operation  == "=" ){
              return [ "$Key"=> "$Value" ];                    // SQL transform select * from table where 'key' = 'value'  ; 
          }elseif( $Operation  == "!=" ) {
              return [ "$Key" => ['$ne' => "$Value" ]  ];       // SQL transform select * from table where 'key' != 'value'
          }elseif($Operation  == "<="){
              return [ "$key" => [ '$lte' =>  "$Value" ]  ];   // SQL transform select * from table where 'key' <= 'value'
          }elseif($Operation  == ">="){
              return [ "$key" => [ '$gte' =>  "$Value" ]  ];    // SQL transform select * from table where 'key' >= 'value'
          }elseif($Operation  == "<"){
              return [ "$key" => [ '$lt' =>  "$Value" ]  ];     // SQL transform select * from table where 'key' < 'value'
          }elseif($Operation  == ">"){
              return [ "$key" => [ '$gt' =>  "$Value" ]  ];    // SQL transform select * from table where 'key' > 'value'
          }elseif( $Operation  == "like" ) {
              if (   $Value[0]  != "%" && substr(  "$Value" , -1 ) =="%"  ) { 
                 return [  "$Key" => new Regex('^'. substr( $Value ,0,-1 ) .'.*$', 'i') ]  ;     // SQL transform select * from table where 'key' like 'value%'   ; find begin with ?    
              }elseif (   $Value[0]  == "%"  &&  substr( "$Value" , -1 ) !=  "%"  ) {
                  return [  "$Key" => new Regex('^.*'.substr( $Value ,1 ) .'$', 'i') ];          // SQL transform select * from table where 'key' like '%value'   ; find end with ?
              }elseif (  $Value[0]  == "%"  &&  substr( "$Value" , -1 ) =="%"   ) {
                  return [ "$Key" => new Regex('^.*'.substr( $Value ,1 ,-1)  .'.*$', 'i')];     // SQL transform select * from table where 'key' like '%value%'  ; find where ever with ?
              }else{
                  return [ "$Key" => new Regex('^.'."$Value".'.$', 'i')];   //  SQL transform select * from table where 'key' like 'value'
              }
          }
      }
  

    public static function  query() {
        $config = new Config ; 
        $config->setDb( (new static)->getDbNonstatic() ) ;  // individual get non static properties inside static method 
        return  (new static)->newQuery();  // same as return $this ; 

         // fixed PHP limitation of using return $this  using "(new static)" and pass with non static method
         // แก้ไข ข้อจะำกัด การเข้าถึง ขอบเขตของ non static properties จาก static method โดยใช้ วิธี แปลง non static method ด้วย class static  เหมือน ( int ) $string   
         // CR. idea found in Laravel framework  Illuminate/Database/Eloquent/Model.php 
         // เครดิต!  พบใน  Class Model ที่ใช้กับ Mysql ของ laravel  ใน  static function query()
    } 
    public function getCollectNonstatic(){
        return $this->collection ; 
     }
    public function getDbNonstatic(){
       return $this->database ; 
    }
    public function newQuery() {
        return $this ;
    }

    public function where( String $Key ,String $Operation ,String $Value ) {
    
        self::$whereQuery = $this->whereConversion( $Key ,$Operation ,$Value ) ;
       return $this ;
    }

    public function orwhere(String $Key ,String $Operation ,String $Value) { 
         // 
         $firstStack =  self::$whereQuery ; 
         if (  !isset( $firstStack['$or'] ) ) { 
              /*
               *  ตรวจสอบ key บนสุดว่า มีค่า เป็น or หรือไม่ 
               *  และยังรองรับ การอันดับตัวกระทำทางคณิตศาตร์  ( mathematic order of operation )
               * 
               */
            self::$whereQuery = [ '$or' =>  [  $firstStack , $this->whereConversion( $Key , $Operation , $Value  )   ]    ]; 
         }else{
            array_push ( self::$whereQuery['$or'] , $this->whereConversion( $Key , $Operation , $Value  )   ) ; 
         }  
        return $this ;
    }

    public function andwhere(String $Key ,String $Operation ,String $Value) { 
        // 
        $firstStack =  self::$whereQuery ; 
        if (  !isset( $firstStack['$and'] ) ) {
           self::$whereQuery = [ '$and' =>  [  $firstStack , $this->whereConversion( $Key , $Operation , $Value  )   ]    ]; 
        }else{
           array_push ( self::$whereQuery['$and'] , $this->whereConversion( $Key , $Operation , $Value  )   ) ; 
        }  
       return $this ;
   }
    
    public static function   all() {  // static method  display output 
        $config = new Config ;
        $config->setDb((new static)->getDbNonstatic() ) ;
        $query = [] ;
        $Operation = "null" ;
  
        $conclude = new BuildConnect ;
        $conclude->findDoc( $config ,(new static)->getCollectNonstatic() ,$query  ) ; 
        return $conclude->result ;  // no more end output  with get() will use and the end  
    }

    public function get() {    // non static method  display output  using after where,orwhere operation
       //dd( self::$whereQuery  ) ;
        $config = new Config ;
        $config->setDb((new static)->getDbNonstatic()) ;
        $conclude = new BuildConnect ; 
        $conclude->findDoc($config ,$this->collection ,self::$whereQuery) ; 
        return $conclude->result ; 
    } 

    public static function insert( array $arrVals ) {    // non static method  display output  using after where,orwhere operation
 
         foreach ( array_keys($arrVals) as $key  ) {
           
            if (  !in_array( $key , (new static)->fillable ) ) { return  [  0 , "Error ! insert fail-> ".$key. " out of fillable" ]; } 
 
          }
         $config = new Config ;
         $config->setDb((new static)->getDbNonstatic()) ;
         $conclude = new BuildConnect ; 
         $reactionInsert = $conclude->insertDoc($config ,(new static)->getCollectNonstatic() , $arrVals ) ; 
         return  [ 1 ,$reactionInsert ] ; 
     }





}
