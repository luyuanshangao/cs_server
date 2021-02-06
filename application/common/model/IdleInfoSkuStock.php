<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;


class IdleInfoSkuStock extends BaseModel
{

    public static function _create($data,$idleInfoId){
        
        $createData = json_decode($data,true);
    
        foreach ($createData as $value) {
                $saveData = [
                    'idleInfoId'=>$idleInfoId,
                    'skuCode'=>self::makeSkuCode(),
                    'price'=>$value['price'],
                    'stock'=>$value['stock'],
                    'freight'=>0,
                    'pic'=>$value['pic'],
                    'spData'=>serialize($value['spData']),
                    'createTime'=>time(),
                ];
          
                $result = self::create($saveData);
                if(!$result){
                   return false;
                }
        }
        return true;
    }

    private static function makeSkuCode()
    {
        $sn = 'AES' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5);
        return $sn;
    }


    public static function skuDecr($idleInfoId){
        $data = self::all([
            'idleInfoId'=>$idleInfoId
        ]);
        $keys = [];
        $values = [];
        $keyValueList = [];
        foreach ($data as $skuStockId => &$sku) {
            $sku['spData'] = unserialize($sku['spData']);
            foreach ($sku['spData'] as  $k=>$v) {
               
                #取keys
                if(!in_array($v['key'],$keys)){
                    array_push($keys,$v['key']);
                   $keyValueList[$k] = [
                        'key'=>$v['key'],
                        'values'=>[],
                   ];   
                }

                #取values 
                if(!in_array($v['value'],$values)){
                    array_push($values,$v['value']);
                    foreach ($keyValueList as  &$j) {
                        if($v['key'] == $j['key']){
                           array_push($j['values'],$v['value']);
                        }
                    }
                    
                        
                }   
                
            }
        }

        return [
            'skuList'=>$data,
            'keyValueList'=>$keyValueList,
        ];
       
       
     
    }

}
