<?php

namespace App;

use Nantaburi\Mongodb\MongoNativeDriver\Model as NanModel ;
// use Jenssegers\Mongodb\Eloquent\Model;
// use Illuminate\Database\Eloquent\Model;

class Products extends NanModel
{
    protected $connection = "mongodb";

    protected $database = "testdb" ;    // default database
 
    protected $collection = 'products';   // default collection 

    protected $schema = [
        'products' => [
            "id" => [
                'AutoInc' => true,
                'AutoIncStartwith' => 10,
                'Index' => true,
                'Unique' => true
            ],
            "name",
            "description",
            "standard",
            "material",
            "coating",
            "code",
            "update",
            "image",
            "price",
            "ratting",
            "categorie_id",
            "created_at",
            "updated_at",
        ],
        'reviews' =>[
            "id" => ['AutoInc' => true, 'Index' => true, 'Unique' => true ] ,
            "product_id",
            "user_id",
            "text"

        ]
    ];



}