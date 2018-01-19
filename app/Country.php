<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Country extends Model
{
    //
    public static function getCountryNameFromID($id){
        $cid = ($id==!null) ? $id : 0;
        //dd($cid);

        if($cid != 0)
        {
            $country = DB::table('countries')
                ->where('id', $cid)
                ->select('name')->get()[0]->name;
        }
        //dd($country);
        return (!empty($country)) ? $country:'';
    }
}
