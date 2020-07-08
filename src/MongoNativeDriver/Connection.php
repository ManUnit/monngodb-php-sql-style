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
use Nantaburi\Mongodb\MongoNativeDriver\Compatible ;
use MongoDB\BSON\Regex; 
use MongoDB\BSON\Int64 ; 


class Connection extends Compatible {  //defind the Class to be  master class
    // Public , Protected  non-static properties  of values $this 
    /*  Static properties of self::$values 
     *
     */
    // protected static $dataBaseStatic ;  // transform non-static $this->database to static self::$dataBaseStatic   
    // protected static $collectStatic ;   // transform non-static $this->database to static self::$dataBaseStatic  
    
    // Private properties 

    protected $collection = 'dummyString';
    protected $database = 'dummyString' ;
    protected $fillable = array();
    
    private static $whereQuery = array() ;

    public  function __construct() { 
        // no use self::$dataBaseStatic = $this->database ;   //replicate  non-static to static zone  //  use ( new static) instead 
        // no use self::$collectStatic = $this->collection ;   //replicate non-static to static zone  //  use ( new static) instead 
    }

    public function join(){}
    public function jeftjoin(){}
    public function select(){}
    public function paginate(){}
    public function links(){}
    public function orderby(){}
    public function groupby(){}
    public static function update(){}
    
    //End with display 
    public function first(){}

    public static function DB(){
        return  (new static)->newQuery(); 
    } 
    public function collection(String $collection){
        $this->collection = $collection ;
        return  $this ;
    } 

    public static function  query() {
        $config = new Config ; 
        $config->setDb((String)(new static)->getDbNonstatic());  // individual get non static properties inside static method 
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
        $query = [] ; // find all documents 

        $conclude = new BuildConnect ;
        $conclude->findDoc( $config ,(new static)->getCollectNonstatic() ,$query  ) ; 
        return $conclude->result ;  // no more end output  with get() will use and the end  
    }

    public function get() {    // non static method  display output  using after where,orwhere operation

        $config = new Config ;
        $config->setDb( $this->getDbNonstatic()) ;
        $conclude = new BuildConnect ; 
        $conclude->findDoc($config ,$this->collection ,self::$whereQuery) ; 
        return $conclude->result ; 
    } 

    public  function insert( array $arrVals ) {    // non static method  display output use after where,orwhere operation
        $canfill =  $this->fillable( $arrVals ) ;  // this method going to reject insert once unmatch schema and fillable 
        if( $canfill[0] = 1 ){
            $config = new Config ;
            $config->setDb( $this->getDbNonstatic()) ;
            $conclude = new BuildConnect ; 
            $reactionInsert = $conclude->insertDoc($config ,$this->getCollectNonstatic(),$arrVals ) ; 
            return  [ 1 ,$reactionInsert ] ; 
         }else{
            return  [ 0 ,"Error ! unfillable detected" ] ; 
         }
    }

    public function getModifySequence(String $autoIncName) { 
        
        $this->fillable( (array) [] );  // Pre scan schema to create all first since empty indecies and counter collection 
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ;
        $result = $conclude->getModifySequence($config, $this->getCollectNonstatic() ,  $autoIncName) ; 
        return $result  ; 
    }

