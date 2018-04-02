<?php
namespace app\admin\taglib;

use think\template\TagLib;

class Cms extends TagLib{
      /**
     * 定义全局标签列表
     *
     */
    protected $tags = [
        'member'  => ['attr' => 'name,uid', 'close' => 1],//用户信息
        'menu'    => ['attr' => 'name,class', 'close' => 1],//后台菜单
        'ueditor' => ['attr' => 'name', 'close' => 1],   //百度编辑器
        'upload'  => ['attr' => 'name,image,id,model', 'close' => 1],//上传图片组件
        'massage' => ['attr' => 'name','close' => 1],//消息提示
    ];

    /**
     * 登陆用户信息
     *
     */
    public function tagMember($tag, $content)
    {
        $name   = $tag['name'];
        $uid    = empty($tag['uid']) ? 'null' : $tag['uid'];
        $parse  = '<?php ';
        $parse .= '$__MODEL__ = new \app\admin\model\UserModel(); ';
        $parse .= '$' . $name . ' = $__MODEL__->getUserInfo("' . $uid . '");';
        $parse .= ' ?>';
        $parse .= '{notempty name="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/notempty}';
        return $parse;
    }

    /**
     * 生成后台菜单
     *
     */
    public function tagMenu($tag, $content)
    {
        $name   = $tag['name'];
        $class   = empty($tag['class']) ? 'active' : $tag['class'];
        $parse  = '<?php ';
        $parse .= '$__MODEL__ = new \app\admin\model\Menu(); ';
        $parse .= '$__LIST__ = $__MODEL__->createMenu("' . $class . '");';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * 编辑器调用标签
     *
     * 编辑器调用方法{cms:ueditor name="content" /}内容{/cms:ueditor}
     */
    public function tagUeditor($tag, $content)
    {
        $parse = '<textarea name="' . $tag['name'] . '"id="ueditor">' . $content . '</textarea>';
        $parse.='<script type="text/javascript" src="__STATIC__/static/ueditor/ueditor.config.js"></script>';
        $parse.='<script type="text/javascript" src="__STATIC__/static/ueditor/ueditor.all.min.js"></script>';
        $parse.='<script type="text/javascript">';
        $parse.='//实例化编辑器
            var ue = UE.getEditor("ueditor", {
                initialFrameWidth: \'100%\',
                initialFrameHeight:300,
                scaleEnabled:true,
            });';
        $parse.='</script>';
        return $parse;
    }
    /**
     * header消息提醒
     */
    public function tagMassage($tag,$content)
    {
        $name   = $tag['name'];
        $parse  = '<?php ';
        $parse .= '$__MODEL__ = new \app\admin\model\Menu(); ';
        $parse .= '$' . $name . ' = $__MODEL__->remindMassage();';
        $parse .= ' ?>';
        $parse .= '{notempty name="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/notempty}';
        return $parse;
    }
 }
