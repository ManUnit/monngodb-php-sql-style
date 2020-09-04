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

    public function join(String $collection , String $localField , String  $foreignField , String $as = " "){  
        return $this->leftjoin($collection ,$localField , $foreignField , $as  ) ;
    }

    public function leftjoin(String $collection,String $localField , String  $foreignField , String $as = " "){ 
        $asForeign = explode('.', $foreignField );
        if($asForeign[0] !== $collection ){ array_push( self::ClassError,['101' => [ "join coollection error" => "foreignField :".$asForeign[0]," prefix mismatch with join collection"  ]   ] ) ;}
        if(!isset(self::$joincollections [0]['$project'][$this->collection])) array_push(self::$joincollections , ['$project' =>['_id'=>0 , $this->collection => '$$ROOT' ]]);
         array_push(self::$joincollections  , ['$lookup' =>  [ 
                                        'localField' =>  $localField ,
                                        'from' => $collection ,
                                        'foreignField' => $asForeign[1] ,
                                        'as' => $asForeign[0]  
                                    ]]);   
        array_push(self::$joincollections  , ['$unwind'=>[ 
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
        foreach($params as $param){
               $groupby['_id'] += [  str_replace(".",dotter(),$param) => '$'. $param ];
        }
        $groupby = ['$group' => $groupby] ;
        self::$groupby = $groupby;
        return $this ;
    }

    
    public function get() {   
        $this->getAllwhere() ;  // Intregate everywhere  
        $config=new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude=new BuildConnect; 
        
        if(!null == self::$joincollections){
            self::$pipeline = array_merge(self::$pipeline,self::$joincollections) ; 
            if(!null==self::$querys)self::$pipeline=array_merge(self::$pipeline,[['$match'=>self::$querys]]) ; 
         
                   if (!null == self::$groupby )  self::$pipeline = array_merge(self::$pipeline,[self::$groupby]);   
                     foreach (self::$options as $mainkey => $mainOption){
                         if('projection'===$mainkey){   
                             $project['$project'] = [];
                             foreach (self::$options['projection'] as $key => $option ){ 
                                 substr($option,0,1) === "$" ?   
                                 $option = substr($option,1) :
                                 $option = substr($option,0) ;
                                 if ($key !== '_id') 
                                
                                 $project['$project'] = array_merge( $project['$project'] ,
                                                                     [ $key => '$_id.'.
                                                                      str_replace(".",dotter(),$option ) ]
                                                                    ); 
                            }
                            $project['$project']= array_merge( $project['$project'],[ '_id' =>  0]); 
                            self::$pipeline = array_merge(self::$pipeline,[$project]); 
                        }else{
                            self::$pipeline = array_merge(self::$pipeline,[["\$".$mainkey => $mainOption ]]); 
                        }
                    }
                    $options = [ 'allowDiskUse' => TRUE ];
            $conclude->aggregate($config,$this->collection,self::$pipeline,$options);  
            //
            // @ re-building new output
            // 
             $displayjoin = [] ;
             $joinresult = json_decode( json_encode( $conclude->result ) , true ) ;
             // Conversion to SQL data list style
             foreach ($joinresult  as $keys => $datas){ 
                 $eachdoc = [] ;
                 foreach ($datas as $key => $data) {
                     foreach($data as $in_key => $in_data ){ 
                      $eachdoc = array_merge($eachdoc , [ self::$mappingAs[$key.".".$in_key] => $in_data ]);
                     }
                }
                    $displayjoin = array_merge( $displayjoin ,[$eachdoc] );
             }
            return  $displayjoin ;
        }
        //END JOIN
        //
        //@command for group by
        //
        if(!null == self::$groupby && null ==  self::$joincollections ){ 
             $findMatch = 0 ;
             foreach( self::$pipeline as $key => $dat  ){
                 if ( $key === '$match'){ $findMatch++;}
             }
             if ( $findMatch == 0  ){
                $swap = self::$pipeline ;
                self::$pipeline = [] ;
                self::$pipeline = array_merge(self::$pipeline,[ ['$match' => self::$querys] ]);
                self::$pipeline = array_merge(self::$pipeline,$swap);
             }
               self::$pipeline = array_merge( self::$pipeline , [self::$groupby] );

             foreach (self::$options as $mainkey => $mainOption){
                        if('projection'===$mainkey){   
                            $project['$project'] = [];
                            foreach (self::$options['projection'] as $key => $option ){ 
                                substr($option,0,1) === "$" ?     
                                       $option = substr($option,1) :
                                       $option = substr($option,0) ;
                                if ($key !== '_id') $project['$project'] = array_merge( $project['$project'] , [ $key => "\$_id.$option" ]); 
                            }
                            $project['$project']= array_merge( $project['$project'],[ '_id' =>  0]); 
                            self::$pipeline = array_merge(self::$pipeline,[$project]); 
                        }else{
                            self::$pipeline = array_merge(self::$pipeline,[["\$".$mainkey => $mainOption ]]); 
                        }
            }
                $options = [
                    'allowDiskUse' => TRUE
                ];
                $conclude->aggregate($config,$this->collection,self::$pipeline,$options); 
                $renewdisplay = [] ;
                $groupresult = json_decode( json_encode( $conclude->result ) , true ) ;
       
                foreach ($groupresult  as $keys => $datas){ 
                    $docs = [] ;
                    foreach($datas as $key => $data){
                       $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                   }
                   $renewdisplay = array_merge($renewdisplay, [$docs]  );
               }
               return $renewdisplay ;
        }else{  
                $conclude->findDoc($config,$this->collection,self::$querys,self::$options); 
                $renewdisplay = [] ;
                $groupresult = json_decode( json_encode( $conclude->result ) , true ) ;
                if ( !null == self::$mappingAs ){
			if ( !null == self::$mappingAs ){
                            foreach ($groupresult  as $keys => $datas){ 
                              $docs = [] ;
                              foreach($datas as $key => $data){
                              $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                            }
                              $renewdisplay = array_merge($renewdisplay, [$docs]  );
			}else{
			    $renewdisplay = $groupresult ;
        	        }
                    }
                    return $renewdisplay ;
                }else{
                    return $groupresult ;
                }
        } 

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

 
   



     
}
