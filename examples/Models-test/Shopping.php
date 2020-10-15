<?php

namespace App\Models;

use Nantaburi\Mongodb\MongoNativeDriver\Model as NanModel ;

class Shopping extends NanModel
{
    use HasFactory;
    protected $database = "shopping" ; 
    protected $collection ="products" ;  // default collection
    protected $timezone = "Asia/Bangkok" ;  // default UTC offset + 0:00  list support timezone https://www.php.net/manual/en/timezones.php
    //Schema Datatype  Double , Decimal128 , Integer32 , String  , Date ,  Auto  
    // DataType Date support "now" 
    protected $schema = [ 
                    'products'=> [
                                'id' => [  
                                        'AutoInc' => true ,
                                        'AutoIncStartwith' => 10,
                                        'Index' => true,
                                        'Unique' => true ,
                                        'DataType' => 'Date'
                                        ] ,
                                'name' => [
                                        'DataType' => 'String'
                                ],
                                'description' => ['DataType' => 'String'],
                                'description_th' => ['DataType' => 'String'],
                                'image' => ['DataType' => 'String'],
                                'price' => ['DataType' => 'String'],
                                'type_id' => ['DataType' => 'Double']
                            ] ,
                    'items' => [
                             'id' => [
                                'AutoInc' => true ,
                                'AutoIncStartwith' => 1000,
                                'Index' => true,
                                'Unique' => true ,
                                'DataType' => 'Double'
                             ],
                             'date' => [
                                     'DataType' => 'Date'
                             ],
                             'description',
                             'name'
                    ],
                    'products_group' => [
                                'cat_id' => [ 
                                        'AutoInc' => true ,
                                        'AutoIncStartwith' => 10,
                                        'Index' => true,
                                        'Unique' => true 
                                        ] ,
                                'description',
                    ] ,
                    'products_type' => [
                        'type_id' => [ 
                                'AutoInc' => true ,
                                'AutoIncStartwith' => 10,
                                'Index' => true,
                                'Unique' => true 
                                ] ,
                        'description',
                        'descriotion_th',
                        'type_groupid'
            ]              
        ] ;
}
