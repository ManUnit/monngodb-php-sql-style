<?php

/*
 *
 *  Nandev :
 *  Create by : Anan Paenthongkham
 *  Update : 2020-7-27
 *  Class Connection 
 *  version 1.0
 *  revision 1.1
 */

namespace Nantaburi\Mongodb\MongoNativeDriver ;

use Nantaburi\Mongodb\MongoNativeDriver\Classifier ;
use PHPUnit\Framework\Exception;
use MongoDB\BSON\Regex;

trait Commands {    
    use Classifier ;  
    
   


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

    public function paginate(int $perpage , string  $viewlinkfile = '') {   
        $this->getAllwhere() ;  // Intregate where everywhere         
        if(!isset($_GET['page'] )){$page=(int) 1 ;}else{  $page= (int) $_GET['page'];}
        $outlet = new Outlet ;
        $outlet->items = $this->pageget($perpage)['items'];
        $outlet->total = $this->pageget($perpage)['totaldocuments'];
        $outlet->perPage = $perpage;
        $outlet->path = 'http'.(empty($_SERVER['HTTPS'])?'':'s').'://'.$_SERVER['HTTP_HOST'].'/'.str_replace ('/','',$_SERVER['REQUEST_URI']);
        $outlet->currentPage = $page  ; 
        $this->getAllwhere()  ;  //@@@ update latest query
        $outlet->query = self::$querys ; 
        $outlet->pageName = 'page'  ; 
        $outlet->viewlinkfile = $viewlinkfile;  
        $outlet->links   = $this->pagedrawing($perpage ,$outlet->total,$page ) ;      
        $outlet->options   =   array_merge($outlet->options , [ 'path' => $outlet->path , 'pageName' => $outlet->pageName  ]) ; 
        return $outlet ;
    }

    private function pageget(int $perpage) {   
        $this->getAllwhere() ;  // Intregate everywhere  
        $config=new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude=new BuildConnect; 
        $options = [] ; 
        $setFind = 0 ;
        $totalDocment = 1 ; 
        if(!isset($_GET['page'] )){$request_page_number=(int) 1 ;}else{  $request_page_number= (int) $_GET['page'];}
            // @ adding 
            if(!null == self::$joincollections){ 
                $setFind = 'join' ; 
              $count_products =  $this->findJoin([ 'count' => true ]) ;
              $totalDocment=isset(json_decode(json_encode($count_products))[0]->count) ? json_decode(json_encode($count_products))[0]->count : 0;
              //$totalDocment=json_decode(json_encode($count_products))[0]->count;
            }elseif(!null == self::$groupby && null ==  self::$joincollections ){ 
                $setFind = 'group' ; 
              $count_products =  $this->findGroup([ 'count' => true ]) ; 
              $totalDocment=isset(json_decode(json_encode($count_products))[0]->count) ? json_decode(json_encode($count_products))[0]->count : 0;
            }else{     // @ normal find 
                $setFind ='normal' ; 
            //  if(env('DEV_DEBUG')) print  (__FILE__. " : "  . __LINE__ ." : DEBUG paginate find normal : <br>\n") ;
              $count_products =  $this->findNormal([ 'count' => true ]) ;
              $totalDocment=isset(json_decode(json_encode($count_products))[0]->count) ? json_decode(json_encode($count_products))[0]->count : 0;
            } 
        //@@=================
        //@@----- Paginate Limit documents calculation --// 
        //@@^above line was  shortcut of $totalDocment= json_decode( json_encode(  $count_products ) ) ; $totalDocment[0]->count  
        //@@ Convert skip to selected page 
        $totalpage=(int) ($totalDocment / $perpage);
        if (($totalDocment% $perpage) !=0) $totalpage=$totalpage+1;
        if ($request_page_number > $totalpage) $request_page_number=$totalpage; // limit when over request
        if ($request_page_number < 1) $request_page_number=1; // set positive number  lowest page limiter 
        $page_offset=($request_page_number - 1) * $perpage; // find skip number 
        if ($request_page_number > $totalpage) $request_page_number=$totalpage; // max page limiter
         if ( $setFind == 'normal'){ 
            array_push ($options, [ 'skip'=> $page_offset]);
            array_push ($options, [ 'limit'=> $perpage]);
             $normalout =   $this->findNormal([ 'count' => false  , 'options' => $options ,'perpage'=> $perpage ]) ;
             return ['items'=>$normalout , 'totaldocuments' => $totalDocment , 'totalpage' => $totalpage ] ;
         }elseif($setFind == 'group'){
            array_push ($options, [ '$skip'=> $page_offset]);
            array_push ($options, [ '$limit'=> $perpage]);
            $groupout = $this->findGroup([ 'count' => false  , 'options' => $options ,'perpage'=> $perpage ]) ;
            return ['items'=>$groupout , 'totaldocuments' => $totalDocment , 'totalpage' => $totalpage ] ;
         }elseif($setFind == 'join'){
            array_push ($options, [ '$skip'=> $page_offset]);
            array_push ($options, [ '$limit'=> $perpage]);
            $joinout = $this->findJoin([ 'count' => false  , 'options' => $options ,'perpage'=> $perpage ]) ;
            return ['items'=>$joinout, 'totaldocuments' => $totalDocment , 'totalpage' => $totalpage ] ;
         }

       return $conclude->result ;
    } 
       
