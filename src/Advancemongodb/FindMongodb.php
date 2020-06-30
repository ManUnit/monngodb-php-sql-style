<?php

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
 *
 *
 * and run command below in  connamd line :
 *
 * $ composer dumpautoload
 *
 ************************* */

namespace Nandev\Advancemongodb;

use App\Group_Type_mongo;
use App\Product_mongo;
use MongoDB\BSON\Regex;

// edit file composor.json

class FindMongodb//  defind Class GetGrouptype

{
    public static function getTypeProducts($TypeId, $perpage, $request_page_number)
    {

        // using studio 3T conversion SQL to mongoDB query format on PHP
        // https://studio3t.com/
        $pipeline = array(
            ['$project' => ['_id' => 0, 'products' => '$$ROOT']],
            ['$lookup' => ['localField' => 'products.type_id', 'from' => 'products_type',
                'foreignField' => 'type_id',
                'as' => 'products_type']],
            ['$unwind' => ['path' => '$products_type', 'preserveNullAndEmptyArrays' => true]],
            ['$lookup' => ['localField' => 'products.group_id', 'from' => 'group_type',
                'foreignField' => 'group_id', 'as' => 'group_type']],
            ['$unwind' => [
                'path' => '$group_type',
                'preserveNullAndEmptyArrays' => true,
            ],
            ],
            ['$match' => [ 'products.type_id' =>  $TypeId  ],  ]
        );
        //dd ( gettype($pipeline)  );
        $option_count = array('$group' => ['_id' => [], 'COUNT(*)' => ['$sum' => 1]]);
        $option_select = array('$project' => ['count' => '$COUNT(*)', '_id' => 0]);
        // $options = array ( 'allowDiskUse' => TRUE  ) ;
        $counter_pipeline = $pipeline;
        // Prepare for  document  counter
        array_push($counter_pipeline, $option_count);
        array_push($counter_pipeline, $option_select);

        //========
        $mongodata = new Product_mongo;

        $count_products = $mongodata->raw(function ($collection) use ($counter_pipeline) {
            return $collection->aggregate($counter_pipeline);
        });

        // Paginate Limit calculation //

        $totalDocment = json_decode(json_encode($count_products))[0]->count;
        //^above line was  shortcut of $totalDocment= json_decode( json_encode(  $count_products ) ) ; $totalDocment[0]->count
        // Convert skip to selected page
        $totalpage = (int) ($totalDocment / $perpage);
        if (($totalDocment % $perpage) != 0) {
            $totalpage = $totalpage + 1;
        }

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // limit when over request
        if ($request_page_number < 1) {
            $request_page_number = 1;
        }
        // set positive number  lowest page limiter

        $page_offset = ($request_page_number - 1) * $perpage; // find skip number

        //  dd ( $page_offset ."  = ( ". $request_page_number ." - 1  ) *  ".$perpage  ." , counter : " . $totalDocment  ); // find skip number

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // max page limiter

        array_push($pipeline, ['$skip' => $page_offset]);
        array_push($pipeline, ['$limit' => $perpage]);

        $data_products = $mongodata->raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $data_array = array();
        $i = 0;
        $data_array['totalpage'] = $totalpage;
        $data_array['TypeId'] = $TypeId;
        $data_array['totaldocument'] = $totalDocment;
        $data_array['pageselected'] = $request_page_number;
        $data_array['data'] = array();
        $data_array['paginate'] = array();

        foreach ($data_products as $key => $value) {

            $subarray = array('name' => $value['products']->name,
                'product_desc_en' => $value['products']->description,
                'product_desc_th' => $value['products']->description_th,
                'image' => $value['products']->image,
                'id' => $value['products']->id,
                'price' => $value['products']->price,
                'type_id' => $value['products_type']->type_id,
                'type_desc_en' => $value['products_type']->description,
                'type_desc_th' => $value['products_type']->description_th,
                'group_id' => $value['group_type']->group_id,
                'group_name_en' => $value['group_type']->type_groupname_en,
                'group_name_th' => $value['group_type']->type_groupname_th,
            );
            array_push($data_array['data'], $subarray);
        }
        $pagination = self::paginate($perpage, $totalDocment, $request_page_number); // call other function inside same class
        foreach ($pagination as $data) {
            array_push($data_array['paginate'], $data);
        }
        // dd ( $Pagination  ) ;
        // dd($data_array) ;

        return json_decode(json_encode($data_array));
    }

