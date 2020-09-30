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
use MongoDB\BSON\Regex; 
use MongoDB\Driver\Manager ; 
use MongoDB\Driver\Command ; 
use MongoDB\Driver\Exception\BulkWriteException ;
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

    public function  findDoc($config ,$reqCollection , $query , $options = [] ) {
        $connection = $this->preparecons($config) ;  
        try {
            $client = new Client($connection);
            $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
       //  $options = [];
        $cursor = $collection->find($query, $options);
        foreach ( $cursor  as $document) {
            array_push ( $this->result ,json_decode(json_encode($document))  ) ; 
        }
 
        unset($connection) ;
        unset($client) ;
    }
   // $deleteResult = $collection->deleteMany(['state' => 'ny']);
   
    
    public function aggregate($config ,$reqCollection , $pipeline , $options = [] ) {
        $connection = $this->preparecons($config);  
        try {
            $client = new Client($connection);
            $db = $client->selectDatabase($config->getDb() );
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
      //  $options = [] ;

        $cursor = $collection->aggregate($pipeline , $options);
     //   dd($pipeline, $options) ;
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
            
            $insertOneResult = $collection->insertOne($vlues) ;
           
        }catch (BulkWriteException $error) {
            return [ false , $error->getMessage() ] ;
        }
        unset($connection) ;
        unset($client) ;
        return [ true , $insertOneResult ] ;  
    }

    public function  updateDoc($config ,$reqCollection , $query , $values ) {
  
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
        $updateData = array_merge ( $query ,[ $values ]  ) ;
        // $result = $collection->updateMany($updateData);
        $result = $collection->updateMany($query, [ $values ]  , []);
        unset($connection) ;
        unset($client) ;
         // dd("update", $updateData ,"Query :", $query,"Value", $values , "Result", $result);
        return $result;
    }

    public function  deleteDoc($config ,$reqCollection , $query ) {
  
        $connection =  'mongodb://'.$config->getUser() 
                                   .":".$config->getPassword()
                                   .'@'.$config->getHost()
                                   .':'.$config->getPort() ;
                                   try {
                                               $client = new Client($connection);
        $db = $client->selectDatabase($config->getDb());
        }catch (Exception $error) {
            echo $error->getMessage(); die(1);
            exit ; 
        }
        $collection =  $db->selectCollection($reqCollection); 
        $result = $collection->deleteMany($query);
       
        unset($connection) ;
        unset($client) ;
        return $result ; 
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
       //  dd(__file__.":".__line__, $command , $Indexs ) ;
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

        

            $connect = new Manager('mongodb://'.$config->getUser() 
            .":".$config->getPassword()
            .'@'.$config->getHost()
            .':'.$config->getPort() 
            );
            // $reqCollection = 'Abpon_counters' ;
                
                $collection =  $db->selectCollection($reqCollection); 
                $found = 0 ; 
                foreach ($collection->listIndexes() as $index) {  
                //    if(env("DEV_DEBUG")) { print ( __file__.":".__line__ ." -----> $reqCollection Index Name : " . $index['name']  ) ; print ("<br>") ; } 
                    if ( $index['name'] === $index_name   ) { $found++ ;  break ;  } 
                }
              //  if(env("DEV_DEBUG")) { print ( __file__.":".__line__ ." Found : $found  , collection :  $reqCollection   ,indexname : $index_name " ) ; print ("<br>") ; } 
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


    public function raw( string $command , $config) {
           
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
       
        dd( get_class_methods($db) );

        try { 
            $response = $db->execute($command) ;
        }catch (BulkWriteException $error) {
            return [ false , $error->getMessage() ] ;
        }
        unset($connection) ;
        unset($client) ;
        return [ true , $response  ] ;  
    } 


}
