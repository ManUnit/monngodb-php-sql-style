# mongodb-sql-style 
Installing 
````
composer require nantaburi/mongodb-sql-model 

````
Mongodb using SQL style 
- Configuraton  add setting in config/database.php of laravel 

````
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('MONGO_DB_HOST', '127.0.0.1'),
    'port' => env('MONGO_MONGO_DB_PORT', 27017),
    'database' => env('MONGO_DB_DATABASE', 'marcompany'),
    'username' => env('MONGO_MONGO_DB_USERNAME', 'maradmin'),
    'password' => env('MONGO_DB_PASSWORD', 'password'),
    'options' => [     
        'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'),
    ],
],

````

Run Nantabury/Mongodb standalone without laravel : 

- once you don't have laravel just base on composer package go to create file under directory ./config/database.php and copy all below to database.php
````
<?php

return [
    'default' => 'mongodb',
    'connections' => [
            'mongodb' => [
                    'driver' => 'mongodb',
                    'host' => '127.0.0.1',
                    'port' => 27017,
                    'database' => 'shopping',
                    'username' =>  'admin',
                    'password' =>  'password',
                    'options' => [],
            ]
    ],
];

````
__________

- Using Laravel for SQL query style  example below 
    - Create Model - using command `  php artisan make:model UserDbModel ` at laravel root project 
      and insert ` use Nantaburi\Mongodb\MongoNativeDriver\Model   ` on top 
      - ex-fillable use to be `protected $fillable = ["useid","username","lastname","password"] ` will replace with $schema as example below
      - Example : `protected $schema [ "userscollection" , ["useid","username","lastname","password"] ] ` 
      - Magic create index with two ways
         - first way :  add array with key [ Index => true ] if you want to add option Unique just add more key [ 'Index' => true , 'Unique' =>  true ] 
         - secound way : do multiple keys with   create key `"$__MULTIPLE_INDEX_01" `on same level of collection change at the end `_01` to be other once you have more one multiple keys you can use  `_02` even `_AB` support as well 
      - Magic create collection for counters auto increasement number by add `'AutoInc' => true , ` and also have option ` 'AutoInc' => true , 'AutoIncStartwith' => 10, ` default datatype  as double max number can be 2^1023 
      - Magic  creation of Index and Magic counter will auto create affective once you run first insert  or you can do [NameModel]::InitIndexAutoInc() 
         - Example : run at Laravel controller just first time or do change schema   ` UserModel::InitIndexAutoInc() ` 
   - to protect update option add ` 'UpdateProtected'=>true ` once you inserted and do you want to protect update such as price , ID Card number , you can put option  example ` idcard => [ 'UpdateProtected'=>true  ] ` into option of that field

````
 <?php

namespace App;

use Nantaburi\Mongodb\MongoNativeDriver\Model as NanModel ;

class UserModel extends NanModel
{  
   /*
   * @override $collection to all stack extends back to -> Class Model -> Class Connection( Using)
   * 
   */ 
   protected  $collection = "users" ;  
   protected  $database = "customer" ;  
   
   /*
   * @override
   * $fillable migrated to under  $schema
   *
   */


   protected  $collection = "users" ;   // prepare for default collection you can use mode of Model::collection("change new collection later")
   protected  $database = "companydata" ;  
   /*
   * protected  $fillable = [ "username","email","first_name","last_name","password",
   *                         "plan","services","server-reference","client-address",
   *                        "server-req-time"
   *                      ];  
   */
   
    protected  $schema = [ 'users' => [ "userid" => [ 'AutoInc' => true  ] , "username","email","first_name",
                                        "last_name","password",
                                        "plan","services","server-reference",
                                        "client-address","server-req-time"],
                          'services' => ['sid'=>[ 
                                                    'AutoInc' => true ,
                                                    'AutoIncStartwith' => 10,
                                                    'Index' => true,
                                                    'Unique' => true
                                                  ],
                                            'productid'=>[ 
                                                    'AutoInc' => true ,
                                                    'AutoIncStartwith' => 1000001,
                                                    'Index' => true,
                                                    'Unique' => false
                                                  ],
                                            'service_name',
                                            'price' =>[
                                                  'UpdateProtected'=>true
                                            ],
                                            'description' ,
                                            '$__MULTIPLE_INDEX_01'=>[
                                                                  'name' => 'indexSidPid',
                                                                  'key' => [ 'sid' => 1 , 'productid' => 1  ],
                                                                  'unique' => true 
                                            ], 
                                            '$__MULTIPLE_INDEX_02'=>[
                                                                  'name' => 'indexSnameDesc',
                                                                  'key' => [ 'service_name' => 1 , 'description' => 1  ],
                                                                  'unique' => true 
                                            ]

                                            ]
                          ];  
    
  

 
}


