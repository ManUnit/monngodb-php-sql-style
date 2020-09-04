<?php
namespace  Nantaburi\Mongodb\MongoNativeDriver ;

trait  Classifier {

    private  function getJoin(object $conclude , object $config , $is_paginate = null){
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


    private function getGroup (object $conclude , object $config , $is_paginate = null) {
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

    private function getFind(object $conclude ,object $config , $is_paginate = null ){  
        if( $is_paginate == true ){ unset(self::$options['limit']); }
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

}