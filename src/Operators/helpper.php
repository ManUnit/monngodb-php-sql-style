<?php

if (! function_exists('commandTranslate')) {
    function commandTranslate(string $command){
           $command =  str_replace(")","" ,$command);
           $command =  explode("(", $command);
           return [ $command[0] => $command[1] ] ;
    }
}
if (! function_exists('env')) {
function env($key, $default = null)
    {   
       $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
       $venderDir = dirname(dirname($reflection->getFileName())); 
       $env = "$venderDir"."/../.env" ; 
       $envfile = fopen("$env", "r") or die("Unable to open file!");
       //$lines = fread($envfile,filesize($env));
       $found = false ;
       $result = "";
       while(!feof($envfile)) {
          $val = explode( "=" ,fgets($envfile) ); 
           if($key === $val[0]){ 
               $result = $val[1] ;
               $found = true ;
               break ;
           }
          
        }
        if($found){
            $result=trim($result);
            $result=str_replace("'",'', $result );
            $result=str_replace("'",'', $result );
            $result=str_replace("\r\n",'', $result );
            $result=str_replace("\n",'', $result );
            $result=str_replace(" ",'', $result );
            $result=trim($result," \r\n");
            fclose($envfile) ;
            return $result ; 
          }else{

            fclose($envfile) ;
            return $default ;
          }

       fclose($envfile) ;

    }
}

if (! function_exists('dotter')) {
    function dotter(){
        return '᎐' ;
    }
}


if (! function_exists('makeProject')) {
       function  makeProject(array $Arr){
            $projects=[ '$project' =>   array() ] ;
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



if (! function_exists('array_findget2deep')) {
    function array_findget2deep (array $arr_data , string $key1 , bool $isKey  , string $key2 ) { 
            $result = array();
            foreach ($arr_data  as $keys => $values ){
                if( isset($arr_data[$keys][$key1 ] )){
                    foreach( $arr_data[$keys][$key1 ]  as $key => $value ){  
                        if($isKey ){
                            if($key2 === strtolower("$value")){ $result = $key ;} 
                        }elseif(!$isKey){
                            if($key2 === strtolower("$key")){ $result = $value ;} 
                        }
                    }
                } ; 
            } 
           if( $result != null){
                return [ true => $result ]  ;
            }else{ 
               return [false => null ] ;
           } 
    }
}

if (!function_exists('array_reindex')){
    function array_reindex (array  $arr ) {
        $result = [] ;
        foreach($arr as $key => $value ){
            $result = array_merge($result , [ $key => $value ]);
        }
        return $result ;
    }
}

if (!function_exists('arrFindKeyRemove')){
    function arrFindKeyRemove(array $arr , string  $findkey){
     $reindex = [] ;
     foreach ($arr as $key => $val ){
         if(isset($arr[$key][$findkey])){ unset($arr[$key]) ; break ;}
     }
     foreach($arr as $key => $val ){
        $reindex = array_merge($reindex,[$key=>$val]); 
     }
     return $reindex ; 
    }
}


}




?>