````
- Example get created magic index  and the counters of each collection after run insert
   - Magic create counter collection  run command with mongo shell ` db.services.getIndexes() ` 
 ````
 $mongo
  >db.services.getIndexes() ;
  [
    {
        "v" : 2.0, 
        "key" : {
            "_id" : 1.0
        }, 
        "name" : "_id_", 
        "ns" : "companydata.services"
    }, 
    {
        "v" : 2.0, 
        "key" : {
            "sid" : 1.0
        }, 
        "name" : "$__INDEX_SID_", 
        "ns" : "companydata.services"
    }, 
    {
        "v" : 2.0, 
        "key" : {
            "productid" : 1.0
        }, 
        "name" : "$__INDEX_PRODUCTID_", 
        "ns" : "companydata.services"
    }, 
    {
        "v" : 2.0, 
        "unique" : true, 
        "key" : {
            "sid" : 1.0, 
            "productid" : 1.0
        }, 
        "name" : "indexSidPid", 
        "ns" : "companydata.services"
    }, 
    {
        "v" : 2.0, 
        "unique" : true, 
        "key" : {
            "service_name" : 1.0, 
            "description" : 1.0
        }, 
        "name" : "indexSnameDesc", 
        "ns" : "companydata.services"
    }
]>db.companydata_counters.find() ;

{ 
    "_id" : "userid", 
    "collection" : "users", 
    "sequence_value" : "0"
}
{ 
    "_id" : "sid", 
    "collection" : "services", 
    "sequence_value" : "BLM10"
}
{ 
    "_id" : "productid", 
    "collection" : "services", 
    "sequence_value" : "PIDTH1000001"
}


 ````


  <table>
    <thead>
      <tr>
        <th>Functional</th>
        <th>Head function </th>
        <th>Example </th>
     </tr>
   </thead>
   <tbody>
   <tr><td>set database</td>
        <td>
            <p>
              <code>[DBModel]::database(string $dbname[, string $collectionName])</code><br>
           </p>
        </td>
        <td>
            <p>
              <code>[DBModel]::database('companyDb')-> </code><br>
              <code>[DBModel]::database('companyDb','Products')-> </code><br> 
             </p>
        </td>
    </tr>
   <tr><td>set database</td>
       <td><p>
           <code>[DBModel]::DB(string $dbname[,string $collectionName])</code><br>
           </p>
        </td>
        <td>
            <p>
              <code>[DBModel]::DB('companyDb')-></code><br>
              <code>[DBModel]::DB('companyDb','Products')-></code><br>    
            </p>
        </td> 
   </tr>
   <tr><td>set database</td>
   <td>
        <p>
        <code>[DBModel]::collection(string $collectionName)</code><br>
        </p>
   </td>
   <td>
      <p>
        <code>[DBModel]::collection('Products')-></code><br>
        <code>[DBModel]::database('companyDb')->collection('Products')-></code><br>
       </p>
      </td>
   </tr>
   </tbody>
  </table>

 



- Create Laravel controller 
   - using command `  php artisan make:controller --model=UserDbModel  ` at laravel root project 
   - then edit and insert basic SQL  example :
      ` select * from user where  username like 'suphacha%' and age > 18 or mooban = 'Pangpoi' ; `
   - using SQL transform to mongodb showing  below : 
 ````
 use App\UserDbModel ; 
 
    $users= UserDbModel::query()
                          ->where("username" , "like" , "suphacha%" )
                          ->andwhere("age" ,">", 18)
                          ->orwhere("mooban" ,"=" ,"Pangpoi" )
                          ->get() ;
                          
    return view('userlist')->with("users",$users) ; 


 ````
Changing database and collection if you have permission of user and password on the same multiple databases you can change database difference in file ./config/database.php   
    - examples change just database  DataBaseModel::database("shopping") ....    , change database and collection DataBaseModel::database("shopping","products") ...
    
````
 
   $prods = CompanyDB::database("shopping")
                                    ->select("id as prod_id","name as prod_name")
                                    ->get()
                                    
   $users = CompanyDB::database("authentication","users")->select("id"," name as prod_name")
                                  ->get()
                                  
   

