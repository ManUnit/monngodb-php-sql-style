# mongodb-sql-style 
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
__________

- Using Laravel for SQL query style  example below 
    - Create Model - using command `  php artisan make:model UserDbModel ` at laravel root project 
      and insert ` use Nantaburi\Mongodb\MongoNativeDriver\Model   ` on top
````

use Nantaburi\Mongodb\MongoNativeDriver\Model 

class UserDbModel  extends Model {

    protected $collection = "users" ;
    protected $database = "marcompany" ;


}


````
- Create Laravel controller 
   - using command `  php artisan make:controller --model=UserDbModel  ` at laravel root project 
   - then edit and insert basic SQL  example :
      ` select * from user where  username like 'suphacha%' and age > 18 or mooban = 'Pangpoi' ; `
   - using SQL transform showing  below : 
 ````
 use App\UserDbModel ; 
 
    $users= UserDbModel::query()
                          ->where("username" , "like" , "suphacha%" )
                          ->andwhere("age" ,">", 18)
                          ->orwhere("mooban" ,"=" ,"Pangpoi" )
                          ->get() ;
                          
    return view('userlist')->with("users",$users) ; 


 ````

 - insert Data to collection 
   - Model  file in app/UserModel.php
   - $schema is array the keys are fillable of collection and value are fillable of each field
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


   protected  $collection = "users" ;   // prepare for default collection you can use mode of Model::DB()->collection("change new collection later")
   protected  $database = "companydata" ;  
   /*
   * protected  $fillable = [ "username","email","first_name","last_name","password",
   *                         "plan","services","server-reference","client-address",
   *                        "server-req-time"
   *                      ];  
   */
   
   protected  $schema = [ 'users' => ["username","email","first_name",
                                        "last_name","password",
                                        "plan","services","server-reference",
                                        "client-address","server-req-time"],
                            'services' => ['sid',
                                           'service_name',
                                          'description']
                          ];  
  

 
}

````
-  switch collection  no need to re create new other Model file
   - put begin with  DB()->collection('[Collction Name]')  see example below
      - ex-fillable use to use `protected $fillable = ["useid","username","lastname","password"] ` will replace with $schema as example below
      - Example : `protected $schema [ "userscollection" , ["useid","username","lastname","password"] ] `

````
<?php

namespace App\Http\Controllers;
use App\CompanyModel;
 
          $users =  CompanyModel::DB() 
                                ->collection("users")
                                ->where( "username" ,"=" , "shppachai")
                                ->get() ;

          $products = CompanyModel::DB() 
                                ->collection("products")
                                ->where( "pid" ,"=" , "101")
                                ->get() ;


          return view("usermanage" )->with('users',$users)
                                    ->with('products',$products); 
               
    }

 ````


 - Controller 
     - insert prepare code example below 
     - $fillable had removed replace with $schema  and fillable will run under $schema
     - once collectaion and  field  data isn't in schema member insert will reject and has error 

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
       $resultInsertOtherone = UserModel::DB()->collection("services")
                                              ->insert(['sid'=>'101',
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


    
 