    public function first (){
        $this->limit(1);
        return $this->get(); 
    }
    /*
    *
    * @Pagedraw calculation page number 
    *
    */
    public static function pagedrawing($perpage, $totaldocument, $request_page_number) {
      
        $stly_class="page-item";
        $stly_class_opt_active='active';
        $stly_class_opt_disabled="disabled";
        $totalpage=(int) ($totaldocument / $perpage);
        if (($totaldocument % $perpage) !=0) $totalpage=$totalpage+1;   // mod checking 
        $data_array=array();
        $start_range=7;
        if ($totalpage > 1) {
            if ($request_page_number==1) {
                $clickable=0;
                $pagevalue=null;
                $class_using=$stly_class." ".$stly_class_opt_disabled;
            }else {
                $clickable=1;
                $pagevalue=$request_page_number - 1;
                $class_using=$stly_class;
            };
            array_push ($data_array, ['page'=> $pagevalue, 'selected'=> 0, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> '<']); // option push at start
        }

        if ($totalpage >=2 && $totalpage <=11) {
            for ($i=1; $i <=$totalpage; $i++) {
            
                if ((int) $request_page_number===$i) {
                    $selected=1;
                    $clickable=0;
                    $class_using=$stly_class." ".$stly_class_opt_active;
                }else {
                    $selected=0;
                    $clickable=1;
                    $class_using=$stly_class;
                };
                array_push ($data_array, ['page'=> $i, 'selected'=> $selected, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> strval($i)]);
            }
        }

        elseif ($totalpage > 11) {
            if ($request_page_number < $start_range) {
                $start_edge=8;
            }else {
                $start_edge=2;
            }

            for ($i=1; $i <=$start_edge; $i++) {

                if ((int) $request_page_number===$i) {
                    $selected=1;
                    $clickable=0;
                    $class_using=$stly_class."  ".$stly_class_opt_active;
                }else {
                    $selected=0;
                    $clickable=1;
                    $class_using=$stly_class;
                };
                array_push ($data_array, ['page'=> $i, 'selected'=> $selected, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> strval($i)]);
            }
            array_push ($data_array, ['page'=> null, 'selected'=> 0, 'clickable'=> 0, 'stly_classes'=> $stly_class." ".$stly_class_opt_disabled, 'icon'=> "..."]);
            if ($request_page_number >=$start_range && $request_page_number <=$totalpage - 6) {
                $middle_range=$request_page_number+3;
                $middle_start_count=$request_page_number - 3;
            }else {
                $middle_range=0;
                $middle_start_count=1; // to disable middle
            };

            for ($i=$middle_start_count; $i <=$middle_range; $i++) {
                if ((int) $request_page_number===$i) {
                    $selected=1;
                    $clickable=0;
                    $class_using=$stly_class."  ".$stly_class_opt_active;
                }else {
                    $selected=0;
                    $clickable=1;
                    $class_using=$stly_class;
                };
                array_push ($data_array, ['page'=> $i, 'selected'=> $selected, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> strval($i)]);
            }
            if ((int) $request_page_number <=$totalpage - 6) {
                if ($request_page_number > 6) array_push ($data_array, ['page'=> null, 'selected'=> 0, 'stly_classes'=> $stly_class." ".$stly_class_opt_disabled, 'clickable'=> 0, 'icon'=> "..."]);
                for ($i=$totalpage - 1; $i <=$totalpage; $i++) {
                    if ((int) $request_page_number===$i) {
                        $selected=1;
                        $clickable=0;
                        $class_using=$stly_class."  ".$stly_class_opt_active;
                    } else {
                        $selected=0;
                        $clickable=1;
                        $class_using=$stly_class;
                    };
                    array_push ($data_array, ['page'=> $i, 'selected'=> $selected, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> strval($i)]);
                }
            }else {
                for ($i=$totalpage - 8; $i <=$totalpage; $i++) {
                    if ((int) $request_page_number===$i) {
                        $selected=1;
                        $clickable=0;
                        $class_using=$stly_class."  ".$stly_class_opt_active;
                    }else {
                        $selected=0;
                        $clickable=1;
                        $class_using=$stly_class;
                    };
                    array_push ($data_array, ['page'=> $i, 'selected'=> $selected, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> strval($i)]);
                }
            }


        }

        if ($totalpage > 1) {
            if ($request_page_number==$totalpage) {
                $clickable=0;
                $request_page_number=null;
                $class_using=$stly_class." ".$stly_class_opt_disabled;
            }else {
                $clickable=1;
                $request_page_number++;
                $class_using=$stly_class;
            };
            array_push ($data_array, ['page'=> $request_page_number, 'selected'=> 0, 'clickable'=> $clickable, 'stly_classes'=> $class_using, 'icon'=> '>']); // option push at end
        }
        return $data_array;
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
    
    private function whereConversion(String $Key ,String $Operation , $Value) {
        if ( $Operation  === "=" ){
            return [ "$Key"=>  $Value ];                    // SQL transform select * from table where 'key' = 'value'  ; 
        }elseif( $Operation  === "!=" ) {
            return [ "$Key" => ['$ne' => $Value ]  ];       // SQL transform select * from table where 'key' != 'value'
        }elseif($Operation  === "<="){
            return [ "$Key" => [ '$lte' =>  $Value ]  ];   // SQL transform select * from table where 'Key' <= 'value'
        }elseif($Operation  === ">="){
            return [ "$Key" => [ '$gte' =>  $Value ]  ];    // SQL transform select * from table where 'Key' >= 'value'
        }elseif($Operation  === "<"){
            return [ "$Key" => [ '$lt' =>  $Value ]  ];     // SQL transform select * from table where 'Key' < 'value'
        }elseif($Operation  === ">"){
            return [ "$Key" => [ '$gt' =>   $Value ]  ];    // SQL transform select * from table where 'Key' > 'value'
        }elseif($Operation  === "in"){
            return [ "$Key" => [ '$in' =>   $Value ]  ];    // SQL transform select * from table where 'Key' in  [1,2,3]   // in array
        }elseif($Operation  === "nin"){
            return [ "$Key" => [ '$nin' =>   $Value ]  ];    // SQL transform select * from table where 'Key' nin  [1,2,3]   // in array
        }elseif( $Operation  === "like" ) {
            if (   $Value[0]  !== "%" && substr(  "$Value" , -1 ) =="%"  ) { 
               return [  "$Key" => new Regex('^'. substr( "$Value" ,0,-1 ) .'.*$', 'i') ]  ;     // SQL transform select * from table where 'Key' like 'value%'   ; find begin with ?    
            }elseif (   $Value[0]  === "%"  &&  substr( "$Value" , -1 ) !==  "%"  ) {
                return [  "$Key" => new Regex('^.*'.substr( "$Value" ,1 ) .'$', 'i') ];          // SQL transform select * from table where 'Key' like '%value'   ; find end with ?
            }elseif (  $Value[0]  === "%"  &&  substr( "$Value" , -1 ) ==="%"   ) {
                return [ "$Key" => new Regex('^.*'.substr( "$Value" ,1 ,-1)  .'.*$', 'i')];     // SQL transform select * from table where 'Key' like '%value%'  ; find where ever with ?
            }else{
                return [ "$Key" => new Regex('^.'."$Value".'.$', 'i')];   //  SQL transform select * from table where 'key' like 'value'
            }
        }else{
               throw new Exception(" Error operator   '$Operation'  not support  for this module ");
        }
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
      
    
    private function findCreateIndexOne(String $fieldIndex){  
        // if(env('DEV_DEBUG')){
        //    print(__file__ .":".__line__ . " function findCreateIndexOne(" . $fieldIndex .")<br>") ;
        // }

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
              print(__file__ .":".__line__ . " Try create  <br>" . " ". $this->getCollectNonstatic()  . "<br> " )  ; 
              print_r($index) ; 
              $reactionInsert = $conclude->createIndex($config , $this->getCollectNonstatic() , $index  );
          }else{
              $reactionInsert = false ;
          }  
         return  $reactionInsert  ; 
    }

    private function findCreateIndexAutoInc(String $fieldIndex , String $collection ){  
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
        $index = [
                    "name" =>'$__IDX_AUTOINC_'.$this->getDbNonstatic()."_counters",
                    "key"  =>['inc_field'=>1,'collection'=>1],
                    "unique" => true ,
                    "ns" => $config->getDb().".".$this->getDbNonstatic()."_counters"
                ];
        $result =  $conclude->getIndex($config ,  $this->getDbNonstatic()."_counters" , $index['name'] );
       // dd(__file__.":".__line__ , $this->getCollectNonstatic() , $index['name'],$result ); 
         if ( !$result ) { 
              $reactionInsert = $conclude->createIndex($config ,  $this->getDbNonstatic()."_counters" , $index  );
          }else{
              $reactionInsert = false ;
          }  
         return  $reactionInsert  ; 
    }

    private  function findCreateAutoInc(String $fieldNameToInc , int $StartSeq  ) {
        $config = new Config ;
        $config->setDb( $this->getDbNonstatic() ) ;
        $conclude = new BuildConnect ; 
        $collection_counter  = $this->getDbNonstatic().'_counters' ; 
        $this->findCreateIndexAutoInc( $fieldNameToInc, $this->getCollectNonstatic() ) ; // Magic create index 
       // dd(__file__.":".__line__ );
        $query = [  'inc_field' => $fieldNameToInc , 'collection' => $this->getCollectNonstatic() ]  ;
       //  dd(__file__."".__line__ , $config , $collection_counter ,$query ) ;
        $conclude->findDoc($config , $collection_counter ,$query ) ; 
         if ( null == $conclude->result ) {
           
            $reactionInsert = $conclude->insertDoc($config ,$collection_counter ,[
                                                     	'inc_field' => $fieldNameToInc ,
						                            	'collection'=> $this->getCollectNonstatic(),
                                                        'sequence_value' => 0.0 + $StartSeq ]) ; // conversion datatype to be double
         }  
        
        return $conclude->result ; 
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
                     
                     //   if (env('DEV_DEBUG')){ print ( __file__.":".__line__ ."  fillable  , key : $keys => $value <br>"  );  }
                       
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

     
    public function findNormal(array $argv = null ) { 
        $paginate_options = '' ; 
        $paginate_perpage = null ; 
       
        if ( isset($argv['perpage']) &&  $argv['perpage'] != null ){ $paginate_perpage = $argv['perpage'] ;} 
        if ( isset($argv['options']) &&  $argv['options'] != '' ){ $paginate_options = $argv['options'] ;} 
        if ( isset($argv['count']) && $argv['count'] == true ){  
            $pipeline = [   ['$match' =>  self::$querys  ],
                            ['$group' => [ '_id' => [],  'COUNT(*)' => [ '$sum' => 1 ]  ] ],
                            ['$project' => [ 'count' => '$COUNT(*)', '_id' => 0  ]
                        ]
            ];
        }else{
            $pipeline = [] ;
        }
        
        if(env("DEV_DEBUG"))  print(  __FILE__. " : " . __LINE__ ." : ---> In find normal <br>\n") ; 
        $config=new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude=new BuildConnect;  

       if ( isset($argv['count']) && $argv['count'] == true ){ 
            $options = [
                'allowDiskUse' => true
            ];
           $conclude->aggregate($config,$this->collection,$pipeline,$options); 
           return $conclude->result ;  //@@ Just find count of document value
       }elseif(!isset($argv['count'])||(isset($argv['count'])&&$argv['count'] == false ) ){
            $modify_limit = [] ;
            if ($paginate_options != '' ){  // @ Call by paginate 
                        $modify_limit =  self::$options ; 
                        $modify_limit = array_merge($modify_limit,['limit' => $paginate_options[1]['limit'] , 'skip' => $paginate_options[0]['skip']   ]   ) ;
                        $conclude->findDoc($config,$this->collection,self::$querys,$modify_limit); 
                    }else{   // @call findnormal with out paginate 

                        $conclude->findDoc($config,$this->collection,self::$querys,self::$options); 
                    }
       } 

   
        $renewdisplay = [] ;
        $groupresult = json_decode( json_encode( $conclude->result ) , true ) ;
            if ( !null == self::$mappingAs ){
                            foreach ($groupresult  as $keys => $datas){ 
                            $docs = [] ;
                     
                            foreach($datas as $key => $data){
                                $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                            }
                                $renewdisplay = array_merge($renewdisplay, [$docs]  );
                            }
                           // dd($renewdisplay);
                            return $renewdisplay ;
            }else{
                return $groupresult ;
            }
   } 
    public function findGroup (array $argv = null ) {  
           //  if(env('DEV_DEBUG'))  dd( __file__ ." : ". __line__,$argv['count']) ; 
           // findGroup([ 'count' => false  , 'options' => $options ,'perpage'=> $perpage ])  
        
           $paginate_options = '' ; 
           $paginate_perpage = null ; 
           $query_random  = null ; 
           if ( isset($argv['random']) &&  $argv['random'] != null ){ $query_random = (int) $argv['random'] ;} 
           if ( isset($argv['perpage']) &&  $argv['perpage'] != null ){ $paginate_perpage = $argv['perpage'] ;} 
           if ( isset($argv['options']) &&  $argv['options'] != '' ){ $paginate_options = $argv['options'] ;} 
  
            $config=new Config ;
            $config->setDb($this->getDbNonstatic()) ;
            $conclude=new BuildConnect; 
            $findMatch = 'null'  ;
            $group_count = [ '$count' => "count" ] ;
            foreach( self::$pipeline as $key => $dat  ){
                if ( isset(self::$pipeline[$key]['$match'])){ $findMatch = $key ; break ;}
            }
          
            if ( $findMatch === 'null' && !null ==  self::$querys ){ 
               // dd(__file__.":".__line__ , " No match "  ,self::$pipeline ,self::$querys ) ;
                $swap = [] ;
                $swap = self::$pipeline ;
                // $group_project = [ '$project' => [ 'count' => '$COUNT(*)','_id' => 0 ]  ] ; 
                self::$pipeline = [] ;
                self::$pipeline = array_merge(self::$pipeline,[ ['$match' => self::$querys] ]);
                self::$pipeline = array_merge(self::$pipeline,$swap);
               
           }

           $foundGroup = 'null' ;
           foreach(self::$pipeline as $key => $values) {
                   if( isset(self::$pipeline[$key]['$group'])  ){
                    $foundGroup = $key ;
                       break ;
                   } ;
           }
           // dd(__file__.":".__line__,self::$groupby);
           if ($foundGroup === 'null' ) {
            self::$pipeline = array_merge( self::$pipeline , [self::$groupby] );
           }
           // @@ End add $match 
           //@@  search $project in pipeline 
            $foundProject = 'null' ;
            foreach(self::$pipeline as $key => $values) {
                    if( isset(self::$pipeline[$key]['$project'])  ){
                        $foundProject = $key ;
                        break ;
                    } ;
            }
            if( $foundProject === 'null' ) { 
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

            }
            $options = [
                'allowDiskUse' => TRUE
            ];

            if(isset($argv['count']) && $argv['count'] == true  ){  
                $modifyPipeline = self::$pipeline ; 
               
                //@@ find where index number  is  $group  and index number $projet  
                 $index_project=0;
                 foreach ( $modifyPipeline as $key => $values) {  // @@ index project finder
                    if(key($values)=='$project'){ $index_project = $key ; } 
                }
                
              unset($modifyPipeline[$index_project]['$project']); //@@ remove project
              $modifyPipeline[$index_project] = array_merge($modifyPipeline[$index_project] , $group_count  ) ; 
              // @@ remove limit and skip
              foreach ($modifyPipeline as $key => $values) {
                     if( isset($modifyPipeline[$key]['$skip'])){ unset($modifyPipeline[$key]) ;}
                     if( isset($modifyPipeline[$key]['$limit'])){ unset($modifyPipeline[$key]) ;}
              }
            
              $conclude->aggregate($config,$this->collection,$modifyPipeline,$options);
              return $conclude->result ;
               
            }elseif( !isset($argv['count']) || isset($argv['count']) && $argv['count'] == false  ) { 
                if ( env('DEV_DEBUG') ){ print (__file__.":".__line__  ." FIND GROUP<br>\n" ) ; print_r ( self::$pipeline ) ; print ("<br>") ; } 
                if (!null == $paginate_perpage) { 
                    $modify_pipeline =   self::$pipeline  ; 
                    
                    $reindex_pipline = [] ;
                     //@@ find limit and skip 
                    $findLimit='null';

                    foreach ($modify_pipeline as $key => $values) {
                        if( isset($modify_pipeline[$key]['$skip'])){ unset($modify_pipeline[$key]) ;}
                        if( isset($modify_pipeline[$key]['$limit'])){ unset($modify_pipeline[$key]) ;}
                    } 
                    // @@ Re index number start with 0
                    foreach ( $modify_pipeline as $key => $values) {
                        if( isset( $modify_pipeline[$key]['$match'])){ 
                            if ( null ==  $modify_pipeline[$key]['$match']) unset( $modify_pipeline[$key]) ;
                        }else{ 
                            $reindex_pipline = array_merge($reindex_pipline,[$modify_pipeline[$key]]); 
                        }
                    }

                    $reindex_pipline = array_merge($modify_pipeline , $paginate_options ) ;  
                    $conclude->aggregate($config,$this->collection,$reindex_pipline,$options); 
                }else{ 
                    $modify_pipeline = self::$pipeline ;
                    $query_random != null ? $modify_pipeline  = array_merge ($modify_pipeline , [ [ '$sample' => [  "size" => $query_random ]  ] ] ) : $modify_pipeline = self::$pipeline  ;  
                     // dd(__file__ . __line__ , $modify_pipeline) ; 
                    $conclude->aggregate($config,$this->collection,$modify_pipeline,$options); 
                } 

                $renewdisplay = [] ;
                $groupresult = json_decode(json_encode( $conclude->result ) , true ) ; 
                foreach ( self::$pipeline as $key => $values ) {
                     if ( isset(self::$pipeline[$key]['$group']["_id"]['*'] ) )  throw new Exception(" Query  with select('*') with groupby() not support. you have to select at least one field ")  ;
                }
             
                foreach ($groupresult  as $keys => $datas){ 
                    $docs = [] ;
                    foreach($datas as $key => $data){
                        $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                    }
                    $renewdisplay = array_merge($renewdisplay, [$docs]  );
                }
                return $renewdisplay ;
            }
            return [] ; // @@ Nothong 
    } 

    public function findJoin(array $argv=null ) { 
        $paginate_options = '' ; 
        $paginate_perpage = null ; 
        $query_random  = null ; 
        if ( isset($argv['random']) &&  $argv['random'] != null ){ $query_random = $argv['random'] ;} 
        if ( isset($argv['perpage']) &&  $argv['perpage'] != null ){ $paginate_perpage = $argv['perpage'] ;} 
        if ( isset($argv['options']) &&  $argv['options'] != '' ){ $paginate_options = $argv['options'] ;} 

        $config=new Config ;
        $config->setDb($this->getDbNonstatic()) ;
        $conclude=new BuildConnect; 
        //@@ find ready merged 
      if(self::$pipeline == null){
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
                    if ($key !== '_id'  ) { 
                        if ( !null == self::$groupby ) {
                            $project['$project'] = array_merge( $project['$project'] ,
                            [ $key => '$_id.'. str_replace(".",dotter(),$option ) ]
                           ); 
                        }else{
                           $project['$project'] = array_merge( $project['$project'] , [ $key => '$'.$option  ]   ); 
                        }
                    }
                }
              $project['$project']= array_merge( $project['$project'],[ '_id' =>  0]); 
              self::$pipeline = array_merge(self::$pipeline,[$project]); 
           }else{
              self::$pipeline = array_merge(self::$pipeline,[["\$".$mainkey => $mainOption ]]); 
           }
     }
    }

