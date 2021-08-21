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
require  __DIR__."/../Operators/helpper.php" ;
use Nantaburi\Mongodb\MongoNativeDriver\Config ;
use Nantaburi\Mongodb\MongoNativeDriver\BuildConnect ;
use Nantaburi\Mongodb\MongoNativeDriver\Compatible ;
use PHPUnit\Framework\Exception;
use MongoDB\BSON\Regex; 


class Connection extends Compatible {  //defind the Class to be  master class

    //@@ Call trait class
    use Addon,Commands ; 

    //@@ Public , Protected  non-static properties  of values $this 
    //@@  Static properties of self::$values 
 
    protected $collection = 'dummyString';
    protected $database = 'dummyString' ;
    protected $timezone = 'UTC' ;
    protected $fillable = array();    
     //@@ Private properties 
    private static $ClassError = array();
    private static $querys = array();
    private static $combind = array();
    private static $options = array(); 
    private static $limits = array(); 
    private static $orderTerm = array();
    private static $pipeline = array();
    private static $updates = array();
    private static $andupdates = array();
    private static $joincollections = array();
    private static $groupby = array();
    private static $pre_groupby = array();
    private static $mappingAs = array(); 
    private static $specialOperator = array();
    private static $SequenceCollection = null ;
    
    protected function  initQuerysValues() {
        self::$pre_groupby = [] ;
        self::$groupby = [] ;
        self::$mappingAs = [] ;
        self::$options = [] ;
        self::$querys = [] ;
        self::$joincollections = [] ;
        self::$pipeline = [] ;
        self::$orderTerm  = [] ;
        self::$limits  = [] ;
        self::$aggregate_options = [] ; 
        self::$specialOperator = [] ;
    }

    protected function initQuery () {
        self::$querys = [] ;
        self::$orderTerm  = [] ;
    }

    protected function resetStaticInsert(){
        self:: $SequenceCollection = null ;
    }
    
    public  function __construct() {  
        // no use self::$dataBaseStatic = $this->database ;   //replicate  non-static to static zone  //  use ( new static) instead 
        // no use self::$collectStatic = $this->collection ;   //replicate non-static to static zone  //  use ( new static) instead 
    }
    
    public  function __destruct( ) { 
        // no use self::$dataBaseStatic = $this->database ;   //replicate  non-static to static zone  //  use ( new static) instead 
        // no use self::$collectStatic = $this->collection ;   //replicate non-static to static zone  //  use ( new static) instead 
        return $this ;  
    }

 

    public function __call($method, $parameters)
    {

    }

    public static function __callStatic($method, $parameters){
        //  if ( $method === "database" ){ 
        //      if(count($parameters) == 0) {
        //              return (new static)->setDatabaseCollection((new static)->database , (new static)->collection);
        //      }elseif ( count($parameters) == 1 ){
        //              return (new static)->setDatabase("$parameters[0]");
        //      }elseif(count($parameters) == 2){
        //              return (new static)->setDatabaseCollection("$parameters[0]" , "$parameters[1]" );
        //      }
        //  }elseif($method === "DB" ) {
        //     if(count($parameters) == 0) {
        //         return (new static)->setDatabaseCollection((new static)->database , (new static)->collection);
        //     }elseif ( count($parameters) == 1 ){
        //             return (new static)->setDatabase("$parameters[0]");
        //     }elseif(count($parameters) == 2){
        //             return (new static)->setDatabaseCollection("$parameters[0]" , "$parameters[1]" );
        //     }
        //  }
    }
 
 
    public static function collection(string $coll=''){
        // support defind value with nothing 
        if ($coll === '' ) return (new static)  // get  default collection from model when nothing value in collection()
                         ->setCollection(
                              (new static)->getCollectNonstatic()
                         ) ;
        
        return  (new static)->setCollection($coll); 
    }

    public static function query($coll=''){
        $this->initQuerysValues() ; 
        // support defind value with nothing 
        if ($coll == '' ) return (new static)  // get  default collection from model when nothing value in collection()
                         ->setCollection(
                              (new static)->getCollectNonstatic()
                         ) ;
        return  (new static)->setCollection($coll); 
    }

    public  function from(String $coll=''){
        // support defind value with nothing 
        if ($coll == '' ) return $this->setCollection( $this->getCollectNonstatic());
        return  $this->setCollection($coll); 
    }