````
-  switch collection  don't need to re-create new other Model file
   - put begin with  ->collection('[Collection Name]')  see example below

````
<?php

namespace App\Http\Controllers;
use App\CompanyModel;
 
          $users =  CompanyModel::collection("users")
                                ->where( "username" ,"=" , "suppachai")
                                ->get();

          $products = CompanyModel::collection("products")
                                ->where( "pid" ,"=" , "101")
                                ->get();

          // Laravel's blade view to displays
          return view("usermanage" )->with('users',$users)
                                    ->with('products',$products); 
               
    }

 ````

-  switch collection  don't need to re-create new other Model file
   - put begin with  ->collection('[Collection Name]')  see example below
   - and many way to get data

````
<?php

namespace App\Http\Controllers;
use App\CompanyModel;
 
          $users =  CompanyModel::collection("users")
                                ->where( "username" ,"=" , "shppachai")
                                ->get();

          $products = CompanyModel::all() ;
          
          $login =  CompanyModel::collection("products")->where( "id" ,"=" , 101)->first();

          // Laravel's blade view to dispale
          return view("usermanage" )->with('users',$users)
                                    ->with('products',$products); 
               
    }

 ````

 - Controller 
     - join collectios code example below 
     - once you use groupby() request select() all of fields same fields in groupby() if seleted fields are not be field's member
       in groupby() output will display with empty data on that selected field 
     - if you want to use shortcut of `->groupby( ) `  to be same as all fields in ` ->select("field1 as fl1","field2") ` you can do shortcut 
       by add one operator`'$selected'` into `->groupby('$selected')`  also auto remove ` as ` operator 
```` 
      $users =  CompanyModel::collection("users")
                              ->select( "users.username as uname","services.sid as service_id" )
                              ->leftjoin("services","users.services_id","services.sid")
                              ->where( "users.username" ,"=" , "supachai")
                              ->get() ; 

     
      $users =  CompanyModel::collection('products')
                                        ->select('products.id as prod_id ','products.name','products_type.description_th','products_group.description')
                                        ->leftjoin('products_type','products.type_id','products_type.type_id')
                                        ->leftjoin('products_group','products.type_groupid','products_group.type_groupid')
                                        ->where("products.name",'like',"%phone%")
                                        ->orwhere("products.id",'>',400)
                                        ->andwhere("products.description",'like','%the%')
                                        ->groupby('products.id','products.name','products_type.description_th','products_group.description')
                                        ->orderby('products.name','asc')
                                        ->limit(10,2)
                                        ->get(); 
                                        
       // Example do reduce shortcut operator ->groupby()                                  
       $users =  CompanyModel::collection('products')
                                        ->select('products.id as prod_id ','products.name','products_type.description_th','products_group.description')
                                        ->leftjoin('products_type','products.type_id','products_type.type_id')
                                        ->leftjoin('products_group','products.type_groupid','products_group.type_groupid')
                                        ->where("products.name",'like',"%phone%")
                                        ->orwhere("products.id",'>',400)
                                        ->andwhere("products.description",'like','%the%')
                                        ->groupby('$selected')   //@@ do shortcut same as ->select()
                                        ->orderby('products.name','asc')
                                        ->limit(10,2)
                                        ->get(); 

````
 - Paginate 
     - pagination are displays data and page breakedown with data perge  
     - paginate going to don't care function limit() if you have added into order command  
     - Output of data will is in property ->items it also show below
     - Output of pages number linkdata  as ->link  and drawing of html code by use method  ->link() 
     - switch to  page 2 using as example :  http://127.0.0.1/?page=2   
     
````

        $users =  CompanyModel::collection("users")
                                ->select( "users.username as u_name" , "users.password as pwd" , "address.city_name as live " )
                                ->leftjoin("services","users.city_id","address.city_id")
                                ->where( "users.username" ,"!=" , "supachai")
                                ->groupby("users.username" , "users.password" ,"address.city_id" )
                                ->orderby("users.username")
                                ->limit(4,4)   // @@ if do pagination will don't care the limit function 
                                               // don't you need to add limit() in process line the module will ignore 
                                ->paginate(10); 
         
        // Laravel view example //
         return view("usermanage" )->with('users',$users)
        
         
         // example get values in blade file  resource/views/usermanage.blade.php
          <?php 
          // ceate example file usermanage.blade.php 
           <div> total users : {{$users->total}}    </div>
           <div>
               @foreach($users->items as $key => $values)
                    <a href="?page={{$values['page']}}" > {{ $values['icon']}} </a>           
               @endforeach
           </div>
           
           ?>
        