    public static function GetGroupType()
    {

        $mongodata = new Group_Type_mongo;
        // using studio 3T conversion SQL to mongoDB query format on PHP
        // https://studio3t.com/
        $cursor = array(
            ['$project' => ['_id' => 0, 'group_type' => '$$ROOT']],
            ['$lookup' => ['localField' => 'group_type.group_id', 'from' => 'products_type', 'foreignField' => 'group_id', 'as' => 'products_type']],
            ['$unwind' => ['path' => '$products_type', 'preserveNullAndEmptyArrays' => true]],
            ['$match' => ['products_type.group_id' => ['$ne' => null]]],
            ['$sort' => ['group_type.group_id' => 1]],
            ['$project' => [
                'group_type.group_id' => '$group_type.group_id',
                'group_type.type_groupname_en' => '$group_type.type_groupname_en',
                'group_type.type_groupname_th' => '$group_type.type_groupname_th',
                'products_type.type_id' => '$products_type.type_id',
                'products_type.description' => '$products_type.description',
                'products_type.description_th' => '$products_type.description_th',
                '_id' => 0,
            ],
            ],
        );

        $group_type = $mongodata->raw(function ($collection) use ($cursor) {
            return $collection->aggregate($cursor);
        }
        );
        //dd ( $group_type ) ;
        $i = 0;
        $keyid = null;
        $grouptype_array = array();
        foreach ($group_type as $key => $value) {

            if ($keyid !== $value['group_type']->group_id) {
                $grouptype_array[$i]['group_id'] = $value['group_type']->group_id;
                $grouptype_array[$i]['gname_en'] = $value['group_type']->type_groupname_en;
                $grouptype_array[$i]['gname_th'] = $value['group_type']->type_groupname_th;
                $grouptype_array[$i]['types'] = array();
                $subarray = array('type_id' => $value['products_type']->type_id, 'desc_en' => $value['products_type']->description, 'desc_th' => $value['products_type']->description_th);
                array_push($grouptype_array[$i]['types'], $subarray);
                $last_i = $i;
                $i++;
                $keyid = $value['group_type']->group_id;
            } else {
                if (isset($last_i)) {

                    $subarray = array('type_id' => $value['products_type']->type_id, 'desc_en' => $value['products_type']->description, 'desc_th' => $value['products_type']->description_th);
                    array_push($grouptype_array[$last_i]['types'], $subarray);
                }

            }
        }

        $jsondata = json_decode(json_encode($grouptype_array));

        return $jsondata;

    }

