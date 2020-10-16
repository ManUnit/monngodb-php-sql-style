<?php

namespace App\Http\Controllers;

use App\Models\Shopping ;


class Tester extends Controller
{   
        public function index(){ 
            $test1 = Shopping::collection("items")
                     ->insert(["id" => Shopping::getSequence('id','items') ,
                                "description" => "test  date insert now " ,
                                'date' => "now"
                                ]) ; 
            $test2 = Shopping::collection("items")
                    ->insert(["id" => Shopping::getSequence('id','items') ,
                        "description" => "test  now ubuntu " ,
                        'date' => "2020-10-14 23:05:59.000"
                          ]) ; 
        }

}