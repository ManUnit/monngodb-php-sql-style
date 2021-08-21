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
    public  $viewlinkfile = '' ; 

    public function links () {
       view()->addNamespace('nantaburi' , __DIR__.'/../views' );
       view()->addLocation(__DIR__.'/../views');
        if ($this->viewlinkfile === 'null') {
            return view("nantaburi::paginate")->with('paginator',$this);
        }else{
            return view($this->viewlinkfile)->with('paginator',$this);
        }
    }

    public function hasPages(){
        ( $this->total > 0 )?  $has = true : $has = false ;
         return $has ;
    }

}