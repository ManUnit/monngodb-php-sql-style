<?php
//namespace Nantaburi\Mongodb\MongoNativeDriver ;
use Illuminate\Support\Env;

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {   
        return Env::get($key, $default);
    }
}

if (! function_exists('dotter')) {
    function dotter(){
        return '᎐' ;
    }
}


if (! function_exists('makeProject')) {
       function  makeProject(array $Arr){
            $projects = [ '$project' =>   array() ] ;
            foreach($Arr as $selection  ){
                $projects['$project'] = array_merge($projects['$project'],[$selection => '$'.$selection  ] );
            }
            return $projects ;     
      }
}     

if (! function_exists('asmap')) {
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
}

if (! function_exists('removeAs')) {
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
}


if (! function_exists('asmap_keys')) {
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
}




?>
