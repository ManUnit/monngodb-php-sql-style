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
require  __DIR__."/../Operators/AdvanceOps.php" ;  // get myown global function
use Nantaburi\Mongodb\MongoNativeDriver\Config ;
use Nantaburi\Mongodb\MongoNativeDriver\BuildConnect ;
use Nantaburi\Mongodb\MongoNativeDriver\Compatible ;
use PHPUnit\Framework\Exception;
use MongoDB\BSON\Regex; 


class Connection extends Compatible {  //defind the Class to be  master class

    // Call trait class
    use Commands ; 
    // Public , Protected  non-static properties  of values $this 
    /*  Static properties of self::$values 
     *
     */

    //
    protected $collection = 'dummyString';
    protected $database = 'dummyString' ;
    protected $fillable = array();    
     // Private properties 
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
     //   return (new static)->$method(...$parameters);
         if ( $method = "database" ){ 
             if(count($parameters) == 0) {
                     return (new static)->setDatabaseCollection((new static)->database , (new static)->collection);
             }elseif ( count($parameters) == 1 ){
                     return (new static)->setDatabase("$parameters[0]");
             }elseif(count($parameters) == 2){
                     return (new static)->setDatabaseCollection("$parameters[0]" , "$parameters[1]" );
             }
         }
    }
 
    
    public static function collection($coll=''){
        // support defind value with nothing 
        if ($coll == '' ) return (new static)  // get  default collection from model when nothing value in collection()
                         ->setCollection(
                              (new static)->getCollectNonstatic()
                         ) ;
        return  (new static)->setCollection($coll); 
    }

    public static function query($coll=''){
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
       // dd(__file__.":".__line__,$joinCollection,$localField ,$foreignField ,$as);
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

    public function getCollectNonstatic(){
        return $this->collection ; 
     }
    public function getDbNonstatic(){
       return $this->database ; 
    }
    public function newQuery() {
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

    public static function update(...$param ){ 
        if (self::$querys == null ){ return [0,"Error missing where query document require method  where() before andupdate() or update() example where(...)->andupdate()->update() "] ;}
        if( count($param) == 1 && is_array($param[0]) ){ 
            $canfill =  (new static)->fillable( $param[0] , ['update' => true] ) ; 
            if( $canfill[0] == 0){  return  $canfill ;}
            self::$updates = [ '$set' => [ $param ] ] ;
            }elseif(count($param) == 2){
            $canfill =  (new static)->fillable([$param[0]=>$param[1]] , ['update' => true]  ); 
            if( $canfill[0] == 0){  return  $canfill ;}
            self::$updates = [ '$set' => [ $param[0] => $param[1]] ];
        }elseif(count($param) == 3  && $param[1] === '=' ){
            $canfill =  (new static)->fillable([$param[0]=>$param[2]] , ['update' => true]   ); 
            if( $canfill[0] == 0){  return  $canfill ;}
            self::$updates = [ '$set' => [ $param[0] => $param[2]] ];
        }elseif(count($param) > 3){
            return [0,"update format expect 3 argement you put " . count($param) ];
        }else{
            return [0,"update input style format are not support for the support format to be following : update('id',1) or update('name','=', 'Nutter') 
            and you can multiply field with array update([ 'name' => 'Supachai' ,'lastname' => 'W.','address' => 'Pangpoi city' ]" ] ;
        }
        $config=new Config ;
        $config->setDb((new static)->getDbNonstatic()) ;
        $conclude = new BuildConnect ;
        if (!isset(self::$updates['$set'])&&self::$andupdates==!null)self::$updates=['$set'=>[]];
        $canfill =  (new static)->fillable(self::$andupdates ,  ['update' => true]   ); 
        if( $canfill[0] == 0){  return  $canfill ;}
        self::$updates['$set'] = array_merge(  self::$updates['$set'], self::$andupdates );
        $updateresult = $conclude->updateDoc( $config ,(new static)->getCollectNonstatic() ,self::$querys , self::$updates  ) ; 
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
        self::$pre_groupby = array_merge(['$selected'=>true], removeAs($fields)  ); 
        if(count($fields)== 1 && $fields[0] === '*'  )return $this;
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
        //  $ClassError = array();
        //  $querys = array();
        //  $combind = array();
        //  $options = array(); 
        //  $limits = array(); 
        //  $orderTerm = array();
        //  $pipeline = array();
        //  $updates = array();
        //  $andupdates = array();
        //  $joincollections = array();
        //  $groupby = array();
        //  $mappingAs = array();
        if(env('Connection DEV_DEBUG@get',false)){
            dd("test 001 this " ,
            "DEBUG status : " , 
            env('DEV_DEBUG',false) , 
            " AS OPTIONS" , 
            self::$options ,
            "AS MAP" ,
            self::$mappingAs , 
            "Query " , 
            self::$querys  ,
            'Join' , 
            self::$joincollections ,
            'this' , 
            $this) ; // dev debug
        }
        $this->getAllwhere() ;  // Intregate where everywhere  
        //dd(__file__.":".__line__,self::$querys);
        if(!null == self::$joincollections){ 
           if(env('DEV_DEBUG'))print  (__file__.":".__line__ ."connection@DEBUG -> find join : <br>\n") ;
           return $this->findJoin() ;
        }
        //
        //@command for group by
        //
        if(!null == self::$groupby && null ==  self::$joincollections ){ 
           if(env('DEV_DEBUG')) print  ("connection@DEBUG find group : <br>\n") ;
           return $this->findGroup() ; 
        }else{     // @ normal find 
          if(env('DEV_DEBUG')) print  (__file__.":".__line__ ."<br> ------> connection@DEBUG find normal : <br>\n") ;
         // dd( self::$querys);
          return  $this->findNormal() ;
        } 

       // return $conclude->result ;
      
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
            //checker : Engagement ASC DESC with pair parameters  
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
        $canfill =  $this->fillable( $arrVals , ['insert'=> true] ) ;  // this method going to reject insert once unmatch schema and fillable 
        if( $canfill[0] == 1 ){  
            $config = new Config ;
            $config->setDb($this->getDbNonstatic()) ;
            $conclude = new BuildConnect ; 
            $reactionInsert = $conclude->insertDoc($config ,$this->getCollectNonstatic(),$arrVals ) ; 
            return  [ 1 ,$reactionInsert ] ; 
         }else{
            return  [ 0 ,$canfill[1]  ] ; 
         }
    } 

  //@@ while get function 
  
   
     
}
