<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class IdleMessage extends BaseModel
{
    
    public function getUserNameAttr($value, $data)
    {
        $userName =  User::where(['userId' => $data['userId']])->value('userName');
        return $userName;
    }
    public function getTimeWordAttr($value, $data)
    {

        $timeWord = wordTime($data['createTime']);
        return $timeWord;
    }
    public function getMessageSonListAttr($value, $data)
    {
        
        $list = IdleMessageSon::where([
            'messageId' => $data['messageId']
        ])->order('createTime desc')->field('messageId,userId,toUserId,content,createTime')->select();
        foreach ($list as $key => &$messageSon) {
            $messageSon['userName'] = User::where(['userId' => $messageSon['userId']])->value('userName');
            $messageSon['timeWord'] = wordTime($messageSon['createTime']);
            $messageSon['toUserName'] = User::where(['userId' => $messageSon['toUserId']])->value('userName');
        }
       
        return $list;
    }

    public static function orderTime($data,$sonList='messageSonList',$order='createTime'){
        if(count($data) == 0){
            return [];
        }
        
        foreach ($data as $key => &$value) {
           
            if(count($value[$sonList]) > 0){
                $value['orderTime'] = end($value[$sonList])[$order];
            }else{
                $value['orderTime'] = $value[$order];
            }
            
        }
        
        $desc = array_column($data,'orderTime');
      
        array_multisort($desc,SORT_DESC,$data);
        
        return array_values($data);
    }
}
