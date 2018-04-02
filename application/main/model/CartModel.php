<?php
namespace app\main\model;

use think\Model;
use think\Db;

class CartModel extends Model{
    protected $name = "tab_cart";
    
    public static function self(){
        return new self();
    }

    public function listCart($user_id)
    {
        $it = $this->alias('a')
            ->join('tab_goods g', 'a.goods_id = g.id')
            ->field('a.id, a.goods_id, a.quantity,  g.title, g.price, g.stock, g.game_name, g.operators_name, g.server_name')
            ->where('a.user_id', $user_id)
            ->select();
    }

    public function addCart($user_id, $goods_id,  $data)
    {
        $now = time();
        $map =[
            'user_id'=>$user_id,
            'goods_id'=>$goods_id,
        ];
        $item = $this->where($map)->find();
        if ($item)
        {
            $item['quantity']  += $data['quantity'];
            return $this->update($item);
        }
        $data['user_id'] = $user_id;
        $data['goods_id'] = $goods_id;
        return $this->insertGetId($data);
    }

    public function updateCart($user_id, $cart_id, $data)
    {
        return $this->where('id',  $cart_id)
            ->where('user_id', $user_id)
            ->update($data);
    }

    public function delCart($user_id, $cart_id)
    {
        return $this->where('id', $cart_id)
            ->where('user_id', $user_id)
            ->delete();
    }
}
