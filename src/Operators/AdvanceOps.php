<?php
namespace Nantaburi\Mongodb\MongoNativeDriver ;



// Global function
function dotter(){
       return '᎐' ;
}

function asmap( array $fields ){
       $letdisplay=[];
       
       foreach($fields as $field){  
            $rebuldfield=[];
              $splitfields=explode(' ',$field);    
                $foundAs = false;    
                 // $splitfields=$rebuldfield;
                if(is_array($splitfields) &&  count($splitfields) >1 ){  
                    foreach($splitfields as $in_field ){
                        if( 'as' === strtolower($in_field)){ $foundAs = true  ;  } ;
                    }
                }
                
                foreach($splitfields as $_fields){
                    if($_fields !== ''){
                        $rebuldfield=array_merge($rebuldfield , [$_fields]);
                    }
                }
                  if($foundAs){ 
                      $letdisplay = array_merge( $letdisplay ,[ $rebuldfield[0] => $rebuldfield[2] ]  );
                  }else{
                      $letdisplay = array_merge( $letdisplay , [ $field => $field ]);
                  }
              
       }
 return  $letdisplay ;
}

function removeAs($fields){
    $letdisplay=[];
       
    foreach($fields as $field){  
         $rebuldfield=[];
           $splitfields=explode(' ',$field);    
             $foundAs = false;    
              // $splitfields=$rebuldfield;
             if(is_array($splitfields) &&  count($splitfields) >1 ){  
                 foreach($splitfields as $in_field ){
                     if( 'as' === strtolower($in_field)){ $foundAs = true  ;  } ;
                 }
             }
             
             foreach($splitfields as $_fields){
                 if($_fields !== ''){
                     $rebuldfield=array_merge($rebuldfield , [$_fields]);
                 }
             }
               if($foundAs){ 
                   $letdisplay = array_merge( $letdisplay ,[ $rebuldfield[0] ]  );
               }else{
                   $letdisplay = array_merge( $letdisplay , [ $field ]);
               }
           
    }
    
    return  $letdisplay ;
}

function asmap_keys( array $fields ){
    $letdisplay=[];
    // space filter 
    foreach($fields as $field){  
        $rebuldfield=[];
           $splitfields=explode(' ',$field);    
               $foundAs = false;    
               if(is_array($splitfields)&&count($splitfields) >1 ){ 
                   foreach($splitfields as $in_field ){
                       if( 'as' === strtolower($in_field)) $foundAs = true ;
                   }
               }

               if($foundAs){ 
                 foreach($splitfields as $_fields){
                     if($_fields !== ''){
                         $rebuldfield=array_merge($rebuldfield , [$_fields]);
                     }
                   }
                   $letdisplay = array_merge( $letdisplay,[ $rebuldfield[0]]);
               }else{
                   $letdisplay = array_merge( $letdisplay,[ $field]);
               }
           
    }
return  $letdisplay ;
}

