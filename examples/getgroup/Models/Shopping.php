<?php
//@@ Modelfor example
//@@ Author by : sciantman@gmail.com
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nantaburi\Mongodb\MongoNativeDriver\Model as NanModel ;

class Shopping extends NanModel
{
    use HasFactory;
    protected $database = "shopping" ; 
    protected $collection ="products" ;  // default collection
    protected $schema = [ 
                    'produts'=> [
                                'id' => [  
                                        'AutoInc' => true ,
                                        'AutoIncStartwith' => 10,
                                        'Index' => true,
                                        'Unique' => true 
                                        ] ,
                                'name',
                                'description',
                                'description_th','image','price',
                                'type_id'
                            ] ,
                    'products_group' => [
                                'catid' => [ 
                                        'AutoInc' => true ,
                                        'AutoIncStartwith' => 10,
                                        'Index' => true,
                                        'Unique' => true 
                                        ] ,
                                'name',
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
