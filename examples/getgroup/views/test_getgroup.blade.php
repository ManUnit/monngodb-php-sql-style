<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <body class="antialiased">
        <h2>==test list by group==</h2>

        @foreach( $test as  $key => $value )
          
           {{$key}} : {{  $value['gdesc']   }}  <br> 

           @foreach( $value['type_desc_en'] as  $list ) 

              =====>{{ $list }} <br>

            @endforeach
             
        @endforeach  
    </body>
    </html>
