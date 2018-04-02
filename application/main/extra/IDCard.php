<?php
namespace app\main\extra;

class IDCard{
    public static function isvalid($id){
        $len = strlen($id);
        if ($len != 13) {
            return false;
        }

        $sum = 0;
        for($i = 0; $i < 12; $i++) {
            $sum += $id[$i]*(13 - $i);
        }
        if( (11 - $sum%11 )%10 != $id[12] ){
            return false;
        }

        return true;
    }
}