<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <body class="antialiased">
        <h2>==test list by group==</h2>

        @foreach( $test as  $key => $value )
          
           {{$key}} => group id {{  $value['gid']}} : {{  $value['pdesc']   }}  <br> 

            @foreach($value['products_type'] as  $value )  
                      --------- >>>>  type ID {{ $value['tid']}}  : type descripton {{ $value['typeDesc_en']}}  <br>
            @endforeach 
      
        @endforeach  
    </body>
</html>
