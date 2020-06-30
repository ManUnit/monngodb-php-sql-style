# mongodb-sqlquery
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
   - using command `  php artisan make:controller --model=Userdatabase ` at laravel root project 
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
 
