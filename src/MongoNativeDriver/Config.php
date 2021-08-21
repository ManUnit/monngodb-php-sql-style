<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-6-7
 */


namespace  Nantaburi\Mongodb\MongoNativeDriver;

class Config {  

  private static $connection = array('config' => array() ) ;

  public function __construct() {
       $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
       $venderDir = dirname(dirname($reflection->getFileName())); 
       $databaseConfig = include $venderDir. "/../config/database.php"   ;
       //dd( $databaseConfig ) ;
       
       self::$connection['config']['host'] = $databaseConfig['connections']['mongodb']['host']  ;
       self::$connection['config']['port'] = $databaseConfig['connections']['mongodb']['port']  ;
       self::$connection['config']['username'] = $databaseConfig['connections']['mongodb']['username']  ;
       self::$connection['config']['password'] = $databaseConfig['connections']['mongodb']['password']  ;
       self::$connection['config']['database'] = $databaseConfig['connections']['mongodb']['database']  ;
       if(isset($databaseConfig['connections']['mongodb']['options']['database'])){
           self::$connection['config']['authendatabase'] = $databaseConfig['connections']['mongodb']['options']['authen_db'] ;
       }else{
           self::$connection['config']['authendatabase'] = 'admin' ;
       }
  }
   
  public static function getDb(){
    return self::$connection['config']['database']  ;
  }  

  public static function setDb($dbname){
    // print "newdb is set -> $dbname <- <br>" ;
    // dd($dbname) ;
      self::$connection['config']['database'] = (String) $dbname  ;
   }  

  public static  function getHost(){
   return self::$connection['config']['host']  ;
  }  
 
  public static  function getPort(){
   return self::$connection['config']['port']  ;
  }  
 
  public static  function getUser(){
   return self::$connection['config']['username']  ;
  }  

  public static  function getPassword(){
   return self::$connection['config']['password']  ;
  }  

  public static  function getAuthDb(){
   return self::$connection['config']['authendatabase']  ;
  }  

 
}
