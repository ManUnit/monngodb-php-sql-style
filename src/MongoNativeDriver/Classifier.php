<?php
namespace  Nantaburi\Mongodb\MongoNativeDriver ;

trait  Classifier {
    
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
        // @@Collector terms 
        // @@conversion SQL to  logic precendence order using Mongodb's format
        // @@term (and)(and)  + term(or)(and)(and)  + term (or)(and)  + term(or) + term(or) 
      
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

    private  function getJoin(object $conclude , object $config ,int $perpage = null, $is_paginate = null){
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


    private function getGroup (object $conclude , object $config ,int $perpage  = null , $is_paginate = null) {
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
    } 

    private function getFind(object $conclude ,object $config ,int $perpage=null,$is_paginate=null ){  
        if( $is_paginate == true && !null == self::$options['limit']  ){
             unset(self::$options['limit']); 
        }

        $conclude->findDoc($config,$this->collection,self::$querys,self::$options); 
        $renewdisplay = [] ;
        $findResult = json_decode( json_encode( $conclude->result ) , true ) ;
        if ( !null == self::$mappingAs ){
                foreach ($findResult  as $keys => $datas){ 
                    $docs = [] ;
                    foreach($datas as $key => $data){
                    $docs = array_merge($docs, [self::$mappingAs[$key]  => $data ]  );
                }
                $renewdisplay = array_merge($renewdisplay, [$docs]  );
            }
            return $renewdisplay ;
        }else{
            return $findResult ;
        }
    }

    private function countDoc () {
        
    }

}