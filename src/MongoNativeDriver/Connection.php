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
       
                foreach ($groupresult  as $keys => $datas){ 
                    $docs = [] ;
                    foreach($datas as $key => $data){
                       $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                   }
                   $renewdisplay = array_merge($renewdisplay, [$docs]  );
               }
               return $renewdisplay ;
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

    public static function InitIndexAutoInc () { 
        $this->fillable( (array) [] );
    }

    public function getModifySequence(String $autoIncName) { 
        $this->fillable( (array) [] );  // Pre scan schema to create all first since empty indecies and counter collection 
        $config = new Config ;
        $config->setDb($this->getDbNonstatic());
        $conclude = new BuildConnect ;
        $result = $conclude->getModifySequence($config,$this->getCollectNonstatic(),$autoIncName); 
        return $result; 
    }

    /****** @Private Zone ******/   
   
    private function anError(int $err , $value ){
        array_push ( self::$ClassError ,   [$err,$value] ) ;
        
    }

    private function setCollection($collection){
        $this->collection = $collection ;
        return $this ; 
    }


    private function setDatabase($database){
        $this->database = $database ;
        return $this ; 
    }

    private function setDatabaseCollection($database , $collection){
        $this->database = $database ;
        $this->collection = $collection ;
        return $this ; 
    }

    private function getAllwhere(){ 
        $allAnd = ['$and'=>[]] ;
        if (  isset (self::$orderTerm) &&  count(self::$orderTerm) == 1 ){  return ;
        }elseif( count(self::$orderTerm) > 1) {
          // Find all term are AND   // to Check all term is and(s)  where()->andwhere()->andwhere()->get()
          $andCount=0; 
          foreach(  self::$orderTerm as $key => $terms ){
               if(array_keys($terms)[0]==='$and'){$andCount++;}
               array_push($allAnd['$and'],$terms[array_keys($terms)[0]] ) ; 
          }
          if( $andCount == count(self::$orderTerm) -1 ){   // All term is ANDs
            self::$querys = $allAnd ; 
            return $allAnd;
          }
        }   
        $finalwhere=self::$orderTerm;
        $beforeOps = null ;
        $beforeTerm = [] ;
        $order = 0 ;
        $termCount = 0 ;
        $terms = [] ;
        $finalTerms = ['$or'=>[]] ;
        $andTerms = ['$and'=>[]] ; 
        // Collector terms 
        // @conversion SQL to  logic precendence order using Mongodb's format
        // term (and)(and)  + term(or)(and)(and)  + term (or)(and)  + term(or) + term(or) 
      
        foreach($finalwhere as $operator => $term){  
            if(   $beforeOps == null  && array_keys($term)[0] === 'mostleft' ){ 
                $termCount++ ; 
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }elseif( $beforeOps == 'mostleft'  && array_keys($term)[0] === '$or' ){
                $termCount++ ; 
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                 array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }elseif( $beforeOps == 'mostleft'  && array_keys($term)[0] === '$and' ){
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }elseif( $beforeOps == '$or'  && array_keys($term)[0] === '$or' ){
                $termCount++ ;
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }
             elseif( $beforeOps == '$or'  && array_keys($term)[0] === '$and' ){
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }elseif( $beforeOps == '$and'  && array_keys($term)[0] === '$or' ){
                $termCount++ ; 
                if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }elseif( $beforeOps == '$and'  && array_keys($term)[0] === '$and' ){
              if(!isset($terms[$termCount])) { $terms[$termCount] = [] ;}
                array_push($terms[$termCount] , $term[array_keys($term)[0]] ); 
            }
             array_keys($term)[0] ; 
             $beforeOps = array_keys($term)[0] ;
             $beforeTerm = $term[array_keys($term)[0]] ;
        } 
        // conversion Terms to OR term
           foreach( $terms as $term){
               if ( count($term) == 1 ){ 
                    array_push ($finalTerms['$or'],$term[0]);  
                }elseif(count($term)  > 1){
                    $andTerms = ['$and'=>[]] ;
                    foreach( $term as $andTerm  ){ 
                       array_push($andTerms['$and'] , $andTerm)   ;
                    } 
                    array_push ($finalTerms['$or'],$andTerms);
                }
           } 
           if ($finalTerms == ['$or'=>[]] ){ self::$querys =[] ;}else{
               self::$querys = $finalTerms ;
           } ;
       return  self::$querys;
    }
    
    private function fillable(array $arrVals , $option = [] ) { 
        $collections=[];
        $fillables=[];
        $updateProtected=[];
        foreach ( array_keys( $this->schema ) as $each_coll  ) { 
            array_push($collections,$each_coll) ; 
        }

        if( !in_array( $this->collection , $collections)  ){
             return [0 , "Error ! collection:$this->collection aren't in member of schema check your Model class ".get_class($this) ] ;
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
                        if ( $key === "UpdateProtected"  &&  $value === true  ) { 
                           array_push( $updateProtected ,  $keys );
                        }
                            
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
  
            if (  !in_array( $key , $fillables ) ) { return  [  0 , "ERROR ! input fail -> field name:".$key. " aren't  member in schema check your Model ".get_class($this) ]; } 
            if( isset($option['update']) ){ 
                if (  in_array( $key,$updateProtected)  &&  $option['update'] == true  ){    return  [  0 , "ERROR ! update collection:". $this->collection."->feild:".$key. " has protected in ".get_class($this) ];  }
            }
        }
        return [1,"fillable OK good luck my friend ! "] ;
    }
    
    private  function findCreateAutoInc(String $fieldNameToInc , int $StartSeq  ) {
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
                                                        'sequence_value' => 0.0 + $StartSeq ]) ; // conversion datatype to be double
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
        
         if(isset($this->schema[$this->getCollectNonstatic()][$fieldIndex]['Sparse'])){
            $index_Sparse =  $this->schema[$this->getCollectNonstatic()][$fieldIndex]['Sparse'] ;
         }else{
            $index_Sparse  = false ; 
         }

        $index = [
                    "name" =>  "\$__INDEX_".strtoupper($fieldIndex)."_"  ,
                    "key"  =>  [$fieldIndex=>1] ,
                    "unique" => $index_unique  ,
                    "sparse" => $index_Sparse  ,
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

    private function whereConversion(String $Key ,String $Operation , $Value) {
          if ( $Operation  == "=" ){
              return [ "$Key"=>  $Value ];                    // SQL transform select * from table where 'key' = 'value'  ; 
          }elseif( $Operation  == "!=" ) {
              return [ "$Key" => ['$ne' => $Value ]  ];       // SQL transform select * from table where 'key' != 'value'
          }elseif($Operation  == "<="){
              return [ "$Key" => [ '$lte' =>  $Value ]  ];   // SQL transform select * from table where 'Key' <= 'value'
          }elseif($Operation  == ">="){
              return [ "$Key" => [ '$gte' =>  $Value ]  ];    // SQL transform select * from table where 'Key' >= 'value'
          }elseif($Operation  == "<"){
              return [ "$Key" => [ '$lt' =>  $Value ]  ];     // SQL transform select * from table where 'Key' < 'value'
          }elseif($Operation  == ">"){
              return [ "$Key" => [ '$gt' =>   $Value ]  ];    // SQL transform select * from table where 'Key' > 'value'
          }elseif( $Operation  == "like" ) {
              if (   $Value[0]  != "%" && substr(  "$Value" , -1 ) =="%"  ) { 
                 return [  "$Key" => new Regex('^'. substr( "$Value" ,0,-1 ) .'.*$', 'i') ]  ;     // SQL transform select * from table where 'Key' like 'value%'   ; find begin with ?    
              }elseif (   $Value[0]  == "%"  &&  substr( "$Value" , -1 ) !=  "%"  ) {
                  return [  "$Key" => new Regex('^.*'.substr( "$Value" ,1 ) .'$', 'i') ];          // SQL transform select * from table where 'Key' like '%value'   ; find end with ?
              }elseif (  $Value[0]  == "%"  &&  substr( "$Value" , -1 ) =="%"   ) {
                  return [ "$Key" => new Regex('^.*'.substr( "$Value" ,1 ,-1)  .'.*$', 'i')];     // SQL transform select * from table where 'Key' like '%value%'  ; find where ever with ?
              }else{
                  return [ "$Key" => new Regex('^.'."$Value".'.$', 'i')];   //  SQL transform select * from table where 'key' like 'value'
              }
          }
      }
     
}