    public static function getProductSearch($searchText, $perpage, $request_page_number)
    {

        // using studio 3T conversion SQL to mongoDB query format on PHP
        // https://studio3t.com/
        $pipeline = array(
            ['$project' => ['_id' => 0, 'products' => '$$ROOT']],
            ['$lookup' => ['localField' => 'products.type_id', 'from' => 'products_type',
                'foreignField' => 'type_id',
                'as' => 'products_type']],
            ['$unwind' => ['path' => '$products_type', 'preserveNullAndEmptyArrays' => true]],
            ['$lookup' => ['localField' => 'products.group_id', 'from' => 'group_type',
                'foreignField' => 'group_id', 'as' => 'group_type']],
            ['$unwind' => [
                'path' => '$group_type',
                'preserveNullAndEmptyArrays' => true,
            ],
            ],
            ['$match' => [
                '$or' => [
                    ['products.name' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['products.description' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['products.description_th' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['products_type.description' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['products_type.description_th' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['group_type.type_groupname_en' => new Regex('^.*' . $searchText . '.*$', 'i')],
                    ['group_type.type_groupname_th' => new Regex('^.*' . $searchText . '.*$', 'i')],
                ],
            ],
            ],
        );
        //dd ( gettype($pipeline)  );
        $option_count = array('$group' => ['_id' => [], 'COUNT(*)' => ['$sum' => 1]]);
        $option_select = array('$project' => ['count' => '$COUNT(*)', '_id' => 0]);
        // $options = array ( 'allowDiskUse' => TRUE  ) ;
        $counter_pipeline = $pipeline;
        // Prepare for  document  counter
        array_push($counter_pipeline, $option_count);
        array_push($counter_pipeline, $option_select);

        //========
        $mongodata = new Product_mongo;

        $count_products = $mongodata->raw(function ($collection) use ($counter_pipeline) {
            return $collection->aggregate($counter_pipeline);
        });

        // Paginate Limit calculation //

        $totalDocment = json_decode(json_encode($count_products))[0]->count;
        //^above line was  shortcut of $totalDocment= json_decode( json_encode(  $count_products ) ) ; $totalDocment[0]->count
        // Convert skip to selected page
        $totalpage = (int) ($totalDocment / $perpage);
        if (($totalDocment % $perpage) != 0) {
            $totalpage = $totalpage + 1;
        }

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // limit when over request
        if ($request_page_number < 1) {
            $request_page_number = 1;
        }
        // set positive number  lowest page limiter

        $page_offset = ($request_page_number - 1) * $perpage; // find skip number

        //  dd ( $page_offset ."  = ( ". $request_page_number ." - 1  ) *  ".$perpage  ." , counter : " . $totalDocment  ); // find skip number

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // max page limiter

        array_push($pipeline, ['$skip' => $page_offset]);
        array_push($pipeline, ['$limit' => $perpage]);

        $data_products = $mongodata->raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $data_array = array();
        $i = 0;
        $data_array['totalpage'] = $totalpage;
        $data_array['totaldocument'] = $totalDocment;
        $data_array['searchtext'] = $searchText;
        $data_array['pageselected'] = $request_page_number;
        $data_array['data'] = array();
        $data_array['paginate'] = array();

        foreach ($data_products as $key => $value) {

            $subarray = array('name' => $value['products']->name,
                'product_desc_en' => $value['products']->description,
                'product_desc_th' => $value['products']->description_th,
                'image' => $value['products']->image,
                'id' => $value['products']->id,
                'price' => $value['products']->price,
                'type_id' => $value['products_type']->type_id,
                'type_desc_en' => $value['products_type']->description,
                'type_desc_th' => $value['products_type']->description_th,
                'group_id' => $value['group_type']->group_id,
                'group_name_en' => $value['group_type']->type_groupname_en,
                'group_name_th' => $value['group_type']->type_groupname_th,
            );
            array_push($data_array['data'], $subarray);
        }
        $pagination = self::paginate($perpage, $totalDocment, $request_page_number); // call other function inside same class
        foreach ($pagination as $data) {
            array_push($data_array['paginate'], $data);
        }
        // dd ( $Pagination  ) ;
        // dd($data_array) ;

        return json_decode(json_encode($data_array));
    }

    public static function getallproducts($perpage, $request_page_number)
    {
        // $searchText = "";
        // using studio 3T conversion SQL to mongoDB query format on PHP
        // https://studio3t.com/
        $pipeline = array(
            ['$project' => ['_id' => 0, 'products' => '$$ROOT']],
            ['$lookup' => ['localField' => 'products.type_id', 'from' => 'products_type',
                'foreignField' => 'type_id',
                'as' => 'products_type']],
            ['$unwind' => ['path' => '$products_type', 'preserveNullAndEmptyArrays' => true]],
            ['$lookup' => ['localField' => 'products.group_id', 'from' => 'group_type',
                'foreignField' => 'group_id', 'as' => 'group_type']],
            ['$unwind' => [
                'path' => '$group_type',
                'preserveNullAndEmptyArrays' => true,
            ],
            ],

        );
        //dd ( gettype($pipeline)  );
        $option_count = array('$group' => ['_id' => [], 'COUNT(*)' => ['$sum' => 1]]);
        $option_select = array('$project' => ['count' => '$COUNT(*)', '_id' => 0]);
        // $options = array ( 'allowDiskUse' => TRUE  ) ;
        $counter_pipeline = $pipeline;
        // Prepare for  document  counter
        array_push($counter_pipeline, $option_count);
        array_push($counter_pipeline, $option_select);

        //========
        $mongodata = new Product_mongo;

        $count_products = $mongodata->raw(function ($collection) use ($counter_pipeline) {
            return $collection->aggregate($counter_pipeline);
        });

        //----- Paginate Limit calculation --//
        $totalDocment = json_decode(json_encode($count_products))[0]->count;
        //^above line was  shortcut of $totalDocment= json_decode( json_encode(  $count_products ) ) ; $totalDocment[0]->count
        // Convert skip to selected page
        $totalpage = (int) ($totalDocment / $perpage);
        if (($totalDocment % $perpage) != 0) {
            $totalpage = $totalpage + 1;
        }

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // limit when over request
        if ($request_page_number < 1) {
            $request_page_number = 1;
        }
        // set positive number  lowest page limiter

        $page_offset = ($request_page_number - 1) * $perpage; // find skip number

        if ($request_page_number > $totalpage) {
            $request_page_number = $totalpage;
        }
        // max page limiter

        array_push($pipeline, ['$skip' => $page_offset]);
        array_push($pipeline, ['$limit' => $perpage]);

        $data_products = $mongodata->raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        $data_array = array();
        $i = 0;
        $data_array['totalpage'] = $totalpage;
        $data_array['totaldocument'] = $totalDocment;
        // $data_array['searchtext'] = $searchText;
        $data_array['pageselected'] = $request_page_number;
        $data_array['data'] = array();
        $data_array['paginate'] = array();

        foreach ($data_products as $key => $value) {

            $subarray = array('id' => $value['products']->id,
                'name' => $value['products']->name,
                'product_desc_en' => $value['products']->description,
                'product_desc_th' => $value['products']->description_th,
                'image' => $value['products']->image,
                'price' => $value['products']->price,
                'type_id' => $value['products_type']->type_id,
                'type_desc_en' => $value['products_type']->description,
                'type_desc_th' => $value['products_type']->description_th,
                'group_id' => $value['group_type']->group_id,
                'group_name_en' => $value['group_type']->type_groupname_en,
                'group_name_th' => $value['group_type']->type_groupname_th,
            );
            array_push($data_array['data'], $subarray);
        }
        $pagination = self::paginate($perpage, $totalDocment, $request_page_number); // call other function inside same class
        foreach ($pagination as $data) {
            array_push($data_array['paginate'], $data);
        }
        // dd ( $Pagination  ) ;
        // dd($data_array) ;

        return json_decode(json_encode($data_array));
    }

    private static function paginate($perpage, $totaldocument, $request_page_number)
    {

        $stly_class = "page-item";
        $stly_class_opt_active = 'active';

        $stly_class_opt_disabled = "disabled";

        $totalpage = (int) ($totaldocument / $perpage);
        if (($totaldocument % $perpage) != 0) {
            $totalpage = $totalpage + 1;
        }

        $data_array = array();
        // $data_array['paginate'] = array();
        $start_range = 7;
        if ($totalpage > 1) {
            if ($request_page_number == 1) {$clickable = 0;
                $pagevalue = null;
                $class_using = $stly_class . " " . $stly_class_opt_disabled;
            } else {
                $clickable = 1;
                $pagevalue = $request_page_number - 1;
                $class_using = $stly_class;
            };
            array_push($data_array, ['page' => $pagevalue, 'selected' => 0, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => '<']); // option push at start
        }

        if ($totalpage >= 2 && $totalpage <= 11) {

            for ($i = 1; $i <= $totalpage; $i++) {
                //  dd ( " I 2 : ".$i ."  Request: " .$request_page_number) ;
                if ((int) $request_page_number === $i) {
                    $selected = 1;
                    $clickable = 0;
                    $class_using = $stly_class . " " . $stly_class_opt_active;
                } else {
                    $selected = 0;
                    $clickable = 1;
                    $class_using = $stly_class;};
                array_push($data_array, ['page' => $i, 'selected' => $selected, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => strval($i)]);
            }
        } elseif ($totalpage > 11) {

            // start ==
            if ($request_page_number < $start_range) {$start_edge = 8;} else { $start_edge = 2;}
            for ($i = 1; $i <= $start_edge; $i++) {
                //    echo "  $request_page_number === $i <br>" ;
                if ((int) $request_page_number === $i) {$selected = 1;
                    $clickable = 0;
                    $class_using = $stly_class . "  " . $stly_class_opt_active;} else { $selected = 0;
                    $clickable = 1;
                    $class_using = $stly_class;};
                array_push($data_array, ['page' => $i, 'selected' => $selected, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => strval($i)]);
            }
            //   dd ( " BAKE : TT =" . $totalpage) ;
            array_push($data_array, ['page' => null, 'selected' => 0, 'clickable' => 0, 'stly_classes' => $stly_class . " " . $stly_class_opt_disabled, 'icon' => "..."]);
            // middle
            if ($request_page_number >= $start_range && $request_page_number <= $totalpage - 6) {
                $middle_range = $request_page_number + 3;
                $middle_start_count = $request_page_number - 3;

            } else {
                $middle_range = 0;
                $middle_start_count = 1; // to disable middle
            };

            for ($i = $middle_start_count; $i <= $middle_range; $i++) {
                // echo "  $request_page_number === $i <br>" ;
                if ((int) $request_page_number === $i) {$selected = 1;
                    $clickable = 0;
                    $class_using = $stly_class . "  " . $stly_class_opt_active;} else { $selected = 0;
                    $clickable = 1;
                    $class_using = $stly_class;};
                array_push($data_array, ['page' => $i, 'selected' => $selected, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => strval($i)]);
            }

            // // ending
            if ((int) $request_page_number <= $totalpage - 6) {
                if ($request_page_number > 6) {
                    array_push($data_array, ['page' => null, 'selected' => 0, 'stly_classes' => $stly_class . " " . $stly_class_opt_disabled, 'clickable' => 0, 'icon' => "..."]);
                }

                for ($i = $totalpage - 1; $i <= $totalpage; $i++) {
                    if ((int) $request_page_number === $i) {$selected = 1;
                        $clickable = 0;
                        $class_using = $stly_class . "  " . $stly_class_opt_active;} else { $selected = 0;
                        $clickable = 1;
                        $class_using = $stly_class;};
                    array_push($data_array, ['page' => $i, 'selected' => $selected, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => strval($i)]);
                }
            } else {
                for ($i = $totalpage - 8; $i <= $totalpage; $i++) {
                    if ((int) $request_page_number === $i) {$selected = 1;
                        $clickable = 0;
                        $class_using = $stly_class . "  " . $stly_class_opt_active;} else { $selected = 0;
                        $clickable = 1;
                        $class_using = $stly_class;};
                    array_push($data_array, ['page' => $i, 'selected' => $selected, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => strval($i)]);
                }
            }

        }
        if ($totalpage > 1) {
            if ($request_page_number == $totalpage) {
                $clickable = 0;
                $request_page_number = null;
                $class_using = $stly_class . " " . $stly_class_opt_disabled;} else { $clickable = 1;
                $request_page_number++;
                $class_using = $stly_class;};
            array_push($data_array, ['page' => $request_page_number, 'selected' => 0, 'clickable' => $clickable, 'stly_classes' => $class_using, 'icon' => '>']); // option push at end
        }

        return $data_array;
    }

    public static function links()
    {
        return "test link";
    }

}
