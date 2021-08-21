<?php

namespace App\Http\Controllers;

use App\Models\Shopping ;
use MongoDB\BSON\UTCDateTime ;

class Tester extends Controller
{   
        public function index(){ 
            $ttt = isoDateTime("2020-09-16 04:01:01.000");
            $start = new UTCDateTime($fmdate) ;
            $test = Shopping::collection('items')->select('*')->where('date','=',$start)->get();
        }

}