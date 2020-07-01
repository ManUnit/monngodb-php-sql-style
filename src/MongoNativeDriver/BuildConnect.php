<?php 

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham , Supachai ,Tanapat
 *  Update : 2020-6-7
 *  Class Connection 
 */

  /*
   * @docs referrence  
   * https://docs.mongodb.com/php-library/v1.2/reference/class/MongoDBCollection/ 
   *
   */



namespace Nantaburi\Mongodb\MongoNativeDriver;
use MongoDB\Client;
class BuildConnect {


    public   $result = array() ;

      public function __construct( ) { 
          // plan for more advance later
       }

    private function preparecons ($config) {
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
        return $connection ; 
    
    } 

    public function  findDoc($config ,$reqCollection , $query ) {
  
        $connection = $this->preparecons($config) ;  

        try {
        $client = new Client($connection);
        $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
        $options = [];
        $cursor = $collection->find($query, $options);
        foreach ( $cursor  as $document) {
            array_push ( $this->result ,json_decode(json_encode($document))  ) ; 
          }
 
        unset($connection) ;
        unset($client) ;


    }


    public function  insertDoc($config ,$reqCollection ,array $vlues ) {
  
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
        try {
            $client = new Client($connection);
            $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
        $insertOneResult = $collection->insertOne($vlues);
        unset($connection) ;
        unset($client) ;
        return   $insertOneResult ;
    }

    public function  updateDoc($config ,$reqCollection , $vlues ) {
  
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
        try {
            $client = new Client($connection);
            $db = $client->selectDatabase($config->getDb() );
            $collection =  $db->selectCollection($reqCollection); 
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $options = [];
        $cursor = $collection->find($query, $options);
        foreach ( $cursor  as $document) {
            array_push ( $this->result ,json_decode(json_encode($document))  ) ; 
        }
 
        unset($connection) ;
        unset($client) ;
    }
    public function  deleteDoc($config ,$reqCollection , $vlues ) {
  
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
                                   try {
                                               $client = new Client($connection);
        $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
        $options = [];
        $cursor = $collection->find($query, $options);
        foreach ( $cursor  as $document) {
            array_push ( $this->result ,json_decode(json_encode($document))  ) ; 
        }
 
        unset($connection) ;
        unset($client) ;


    }

    public function  adminCreateUserDatabase( $User,$Password,$Role,$UserDatabase ) {
  
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
        try {
             $client = new Client($connection);
             $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }

        $db = $client->selectDatabase($UserDatabase);
        $command = array( "createUser" => "$User" ,
                          "pwd"        => "$Password" ,
                          "roles"      => array(   array("role" => "$Role", "db" => $UserDatabase )  )  // $role will be read , readWrite
        );
        
         $reaction =  $db->command( $command );
         unset($client) ;
         unset($connection) ;
        
         return $reaction ; 
    }



}
