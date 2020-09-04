<?php
namespace  Nantaburi\Mongodb\MongoNativeDriver ;

class Outlet {

    public  $total = 0 ;
    public  $items  ;
    public  $perPage  ;
    public  $currentPage ; 
    public  $path ='' ;
    public  $query = array();
    public  $pageName = '' ;
    public  $links = array() ;
    public  $options = array() ;
    public  $elements = array(['page=1']) ;

    public function links () {
       view()->addNamespace('nantaburi' , __DIR__.'/../views' );
       view()->addLocation(__DIR__.'/../views');
       return view("nantaburi::paginate")
                        ->with('paginator',$this);
    }

    public function hasPages(){
        ( $total > 0 )?  $has = true : $has = false ;
         return $has ;
    }

}