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

trait Addon { 
    
    private static $key_collection= '' ; 
    private static $pair_collection = '' ; 
    private static $key_join = "" ; 
    private static $pair_join = "" ;
    private static $aggregate_options = array() ; 
    
     public function options ( array $arrVal ) {  
        // LanguageCountry support   https://docs.mongodb.com/manual/reference/collation-locales-defaults/#collation-languages-locales 
        $optionsAble = array('language'); 
        $language  =  ['collation' =>  [ 'locale' =>  'en', 'strength' => 1 ] ] ; 
        foreach($arrVal as  $key => $val){  
        //     print ("KEY --->");
            if (in_array($key,$optionsAble)) {
                if ( isset($arrVal['language'] ) ){
                    $language['collation']['locale']  =  $arrVal['language'] ;
                    self::$aggregate_options = array_merge ( self::$aggregate_options  , $language ) ; 
                }
            }else{
                   throw new Exception(" Option  $key : $val not support ");     
            }
        }


       return $this ; 
     }
    
     public function getTimeZone(){
        return $this->timezone; 
     }

     public function getDateFormat(){
        return $this->dateformat; 
     }
   
     public function getgroup( ) {    
    
        $this->getAllwhere() ;  // Intregate where everywhere  
        if(!null == self::$joincollections){ 
           //    if(env('DEV_DEBUG'))print  (__file__.":".__line__ ." -> GET Group : $group  --> Sub group :  $subgroup  <br>\n") ; 
           //@@ Make sort select
           ksort(self::$mappingAs); 
           $group_type =  $this->findJoin(['getgroup'=> true]) ;   
           $local_collection =  null; 
           $foreign_collection =   null ;
           $local_collection = array_findget2deep(self::$joincollections,'$project',true,'$$root')[1] ;
           $foreign_collection = array_findget2deep(self::$joincollections,'$lookup',false,'from')[1] ;
           $concatjoin=array();
           // rewrite display concatnate array 
           foreach ($group_type  as $keys => $datas){ 
                $eachdoc = [] ;
                foreach ($datas as $key => $data) {
                    foreach($data as $in_key => $in_data ){ 
                    $eachdoc = array_merge($eachdoc , [ self::$mappingAs[$key.".".$in_key] => $in_data ]);
                    }
                }
                    $concatjoin = array_merge( $concatjoin ,[$eachdoc] );
            }
            $i=0;
            $keyid=NULL;
            $grouptype_array=array();
            $filtered_localCol = array();
            // find filter 
            $filtered_foreignCal = array();
            foreach(  self::$mappingAs as $key =>$value  ){ 
                 $filter =  explode(".",$key) ; 
                 if ( $filter[0] === $local_collection  ) {  array_push ( $filtered_localCol , $value  ) ; }
                 if ( $filter[0] === $foreign_collection  ) {  array_push ( $filtered_foreignCal , $value  ); }
            }
            //@@ make group with all select  // master is line 142
            foreach ($concatjoin as $index => $value) { 
                if ($keyid !==$value[$filtered_localCol[0]] ) {
                    //@@push array of local field 
                    foreach ( $filtered_localCol as $localField  ){  
                        if(isset($value[$localField])){ $grouping_array[$i][$localField]=$value[$localField];}
                    }
                    
                    $grouping_array[$i][$foreign_collection]=array();
                    $prepair_subgroup = array() ; 
                    foreach ( $filtered_foreignCal as  $foreignField ) { 
                       if(isset($value[$foreignField])){ $prepair_subgroup = array_merge($prepair_subgroup, [ $foreignField => $value[$foreignField]] );}
                    }
                    array_push($grouping_array[$i][$foreign_collection], $prepair_subgroup );
                    $last_i=$i; 
                    $i++;
                    $keyid=$value[$filtered_localCol[0]];
                    
                }else{ 
                    if(isset($last_i)) {
                        $prepair_subgroup = array() ; 
                        foreach ( $filtered_foreignCal as  $foreignField ) { 
                        if(isset($value[$foreignField])){ $prepair_subgroup = array_merge($prepair_subgroup, [ $foreignField => $value[$foreignField]] );}
                        }
                        array_push($grouping_array[$last_i][$foreign_collection], $prepair_subgroup );
                    }
                }
            }
            return ($grouping_array)  ; 
        }else{
  
            throw new Exception("Require Join collections ");         
        }
       
    } 



}