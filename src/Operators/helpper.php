<?php
namespace Nantaburi\Mongodb\MongoNativeDriver ;
use Illuminate\Support\Env;

// Global function 
function  env2(){

    print ("=====test ====<br>");
}


if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return Env::get($key, $default);
    }
}




?>