<?php

namespace app\common\library\push;

use think\Loader;

Loader::import('getui.GeTui');
class ServerPush
{
  
    public static function send($cid, $title, $content, $data = '')
    {
        $GeTui = new \GeTui();
        $GeTui->pushMessageToSingle($cid, $title, $content, $data);
    }
}