    /****** @Private Zone ******/  
    private function fillable(array $arrVals ) {
        $collections=[];
        $fillables=[];
        foreach ( array_keys( $this->schema ) as $each_coll  ) { 
            array_push($collections,$each_coll) ; 
        }

        if( !in_array( $this->collection , $collections)  ){
             return [0 , "Error ! collection non member in schema" ] ;
        }else{ 
             foreach (  $this->schema[ $this->collection]  as $keys =>  $values ) { 
                if ( is_array($values) ){
                     
                    if(isset($this->schema[$this->collection][$keys]['AutoIncStartwith'])){ 
                        $startseq = $this->schema[$this->collection][$keys]['AutoIncStartwith'] ; 
                    }else{
                        $startseq = 0 ;
                    }
                     
                    foreach ( $values  as $key => $value  ) { 
                        if ( $key === "AutoInc"  &&  $value === true )   $this->findCreateAutoInc($keys, $startseq )  ; 
                        if ( $key === "Index"  &&  $value === true ) $this->findCreateIndexOne($keys) ;
                    } 
                    $findmultiIndex = substr( $keys  , 0, strlen( "\$__MULTIPLE_INDEX")  );
                    if ( "\$__MULTIPLE_INDEX" === $findmultiIndex ){ 
                             $this->findCreateIndexMany($keys) ;  
                    }else{
                        array_push ($fillables , $keys) ; 
                    }

                }else{
                    array_push ($fillables , $values) ; 
                }
             } 
        }
        
        foreach ( array_keys($arrVals) as $key  ) {
            if (  !in_array( $key , $fillables ) ) { return  [  0 , "Error ! insert fail -> feild name ".$key. " out of member fillables" ]; } 
        }
 
        return [1," OK "] ;
    }
    
    public  function findCreateAutoInc(String $fieldNameToInc , int $StartSeq  ) {
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
        $collection_counter  = $this->getDbNonstatic().'_counters' ; 
        $this->findCreateIndexAutoInc( $fieldNameToInc, $this->getCollectNonstatic() ) ; // Magic create index 
        $query = [  'inc_field' => $fieldNameToInc , 'collection' => $this->getCollectNonstatic() ]  ;
        
        $conclude->findDoc($config , $collection_counter ,$query ) ; 
         if ( null == $conclude->result ) {
           
            $reactionInsert = $conclude->insertDoc($config ,$collection_counter ,[
                                                     	'inc_field' => $fieldNameToInc ,
						                            	'collection'=> $this->getCollectNonstatic(),
                                                        'sequence_value' =>   0.0 + $StartSeq    ]) ;
         }  
        
        return $conclude->result ; 
    } 
    private function findCreateIndexAutoInc(String $fieldIndex , String $collection ){  
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
        $index = [
                    "name" =>"\$__IDX_AUTOINC_".$this->getDbNonstatic()."_counters",
                    "key"  =>['inc_field'=>1,'collection'=>1],
                    "unique" => true ,
                    "ns" => $config->getDb().".".$this->getDbNonstatic()."_counters"
                ];
        $result =  $conclude->getIndex($config , $this->getCollectNonstatic() , $index['name'] );
         if ( !$result ) { 
              $reactionInsert = $conclude->createIndex($config ,  $this->getDbNonstatic()."_counters" , $index  );
          }else{
              $reactionInsert = false ;
          }  
         return  $reactionInsert  ; 
    }

    private function findCreateIndexOne(String $fieldIndex){  
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
         if(isset($this->schema[$this->getCollectNonstatic()][$fieldIndex]['Unique'])){
            $index_unique =  $this->schema[$this->getCollectNonstatic()][$fieldIndex]['Unique'] ;
         }else{
            $index_unique  = false ; 
         }
          
        $index = [
                    "name" =>  "\$__INDEX_".strtoupper($fieldIndex)."_"  ,
                    "key"  =>  [$fieldIndex=>1] ,
                    "unique" => $index_unique  ,
                    "ns" => $config->getDb().".".$this->getCollectNonstatic()  
                ];
        $result =  $conclude->getIndex($config , $this->getCollectNonstatic() , $index['name'] );
         if ( !$result ) { 
              $reactionInsert = $conclude->createIndex($config , $this->getCollectNonstatic() , $index  );
          }else{
              $reactionInsert = false ;
          }  
         return  $reactionInsert  ; 
    }

    private function findCreateIndexMany(String $IndexMany){ 
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
        $index =  $this->schema[$this->getCollectNonstatic()][$IndexMany] ;  
        $index['ns'] = $config->getDb().".".$this->getCollectNonstatic()  ;
        $result =  $conclude->getIndex($config , $this->getCollectNonstatic() , $index['name'] );
         if (  !$result ) { 
              $reactionInsert = $conclude->createIndex($config , $this->getCollectNonstatic() , $index  );
          }else{
              $reactionInsert = false ;
          }  
         return  $reactionInsert  ; 
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
  

   
     
}
