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
 
    private function resetPair(){
         self::$key_collection = '' ; 
         self::$pair_collection = ''  ; 
         self::$key_join = "" ; 
         self::$pair_join = "" ;
    } 
 
     public static function pair( string $key_collection , string $pair_collection )  { 
         (new static )->resetPair() ;
         self::$key_collection = $key_collection  ; 
         self::$pair_collection = $pair_collection ; 
         return  (new static)->setPairCollection(); 
     }
 
     private function setPairCollection(){
       
         return $this ; 
     }
 
     public   function  pairJoin(string $key_join , string $pair_join) {
 
         self::$key_join = $key_join; 
         self::$pair_join = $pair_join;

        return $this; 
 
     }
 
     public   function  pairOption(array $Arr ) {
 
     }
     
     public function pairGet( ...$getFileds ){  
 
         
         // dd( "Break !! " , $viewskey  ,  $viewpair , $key_join , $pair_join ) ; 

         
         $cursor=array( [  '$project'=> [ '_id'=> 0, self::$key_collection => '$$ROOT']] ,
                        [ '$lookup'=> [ 'localField'=> self::$key_collection.".". self::$key_join, 'from'=> self::$pair_collection , 'foreignField'=> self::$pair_join, 'as'=> self::$pair_collection]],
                        [ '$unwind'=> [ 'path'=> '$'.self::$pair_collection, 'preserveNullAndEmptyArrays'=> true]],
                        [ '$match'=> [ self::$pair_collection.".".self::$pair_join => [ '$ne'=> null]]],
                        [ '$sort'=> [ "group_type.group_id"=> 1]],
                          makeProject($getFileds )
                    );
         
         dd(__file__.__line__,"Pair Break" , self::$key_collection , self::$pair_collection ,"===get====" , $getFileds ,"===Joins===" , self::$key_join  , self::$pair_join   , '==Currer==' , $cursor  , $this  );
         
              $mixKeysValues = [] ;
              foreach(self::$mappingAs as $key => $value ) {
                 $mixKeysValues = array_merge( $mixKeysValues,[$key]);
                 $mixKeysValues = array_merge( $mixKeysValues,[$value]);
              }
              $mixKeysValues  =  array_unique($mixKeysValues );
            // dd($cursor,self::$joincollections , self::$mappingAs , "MIX" , $mixKeysValues , "Break !!") ;
          if( null == self::$joincollections ){ throw new Exception(" Error !  ->getgroup() require function ->leftjoin() before ");  }
        //  $joinout = $this->findJoin( ) ;
     $group_type=$mongodata->raw(function($collection) use ($cursor) {
             return $collection->aggregate($cursor);
         }
     );
     $i=0;
     $keyid=NULL;
     $grouptype_array=array();
         foreach ($group_type as $key=> $value) {
             if ($keyid !==$value['group_type']->group_id) {
                 $grouptype_array[$i]['group_id']=$value['group_type']->group_id;
                 $grouptype_array[$i]['gname_en']=$value['group_type']->type_groupname_en;
                 $grouptype_array[$i]['gname_th']=$value['group_type']->type_groupname_th;
                 $grouptype_array[$i]['types']=array();
                 $subarray=array('type_id'=> $value['products_type']->type_id, 'desc_en'=> $value['products_type']->description, 'desc_th'=> $value['products_type']->description_th);
                 array_push($grouptype_array[$i]['types'], $subarray);
                 $last_i=$i;
                 $i++;
                 $keyid=$value['group_type']->group_id;
             }else{
                 if(isset($last_i)) {
                     $subarray=array('type_id'=> $value['products_type']->type_id, 'desc_en'=> $value['products_type']->description, 'desc_th'=> $value['products_type']->description_th);
                     array_push($grouptype_array[$last_i]['types'], $subarray);
                 }
             }
         }
 
        $jsondata=json_decode(json_encode($grouptype_array));
 
       return $jsondata;
     }

     public function getgroup() {    

        $this->getAllwhere() ;  // Intregate where everywhere  
        if(!null == self::$joincollections){ 
           if(env('DEV_DEBUG'))print  (__file__.":".__line__ ." -> GET Group : <br>\n") ;
           return $this->findJoin() ;
        }else{
  
            throw new Exception("Require Join collections ");   ;        
        }
       
    } 



}