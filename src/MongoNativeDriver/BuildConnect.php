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
use MongoDB\Driver\Manager ; 
use MongoDB\Driver\Command ; 
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
             return [ 0, $error->getMessage() ] ;
        }
        $collection =  $db->selectCollection($reqCollection); 
        try {
            $insertOneResult = $collection->insertOne($vlues);
        }catch (Exception $error) {
            return [ 0, $error->getMessage() ] ;
        }
        unset($connection) ;
        unset($client) ;
        return [ 1, $insertOneResult ] ;  
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

    public function createIndex(  $config ,  String $collection , Array $Indexs ) { 
        $connect = new Manager('mongodb://'.$config->getUser() 
                                                        .":".$config->getPassword()
                                                        .'@'.$config->getHost()
                                                        .':'.$config->getPort() 
                                            );
        $command = new Command([ "createIndexes" => $collection  ,
                                 "indexes"       => [ $Indexs ],
                                ]);
        $result = $connect->executeCommand($config->getDb(), $command);
        return  $result ; 
    }

    public function getIndex(  $config ,  String $reqCollection , String $index_name ) { 
        $connection =  'mongodb://'.$config->getUser() 
                                                        .":".$config->getPassword()
                                                        .'@'.$config->getHost()
                                                        .':'.$config->getPort(); 
        try {
            $client = new Client($connection);
            $db = $client->selectDatabase($config->getDb());
        }catch (Exception $error) {
                return [ 0, $error->getMessage() ] ;    
        }
        $collection =  $db->selectCollection($reqCollection); 
        $found = 0 ; 
        foreach ($collection->listIndexes()as $index) {
             if ( ($index['name']) === $index_name   )$found++ ;
         }
        if ( $found > 0 ){
            return true;
        }else{
            return false;
        }   
    }

    public  function getModifySequence($config,$key_collection ,$autoIncName)
    {   //dd("Here is get Seq") ;
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
        $db = $client->selectDatabase($config->getDb());
        $collection =  $db->selectCollection(  $config->getDb() . '_counters');
        $command = array( "findAndModify" => $config->getDb()."_counters",
                          "query" => array("inc_field"=> "$autoIncName" , 'collection'=>$key_collection ),
                          "update"=> array('$inc'=>array('sequence_value'=>1)),
                          "upsert"=> true,
                          "new"=> true
                        ); 
        $reaction =   $db->command($command)->toArray()[0]->value->sequence_value ;
        unset($client) ;
        unset($collection) ;
        return $reaction ;
    }



}
