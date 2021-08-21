<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shopping ;
use App\Models\OrderItem  ;
use App\Models\Products ;
use Nantaburi\Mongodb\Operators\Nantacos ;
use MongoDB\BSON\UTCDateTime ;

class Tester extends Controller
{
    //
    public function index() {
         // $products = Products::all();
        // ==================================================
        // QUERY TESTER
        // @@  1 test all()
        // $test = Products::collection()->all();

        // @@  2 test get()
        //  $test = Products::collection()->select('*')->get();
        // dd(__file__.__line__,$test);

        // @@  2.1 test order() options('language') get()
        // $test = Products::collection()->select('*')->orderby("name")->options(['language'=>'th'])->get();
        // dd(__file__.__line__,$test);
        
        // @@  3  test where()
         
        // $fmdate = isoDateTime("2020-09-16 04:01:01.000");
        // $start = new UTCDateTime($fmdate) ;
        // $test = Shopping::collection('items')->select('*')->where('date','=',$start)->get();
        // $test = Shopping::collection('items')->select('*')->get();
        
        // @@  4  test like()
        // $test4 = Products::collection()->select('*')->where('image','like','%head%')->get();

        // @@  5 test first()
        //  $test = Products::collection()->select('*')->where('image','like','%หัว%')->first();
        // @@  6  test where andwhere
        //  $test = Products::collection()->select('*')->where('image','like','%หัว%')
        //                                              ->andwhere('code','=','CHT')
        //                                              ->get();
        // @@  7  test where andwhere orwhere
        // $test = Products::collection()->select('*')->where('image','like','%หัว%')
        //                                              ->andwhere('code','=','CHT')
        //                                              ->orwhere('price','<',100)
        //                                              ->get();
        // @@  8  test AS 
        // $test = Products::collection()->select('id as pid','image as im')->where('image','like','%หัว%')
        //                                              ->andwhere('code','=','CHT')
        //                                              ->orwhere('price','<',100)
        //                                              ->get();
        // @@  9 test orderby () ASC and DESC
        // $test = Products::collection()->select('id as pid','image as im')->where('image','like','%หัว%')
        //                                               ->andwhere('code','=','CHT')
        //                                               ->orwhere('price','<',100)
        //                                               ->orderby('id','desc')
        //                                               ->get();
        //    
        // @@ 10 test groupby()
        // $test = Products::collection('reviews')->select('user_id as uid')
        //                                               ->where('ratting','>',3)
        //                                               ->groupby('user_id')
        //                                               ->orderby('user_id')
        //                                               ->get();
        //
        // @@ 11 test follow selecting  groupby('$selected')
        // $categorie_quantity = OrderItem::collection('order_items')
        //     ->select('order_items.id as order_items_id','order_items.product_id as product_id', 'categories.id as categories_id', 'categories.name as categories_name','orders.status_payment','order_items.product_quantity as product_quantity' , 'orders.created_at')
        //     ->leftjoin('orders','order_items.order_id','orders.id')
        //     ->leftjoin('products','order_items.product_id','products.id')
        //     ->leftjoin('categories','products.categorie_id','categories.id')
        //      ->where('orders.status_payment','!=',1)
        //      ->andwhere('orders.created_at','<',"2020-09-09 08:10:23")
        //     //->andwhere('orders.created_at','<=',"2020-03-00 00:00:00")
        //     ->groupby('$selected')
        //     ->orderby('order_items.id','asc')
        //     ->get();

        // dd($categorie_quantity);
        // 11.1 test group () + count(*) ;
        // $test = Shopping::collection('products_type')->select('count(*)')
        // //$test = Shopping::collection('products_type')->select('type_groupid')
        //                                               ->where('type_groupid','>',2)
        //                                               ->groupby('type_groupid')
        //                                               ->limit(10)
        //                                               ->get();
                                                     // ->orderby('user_id')
                                                     //->get() ; 
        // 11.2 test group () + sum(*) ;
        // $test = Shopping::collection('products_type')->select('count(*)')
        //$test = Shopping::collection('products')->select('sum(price)')->where("type_id","=",'3')->get();

        // @@ Raw test rawAggregate(); 
        // sum(qty) * sum(price) ;
        // $raw = [
        //             [ 
        //                 '$match' => [ 
        //                     "qty" => [ 
        //                         '$lte' => 73
        //                     ]
        //                 ]
        //             ], 
        //             [ 
        //                 '$group' => [ 
        //                     '_id' => [   ], 
        //                     'SUMprice' => [ '$sum' => '$price' ],
        //                     'SUMqty' =>   [  '$sum' => '$qty'  ] ,
        //                     'count' => [ '$sum' => 1 ] 
        //                 ]
        //             ], 
        //             [ 
        //                 '$project' => [ 
        //                     'SUMprice' => '$SUMprice', 
        //                     'SUMqty' => '$SUMqty',
        //                     'count_all' => '$count' ,
        //                     'total' => [ '$multiply'  => [ '$SUMqty' ,  '$SUMprice' ]   ],
        //                     '_id' => 0
        //                 ]
        //             ]
        //         ] ;
            
    //         $option = [ "allowDiskUse" => true] ;
            
    //         $raw = [
    //                [ 
    //                 '$match' => [ 
    //                     "qty" => [ 
    //                              '$lte' => 19
    //                             ]
    //                    ]
    //                 ], 
    //                 [ 
    //                     '$group' => [ 
    //                         '_id' => [   ], 
    //                         'sum_each_items' => [  '$sum' => [ '$multiply'  => [ '$qty' ,'$price' ] ] ],
    //                         'count' => [ '$sum' => 1 ] 
    //                     ]
    //                 ], 
    //                 [ 
    //                     '$project' => [ 
    //                         'ราคาสุทธิ' => '$sum_each_items', 
    //                         'รวมรายการ'=> '$count' ,
    //                         '_id' => 0
    //                     ]
    //                 ]
    //             ] ;



    //    $test = Shopping::collection('items')->rawAggregate($raw , $option ) ;
       //  @@ test rawFind() ;

   //    dd(__file__.__line__,$test );


        // $query =   
        //                 [ 
        //                     'qty' => [ '$lte' => 73 ] 
        //                 ] ;
        // $option =  [  'projection' => [
        //                 'price' => '$price',
        //                 'qty' => '$qty',
        //                 '_id' => 0 
        //                ] 
        //             ] ;
       //    $test = Shopping::collection('items')->rawFind($query , $option );
        //     $test = Shopping::collection('items')->select('qty','price')->where('qty',">=",73)->get() ;
        //    $json = json_decode( json_encode( $test ) , true ) ;
        //dd(__file__.__line__, $test);
        //dd(__file__.__line__,$test);
        //
        // @@ 11.1 test selecting  option('language') and groupby('$selected')
        // $test = Shopping::collection('products_type')->select('description_th')
        //                                              // ->where('ratting','>',3)
        //                                               ->groupby('$selected')
        //                                               ->orderby('description_th')
        //                                               ->options(['language'=>'th'])
        //                                               ->get() ; 
        // dd(__file__.__line__,$test);
                                                      //
        // @ 12 test limit()
        // $test = Products::collection('reviews')->select('user_id as uid' , 'id  as pid')
        //                                               ->where('ratting','>',3)
        //                                               ->groupby('$selected')
        //                                               ->orderby('user_id')
        //                                               ->limit(2)
        //                                               ->get() ;
        //
        //  @@ 13 test paginate()
        // $test = Products::collection('products')->select('id as pid','image as im')->where('image','like','%หัว%')
        //                                               ->andwhere('code','=','CHT')
        //                                               ->orwhere('price','<',100)
        //                                               ->orderby('id','desc')
        //                                               ->paginate(5);
        //
        //  @@ 14 test leftjoin()
        // $test = Products::collection('reviews')->select('products.id as pid','products.image as pim','reviews.id as rid','reviews.product_id as rpid')
        //                                               ->leftjoin('products','reviews.product_id','products.id')
        //                                               ->where('reviews.product_id','=',15)
        //                                               ->orderby('reviews.id','desc')
        //                                               ->groupby('$selected')
        //                                              ->get();
        // @@  14.1 test jeftjoin and count(*) 
        // $test = Products::collection('reviews')->select('count(*)')
        //                                 ->leftjoin('products','reviews.product_id','products.id')
        //                                 ->where('reviews.product_id','>',0)
        //                                 ->orderby('reviews.id','desc')
        //                                 ->groupby('reviews.product_id')
        //                                //->limit(2)
        //                                 ->get();
        //  @@ 15 test getgroup()
         //   $test = Shopping::collection('products_group')->select('products_group.cat_id as pgid','products_group.description as pgdesc','product_type.type_id as tid','product_type.description as endesc')
        // $test1 = Shopping::collection('products_group')
        //         ->select('products_group.cat_id as pgid','products_group.description as gdesc','productstype.type_id as tid','products_type.description as typeDesc_en')
        //         ->where('products_type.type_id','!=',null)
        //         ->leftjoin('products_type','products_group.cat_id','products_type.type_groupid')
        //         ->orderby('products_group.description','ASC')
        //         ->groupby('$selected')
        //          ->get() ;
        // 
        // @@ 15.1 test  options('language') and getgroup() 
        // $test = Shopping::collection('products_group')
        //         ->select('products_group.cat_id as gid',
        //                  'products_group.description as pdesc',
        //                         'products_type.type_id as tid',
        //                         'products_type.description as typeDesc_en',
        //                         'products_type.description_th as typeDesc_th'
        //           )
        //         ->where('products_type.type_id','!=',null)
        //         ->leftjoin('products_type','products_group.cat_id','products_type.type_groupid')
        //         ->orderby('products_group.description','products_type.description_th')
        //         ->groupby('$selected')
        //         ->options( ['language' => 'th' ]) 
        //         ->getgroup() ; 
        //     
        //  @@ 16 test random()
        // $test = Products::collection('users')->select('name')
        //                                               ->where('sale','=',1)
        //                                               ->groupby('$selected')
        //                                               ->random(1) ; 
        //  @@ 17 test random() with join
        // $test = Products::collection('reviews')->select('products.id as pid','reviews.product_id as rpid')
        //                                               ->leftjoin('products','reviews.product_id','products.id')
        //                                               ->where('reviews.product_id','>',1)
        //                                               ->orderby('products.id','desc')
        //                                               ->groupby('$selected')
        //                                               ->random(1) ; 
        //======================================
        // INSERT  tester  
        // @test 1  data type 
        // $test = Shopping::collection("praduct")->insert( [ "id" => "test"]) ;   // Spell Wrong collection name test   
        // $test = Shopping::collection("products")->insert( [ "idx" => "test"]) ;   // Spell field "idx" are not in member 
        //  $test = Shopping::collection("products")->insert(["id" => "now" , "description" => "1234"]) ;   // Test datatype 
        //  $test = Shopping::collection("items")->insert(["id" => Shopping::getSequence('id','items') ,
        //                                                 "description" => "ทดสอบสอง 123456" ,
        //                                                 "price" => "1234567"]) ;   // Test datatype 
        // @@test 2 insert with getSequence 
        // $test = Shopping::collection("items")->insert(["itid" => Shopping::getSequence('itid','items') , "description" => "test  now ubuntu " , 'date' => "now"]) ;   // Test datatype 
        // dd(get_class_methods($test2));

        // @@ test 3 insertGetid();
        // $test = Shopping::collection("items")->insertGetId(["id" => Shopping::getSequence('id','items')  , "description" => "test 1234" , "price" => "12321" ]  ) ;

        //====================================
        // UPDATE Tester
        // @test 01 update with 2 argument //
        // $test = Shopping::collection("items")->where("description","=","5687")->update("description","7891");
        // $test2 = Shopping::collection("items")->where("description","=","7891")->update("price","567893");
        // $test1 = Shopping::collection("customers")->where("description","=","7891")->update("price","567890");
        // $fmdate = isoDateTime("2020-09-16 04:01:01.000");
        // $start = new UTCDateTime($fmdate) ;
        // $test = Shopping::collection('items')->select('*')->where('date','=',$start)->get();

         dd(__file__.__line__ ,"Break !!! test get group get ID " ,  $test) ;
         // test.blade.php
         return view('test')->with('test',$test) ;                  
    }
}
