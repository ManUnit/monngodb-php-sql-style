<?php
/*
* Class functions  
*/

namespace Nandev\Models;

use Nantaburi\Mongodb\MongoNativeDriver\Model as NanModel ;

class AdminModel extends NanModel
{  
   /*
   * @override $collection to all stack extends back to -> Class Model -> Class Connection( Using)
   * 
   */ 
   protected  $collection = "system.users" ;  
   protected  $database = "admin" ;  
  

 
}
