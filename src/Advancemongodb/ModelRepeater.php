<?php namespace Nandev\Advancemongodb;

/*
*
*  Nandev :
*  Create by : Anan Paenthongkham
*  Update : 2020-6-7
*/

/* ******************* 
* project Nandave
* edit file composor.json 
* Create by : Anan Paenthongkam
*
* config: composer.json 
* 
* add  : "psr-4": { 
*     
*              "Nandev\\" : "nandev/"
*
*        }
* and run command below in  connamd line :
* $ composer dumpautoload
************************* */



class ModelRepeater extends SuperModelClass {


    // Start all of $this properties  
    // public  return dd with sign +
    // protected  return with sign #


    
    // Start all of $this methods  
    public function getSearch($searchtxt) {   // using this method have to get output  method getperpage () 
        $this->searchText=$searchtxt;    

        return $this;   //  send all this to once call  getSearch($text)->getperpage( int $number)  
    }

    public function getperpage($perpage) {   
         /*
         * its like swap calling stack: getSearch($text)->getperpage( int $number) end to be first by function from caller 
         *  to be getperpage( int $number)->getSearch($text)
         */
        $this->paginate_size=(int) $perpage;  

        if ( !isset($_REQUEST['page'])) {
            $this->request_pagenumber=1;
        }

        else {
            $this->request_pagenumber=(int) $_REQUEST['page'];
        }

        ;

      $this->items =  parent::getProductSearch($this->searchText, $this->paginate_size, $this->request_pagenumber);
      return $this ;
    }

    public function links() {
        //dd(self::$resultpaginate) ; 
        $paginates =  self::$resultpaginate ;
        print "<div class=\"product__pagination\">";
        foreach ( $paginates as  $paginate ){
            // print ("$paginate->icon sign \" <br>"  ) ;
            print "<a href=\"?page=$paginate->page \"> $paginate->icon </a>";
        }
            
        print "</div>";
        // foreach ( $paginates as  $paginate ){
        //      print ("$paginate->icon sign \" <br>"  ) ;
        // }
      //   dd(" Paginate BAKE !") ;

    }

}