    public function join(String $joinCollection , String $localField , String  $foreignField , String $as = " "){  
        return $this->leftjoin($joinCollection ,$localField , $foreignField , $as  ) ;
    }

    public function leftjoin(String $joinCollection,String $localField , String  $foreignField , String $as = " "){ 
        $asForeign = explode('.', $foreignField );
        if($asForeign[0] !== $joinCollection ){  throw new Exception(" Error ! Foreign field  $foreignField isn't in join collection $joinCollection  ");     }
        if(!isset(self::$joincollections [0]['$project'][$this->collection])) array_push(self::$joincollections , ['$project' =>['_id'=>0 , $this->collection => '$$ROOT' ]]);
         array_push(self::$joincollections  , ['$lookup' =>  [ 
                                        'localField' =>  $localField ,
                                        'from' => $joinCollection ,
                                        'foreignField' => $asForeign[1] ,
                                        'as' => $asForeign[0]  
                                    ]]);   
        array_push(self::$joincollections,['$unwind'=>[ 
                                      'path' => "\$$asForeign[0]",
                                      'preserveNullAndEmptyArrays' => true
                                    ]]); 
        return $this ;
    }

    protected function getCollectNonstatic(){
        return $this->collection ; 
     }
     protected function getDbNonstatic(){
       return $this->database ; 
    }
    protected function newQuery() {
        return $this ;
    }
    
    public  function andupdate(...$param){ 
        if(count($param) == 1  &&  is_array($param[0])){
            self::$andupdates = array_merge( self::$andupdates,$param[0]); 
        }elseif( count($param) == 2){
            self::$andupdates = array_merge( self::$andupdates,[$param[0] => $param[1]]);
        }elseif(count($param) == 3  && $param[1] === '=' ){
            self::$andupdates = array_merge( self::$andupdates,[$param[0] => $param[2]]);
        }elseif(count($param) > 3  ){ 
            self::$anError (0,"Error in method andupdate(), require 3 you give ".count($param) ) ;
        }
        return   $this ;  
    } 

