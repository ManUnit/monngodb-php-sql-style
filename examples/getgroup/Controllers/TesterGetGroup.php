<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shopping ;

class TesterGetGroup extends Controller
{
    //
    public function index() {
 
            $test = Shopping::collection('products_group')
             ->select('products_group.cat_id as pgid','products_group.description as gdesc','productstype.type_id as tid','products_type.description as type_desc_en')
             ->where('products_type.type_id','!=',null)
             ->leftjoin('products_type','products_group.cat_id','products_type.type_groupid')
             ->orderby('products_group.description','ASC')
             ->groupby('$selected')
             ->getgroup() ; 
          
         return view('test_getgroup')->with('test',$test) ; 

    }
}