````

 - insert via Controller 
     - insert prepare code example below 
     - $fillable had removed replace with $schema  and fillable will run behind $schema
     - once collectaion and  field  data isn't in schema member insert will reject and has error  
     - find below example to use function  `getModifySequence() ` for auto increase number was bild-in this function you have to  create schema to prepare on Model file then set  ` 'AutoInc' => true , 'AutoIncStartwith' => 101,` with 


````

        $prepairinsertServices["username"] =  $request->input('username') ;
        $prepairinsertServices["email"] =  $request->input('email') ;
        $prepairinsertServices["first_name"] =  $request->input('first_name') ;
        $prepairinsertServices["last_name"] =  $request->input('last_name') ;
        $prepairinsertServices["password"] =  $request->input('psswd') ;
        $prepairinsertServices["plan"] =  $request->input('radioplan') ;
        $prepairinsertServices["services"] = [   ] ;
         // Get data from Check box 
         if ( null != $request->input('service-ecom') ) 
           array_push ( $prepairinsertServices["services"] ,[ "service-ecom" ,  $request->input('service-ecom') ])  ; 
         if (  null != $request->input('service-chat') )
            array_push ( $prepairinsertServices['services'], ["service-chat", $request->input('service-chat')]);
         if (  null != $request->input('service-email') )
            array_push ( $prepairinsertServices['services'],["service-email" , $request->input('service-emai)') ]);
  
       $prepairinsertServices["server-reference"] = $_SERVER['HTTP_REFERER'] ;
       $prepairinsertServices["client-address"] = $_SERVER['REMOTE_ADDR'] ;
       $prepairinsertServices["server-req-time"] = $_SERVER['REQUEST_TIME'] ; 

       $resultInsert =  UserModel::insert( $prepairinsertServices ) ;   // using default $collection in model
       $resultInsertOtherone = UserModel::database()->collection("services")
                                              ->insert(['sid'=> UserModel::database()->collection("services")->getModifySequence('sid') ,
                                                        'service_name'=>'Gold' ,
                                                      'description'=>'VIP sevice top level'
                                                      ]) ; 
        
      // Handle insert error !
      if ( $resultInsert[0] == 0 ) {
            return redirect()->back() ->with('alert', $resultInsert[1] );
      }else { sleep(1) ;  }

      $users =  UserModel::all()  ; 
      
        return view('usermanage',compact('users')  ) ; 
    } 

````

- Update
   - to prevent update field put in schema ` idcard => [ 'UpdateProtected'=>true  ] `
   - update can be many style do you need to see examples below
 ````
$updateResult  =  ShoppingModel::collection("products")
                                                  ->where('id',"=",454)   // update mltiple style 
                                                  ->andupdate( [ "name" => "PHONE UPDATE 11.0 v3" , 'price' => 25200 ])    // Array style
                                                  ->andupdate("description","=","Iphone version Thai v3")                  // Equaly style
                                                  ->update("description_th","New iPhone 11.0 v3th ") ;                     // Two values style

$updateResult  =  ShoppingModel::collection("products")
                                                  ->where('id',"=",454) 
                                                  ->update( [ "description_th" => "New iPhone 11.0 v3th " ]) ;  // Array style
                                                  


$updateResult  =  ShoppingModel::collection("products")
                                                  ->where('id',"=",454) 
                                                  ->update( "description_th" , "New iPhone 11.0 v3th " ) ;   // Two values style
                                                  
 ````
 
- Delete  documents
    - request begin with where statement 
 
 ````
       $deleteresult =  ShoppingModel::collection("products")
                                 ->where("id" ,">",444)
                                 ->andwhere("id" ,"=",442)
                                 ->delete("id",">",440);
                                 
      $deleteresult =  ShoppingModel::collection("products")
                                               ->delete("id","=",440);
 ````
- Handle insert error in view 
  -  add script below into your view file.blade.php

````
   <script>
        var msg = '{{Session::get('alert')}}';
        var exist = '{{Session::has('alert')}}';
        if(exist){
        alert(msg);
        }
   </script>
     
````


    
 