    public  function update(...$param ){ 
        if (self::$querys == null ){ return [0,"Error missing where query document require method  where() before andupdate() or update() example where(...)->andupdate()->update() "] ;}
        
        if( count($param) == 1 && is_array($param[0]) ){ 
            $canfill =  $this->fillable( $param[0] , ['update' => true] ) ; 
            if( $canfill[0] == 0){  return  $canfill ;}
            $dataTypesMapping = dataTypemapping( $this->schema[$this->getCollectNonstatic()] , $param , $this->timezone , $this->dateformat) ;  
            self::$updates = [ '$set' => [ $dataTypesMapping ] ] ;
            }elseif(count($param) == 2){
            $canfill =  $this->fillable([$param[0]=>$param[1]] , ['update' => true]  ); 
            if( $canfill[0] == 0){  return  $canfill ;}
            $dataTypesMapping = dataTypemapping( $this->schema[$this->getCollectNonstatic()] , [$param[0] => $param[1] ] , $this->timezone , $this->dateformat ) ;  
          //  dd(__file__.__line__,$dataTypesMapping , $this->getCollectNonstatic());
            self::$updates = [ '$set' => $dataTypesMapping ];
        }elseif(count($param) == 3  && $param[1] === '=' ){
            $canfill =  $this->fillable([$param[0]=>$param[2]] , ['update' => true]   ); 
            if( $canfill[0] == 0){  return  $canfill ;}
            $dataTypesMapping = dataTypemapping( $this->schema[$this->getCollectNonstatic()] , [$param[0] => $param[2] ] , $this->timezone , $this->dateformat ) ; 
            self::$updates = [ '$set' => $dataTypesMapping ];
        }elseif(count($param) > 3){
            return [0,"update format expect 3 argement you put " . count($param) ];
        }else{
            return [0,"update input style format are not support for the support format to be following : update('id',1) or update('name','=', 'Nutter') 
            and you can multiply field with array update([ 'name' => 'Supachai' ,'lastname' => 'W.','address' => 'Pangpoi city' ]" ] ;
        }
        $config=new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude = new BuildConnect ;
        if (!isset(self::$updates['$set'])&&self::$andupdates==!null)self::$updates=['$set'=>[]];
        $canfill =  $this->fillable(self::$andupdates ,  ['update' => true]   ); 
        if( $canfill[0] == 0){  return  $canfill ;}
        self::$updates['$set'] = array_merge(  self::$updates['$set'], self::$andupdates );
        $updateresult = $conclude->updateDoc( $config ,$this->getCollectNonstatic() ,self::$querys , self::$updates  ) ; 
        return $updateresult ;  // no more end of output with get()   
    }
    
    public function where( String $Key = '' ,String $Operation = '' , $Value , $boolean = 'mostleft' ) { 
       self::$querys= $this->whereConversion($Key,$Operation,$Value);
       array_push(self::$orderTerm, [ $boolean =>  $this->whereConversion($Key,$Operation,$Value)]); 
       return  $this ;
    }

    public function wherein( String $Key = '' ,array $searchArr , $boolean = 'mostleft'  ) { 
        self::$querys= $this->whereConversion($Key,'in',$searchArr );
         array_push(self::$orderTerm, [ $boolean =>  $this->whereConversion($Key,'in',$searchArr )]); 
        return  $this ;
     }

    public function orwhere(String $Key ,String $Operation , $Value) { 
        return $this->where($Key ,$Operation ,$Value , '$or' ) ;
    }

    public function andwhere(String $Key ,String $Operation , $Value) { 
        return $this->where($Key ,$Operation ,$Value , '$and' ) ;
    }

    public function delete(...$params){ 
        $this->initQuerysValues() ; 
        $deleteQuery = [] ;
        if (self::$querys == null && count($params) == 0 ){ return [0,"Error missing where query document require  where() before delete() : example where(...)->delete() "] ;}
        elseif(self::$querys == !null && count($params) == 0){
            $deleteQuery=self::$querys;
        }elseif(self::$querys == !null && count($params) == 1 && is_array($params[0])){ 
           foreach( $params[0] as $Key => $Values ){ 
                $this->andwhere( $Key ,"=",$Values ) ;
            }
            $this->getAllwhere() ;
            $deleteQuery=self::$querys;
        }elseif(self::$querys == !null && count($params) == 2 && !is_array($params[0] && !is_array($params[1] )) ){
            $this->andwhere( $params[0] ,"=", $params[1] ) ;
            $this->getAllwhere() ;
            $deleteQuery=self::$querys;
        }elseif(self::$querys== !null&& count($params) == 3 && !is_array($params[0] && !is_array($params[1] ) && !is_array($params[2]))){
            $this->andwhere( $params[0] , $params[1] , $params[2] ) ;
            $this->getAllwhere() ;
            $deleteQuery=self::$querys;
        }elseif(count($params) > 3 ){
            return [0,"Error delete() value format unsupport  aregements over 3 you give " . count($params) . "try change value from example format delete('name','John'),delete('name','=','Johnny' ) or delete(['id'=>1,'name'=>'Jonh'])"  ] ;
        }else{
            return [0,"Error delete('\$argement')  unsupport \$agement try change as example format delete('name','John'),delete('name','=','Johnny' ) or delete(['id'=>1,'name'=>'Jonh'])"  ] ;
        }
        $config=new Config ;
        $config->setDb((new static)->getDbNonstatic()) ;
        $conclude = new BuildConnect;
        $deleteresult = $conclude->deleteDoc($config ,(new static)->getCollectNonstatic() ,$deleteQuery); 
        return $deleteresult;
    }

    public static function   all() {  // static method  display output 
        $config = new Config ;
        $config->setDb((new static)->getDbNonstatic() ) ;
        $query = [] ; // find all documents 
        $conclude = new BuildConnect ;
        $conclude->findDoc( $config ,(new static)->getCollectNonstatic() ,$query  ) ; 
        return $conclude->result ;  // no more end output  with get() will use and on the end  
    } 

    public function select(...$fields){  
        $this->initQuerysValues() ; // reset all of $this values 
        
        self::$pre_groupby = array_merge(['$selected'=>true], removeAs($fields)  ); 
        $selectCommand=trim($fields[0]);
        $selectCommand=str_replace(" ","",$selectCommand);
  
        if(count($fields)== 1 && $selectCommand === '*'  ){
            return $this;
        }elseif( count($fields)== 1 && true == preg_match("/^count\(+[*a-zA-Z0-9._-]+\)/" , $selectCommand)){ 
            self::$specialOperator =  commandTranslate($selectCommand)  ; 
           // dd(__file__.__line__,self::$specialOperator , $selectCommand);
            return $this ; 
        }elseif( count($fields)== 1 && true == preg_match("/^sum\(+[*a-zA-Z0-9._-]+\)/" , $selectCommand)){ 
            self::$specialOperator =  commandTranslate($selectCommand)  ; 
            dd(__file__.__line__,self::$specialOperator , $selectCommand);
            return $this ; 
        }

        if (!isset ( self::$options['projection'])){self::$options['projection'] = [] ; }
        self::$mappingAs = asmap($fields) ;

        $fields=asmap_keys($fields);
        foreach($fields as $field){  
            self::$options['projection'] = array_merge(self::$options['projection'] , [ $field => "\$$field"  ]  );
        }
            self::$options['projection'] = array_merge(self::$options['projection'] , [ "_id" =>  0  ]  );
        return $this ;
    }

    public function groupby(String ...$params){ 
        
        $groupby =['_id' => [
            
        ]];

        if($params[0]==='$selected'){$params=self::$pre_groupby;unset($params['$selected']);}
        foreach($params as $param){
               $groupby['_id'] += [  str_replace(".",dotter(),$param) => '$'. $param ];
        }
        $groupby = ['$group' => $groupby] ;
        self::$groupby = $groupby;
        return $this ;
    }
    

    public function get() {    
        $this->getAllwhere() ;  // Intregate where everywhere  
        if(!null == self::$joincollections){ 
            if(env('DEV_DEBUG'))print  (__file__.":".__line__ ." -> find join : <br>\n") ;
            if (isset(self::$specialOperator['count'])){ return  $this->execFunctions('count','join') ;}
            
            return $this->findJoin() ;
        }
        //
        //@command for group by
        //
        if(!null == self::$groupby && null ==  self::$joincollections ){ 
          //  if(env('DEV_DEBUG')) print  ("connection@DEBUG find group : <br>\n") ;
            if(isset(self::$specialOperator['count'])){ return  $this->execFunctions('count','group') ;}
            if(env('DEV_DEVBUG')== true )print(__file__.__line__." AFTER CAL FUNCTION <br>") ;
            return $this->findGroup() ; 
        }else{     // @ normal find 
            // if(env('DEV_DEBUG')) print  (__file__.":".__line__ ."<br> ------> connection@DEBUG find normal : <br>\n") ;
            // if (isset(self::$specialOperator['count'])){ return $this->execFunctions('count','find') ;}
            return  $this->findNormal() ;
        } 
    } 

    public function random( int $numRec = 1 ) {    

        $this->getAllwhere() ;  // Intregate where everywhere  
        if(!null == self::$joincollections){ 
          // if(env('DEV_DEBUG'))print  (__file__.":".__line__ ." -> find join : <br>\n") ;
           return $this->findJoin(['random' => $numRec ]) ;
        }
        //
        //@command for group by
        //
        if(!null == self::$groupby && null ==  self::$joincollections ){ 
          // if(env('DEV_DEBUG')) print  ("connection@DEBUG find group : <br>\n") ;
           return $this->findGroup(['random' => $numRec ]) ; 
        }else{     // @ normal find 
          if(env('DEV_DEBUG')) print  (__file__.":".__line__ ."<br> ------> connection@DEBUG find normal : <br>\n") ;
          //throw new Exception(" Error ! request group() function  and and format select ()->  group()  and ->random() in order format functions "); 
          return  $this->findGroup(['random' => $numRec ]) ;
        } 
      
    } 
    
    public function  rawAggregate(array $Array , array $option ){
        // aggregate($config ,$reqCollection , $pipeline , $options = [] )
        $config = new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude = new BuildConnect ; 
        $reactionInsert = $conclude->aggregate($config ,$this->getCollectNonstatic(),$Array , $option  ) ; 
        return $conclude->result ;
    }

    public function  rawFind(array $Array , array $option = null ){
        // aggregate($config ,$reqCollection , $pipeline , $options = [] )

        if($option==null){$options=[];}else{$options=$option ;}  
        $config = new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude = new BuildConnect ; 
        $reactionInsert = $conclude->findDoc($config ,$this->getCollectNonstatic(),$Array , $options  ) ; 
        return $conclude->result ;
    }
       
    public function first (){
        $this->limit(1);
        return $this->get(); 
    }
    
    public function limit(int $limit,int $skip = 0){
        if( $skip > 0) self::$options = array_merge( self::$options , [ 'skip' => $skip ] ) ;
        self::$options = array_merge( self::$options , [ 'limit' => $limit ] ) ;
        return $this ;
    }
   
    public function orderby(...$parameters){
        $argcount = count($parameters) ;  
        if ($argcount == 1 ){
            self::$options = array_merge( self::$options , [ 'sort' => [ $parameters[0]=> 1 ] ]); 
            return $this ;
        }elseif($argcount > 1){
            //@@checker : Engagement ASC DESC with pair parameters  
             $engagementMap = ['sort'=>[]] ;
             $leftParameter = '' ;  
             $countParam = 0 ;  
             $min2max = 1 ;
             foreach ( $parameters  as $parameter){  
                if ( (strtolower($parameter) ==='asc' || strtolower($parameter)==='desc' )){$countParam++;continue; } 
                if( isset($parameters[$countParam+1]) && strtolower($parameters[$countParam+1]) === 'asc' ){   $min2max = 1;} 
                elseif( isset($parameters[$countParam+1]) && strtolower($parameters[$countParam+1]) === 'desc' ){   $min2max = -1;}
                elseif( isset($parameters[$countParam+1]) && (strtolower($parameters[$countParam+1])!=='asc' || strtolower($parameters[$countParam+1]) !=='desc' ) ){   $min2max = 1;} 
                elseif( !isset($parameters[$countParam+1])  ){   $min2max = 1;}
                $engagementMap['sort'] = array_merge($engagementMap['sort'] , [ $parameter =>  $min2max ] );
                $countParam++;
             } 
        }
             self::$options = array_merge ( self::$options , $engagementMap);
             return $this ;   
    }  

    public  function insert( array $arrVals ) {    // non static method  display output use after where,orwhere operation 
         if (null == self::$SequenceCollection ){ 
             $canfill =  $this->fillable( $arrVals , ['insert'=> true] ) ;  // this method going to reject insert once unmatch schema and fillable 
          }else{
             $canfill =  $this->fillable( $arrVals , ['insert'=> true , 'forceCollection' => self::$SequenceCollection ] ) ;  // this method going to reject insert once unmatch schema and fillable 
         }
         $dataTypesMapping = dataTypemapping( $this->schema[$this->getCollectNonstatic()] , $arrVals , $this->timezone , $this->dateformat ) ;  
        if( $canfill[0] == 1 ){  
            $config = new Config ;
            $config->setDb($this->getDbNonstatic()) ;
            $conclude = new BuildConnect ; 
            $reactionInsert = $conclude->insertDoc($config ,$this->getCollectNonstatic(),$dataTypesMapping) ; 
            $this->resetStaticInsert() ; // @@ reset static values for static function getSequence() reset self::$SequenceCollection to null
            return $reactionInsert ;
         }else{
            return  [ 0 ,$canfill[1]  ] ; 
         }
    } 

    public  function insertGetId( array $arrVals , string $key_return  = null ) {    // non static method  display output use after where,orwhere operation
        
        $canfill =  $this->fillable( $arrVals , ['insert'=> true] ) ;  // this method going to reject insert once unmatch schema and fillable 
        $dataTypesMapping = dataTypemapping( $this->schema[$this->getCollectNonstatic()] , $arrVals , $this->timezone , $this->dateformat ) ;  
        if( $canfill[0] == 1 ){  
            $config = new Config ;
            $config->setDb($this->getDbNonstatic()) ;
            $conclude = new BuildConnect ; 
            $inOrderArray = [] ;
            foreach ($arrVals as $arr) {  // 
                $inOrderArray = array_merge($inOrderArray , [$arr]) ;  
            }
          
             $reactionInsert = $conclude->insertDoc($config ,$this->getCollectNonstatic(),$dataTypesMapping) ; 
             
            if ($reactionInsert[0]) {   
                if ( !null == $key_return  ) {
                  return  [ true ,$reactionInsert , $dataTypesMapping[$key_return]  ] ; 
                }else{
                  return  [ true ,$reactionInsert , $inOrderArray[0]  ] ; 
                }
            }else{
                return [ $reactionInsert[0],$reactionInsert[1] , null ] ;
            }
         }else{
            return  [ 0 ,$canfill[1] , null  ] ; 
         }
       
    } 
    

  //@@ while get function 
}