    foreach ( self::$pipeline as $key => $values ) {
        if ( isset(self::$pipeline[$key]['$group']["_id"]['*'] ) )  throw new Exception(" Query  with select('*') with groupby() not support. you have to select at least one field ")  ;
    }


    $options = [ 'allowDiskUse' => TRUE ]; 
        if(isset($argv['count']) && $argv['count'] == true ){
            $modifyPipeline = self::$pipeline ;
            foreach ($modifyPipeline as $key => $values) {
                if( isset($modifyPipeline[$key]['$skip'])){ unset($modifyPipeline[$key]) ;}
                if( isset($modifyPipeline[$key]['$limit'])){ unset($modifyPipeline[$key]) ;}
                if( isset($modifyPipeline[$key]['$project']) && $key != 0 ){ unset($modifyPipeline[$key]) ;}
            } 
            $modifyPipeline = array_merge($modifyPipeline , [['$count' => 'count']] ) ;
            $conclude->aggregate($config,$this->collection,$modifyPipeline,$options); 
            return $conclude->result ;
        }

        if(!isset($argv['count']) || isset($argv['count']) && $argv['count'] == false){
            if(!null == $paginate_perpage){  //@@ FindJoin as pagination
                $modifyPipeline = self::$pipeline ;
                
                foreach ($modifyPipeline as $key => $values) {
                  if( isset($modifyPipeline[$key]['$skip'])){ unset($modifyPipeline[$key]) ;}
                  if( isset($modifyPipeline[$key]['$limit'])){ unset($modifyPipeline[$key]) ;}
                }
               
                $modifyPipeline = array_merge($modifyPipeline,$paginate_options) ; 
                $conclude->aggregate($config,$this->collection,$modifyPipeline,$options); 
            }else{
                $modifyPipeline = self::$pipeline ;
                $query_random != null ? $modifyPipeline  = array_merge ($modifyPipeline , [ [ '$sample' => [  "size" => $query_random ]  ] ] ) : $modifyPipeline = self::$pipeline  ;  
                $conclude->aggregate($config,$this->collection,$modifyPipeline,$options); 
            }
            $displayjoin = [] ;
            $joinresult = json_decode( json_encode( $conclude->result ) , true ) ; 

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
    } //@@end function findJoin
}