<?php

namespace app\common\model;

class Message extends BaseModel
{

    /**
     * @name: 获取器
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getCreateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['time']);
    }


    /**
     * 添加新消息
     * @param $userId
     * @param $title
     * @param $content
     * @return bool
     */
    public static function add($userId, $type, $title, $content)
    {
        $data = array();
        $data["userId"] = $userId;
        $data["title"] = $title;
        $data["content"] = $content;
        $data["time"] = time();
        $data["type"] = $type;
        $data["read"] = 0;
        self::create($data);
        return true;
    }


    /**
     * 获取某类型未读消息数量
     * @param $type
     * @return int|string
     * @throws \think\Exception
     */
    public function getUnreadMessageCount($type, $userId)
    {
        $messageCount = $this->where("userId", "=", $userId)->where("read", "=", 0)->where("type", "=", $type)->count();
        return $messageCount;
    }
}
