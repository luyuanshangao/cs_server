<?php

namespace app\api\controller\v1;

use think\Controller;
use app\common\library\redis\GoodsPool;
use app\common\library\redis\GoodsSku;
use app\common\library\redis\GoodsInfo;
use app\common\library\redis\OrderDelayed;
use app\common\library\redis\CacheKeyMap;
use app\common\library\redis\GoodsCategory;
use app\common\library\push\ServerPush;
use app\api\controller\v1\Vop;
use app\common\model\ExtensionInvitation;
use app\common\model\Order;
use app\common\model\Area;
use app\common\model\Assets;
use app\common\model\Category;
use app\common\model\SdkCategroy;
use app\common\model\SdkPicture;
use app\common\model\UserAddress;
use app\common\model\ExtensionUser;
use app\common\model\Order as OrderModel;
use app\common\library\task\Task;

class Test extends Controller
{
    public function categoryGoods()
    {
   
        $caIds = Category::where(['parentId' => 0])->column('catId');
        $caIds = Category::where(['parentId' => ['in',$caIds]])->column('catId');
        $data = Category::where(['parentId' => ['in',$caIds]])->column('name');
   
        foreach ($data as $name) {
            $condition = [
            "keyword" => $name,
            "pageIndex" => 1,
            "pageSize" => 10000,
            //"sortType" => $sortType //sale_desc price_asc price_desc
            ];
            $result = Vop::GoodsSearch($condition);
     
            var_export($result);
            die();
        }
    }
    public function index()
    {
        \app\queue\common\NoticeLib::getOrderMessage();
        die();
      //ServerPush::send('6cfcdd35ac4da9b42c705d270804ef03', '通知', '测试赛所所所所', ['page' => 1]);
      // $OrderDelayed = new \app\common\library\redis\OrderDelayed();

      //Vop::getCategorys(2);
      //         $OrderDelayed::run();

      //$aa = Vop::aftersaleOrderInfo('SAS070149283315','SPS062930552552');
      //   $aa = Vop::serviceDetailInfo('SAS070149283315',"[2]");
      // var_export($aa);
      //$result = Vop::selectylOrderQuery('SPS070368361432');var_export($result);die();
      //获取商品池
      //var_export(GoodsPool::allGoodsPoolKeys());
      //根据键名获取商品池
      //  var_export(GoodsPool::getGoodsPoolByName('cs:pool:4:hash',['pool_id']));

      // $result = Vop::getVopGoodsSku(1);
      // $results = GoodsSku::setGoodsSku(1,$result);
      // var_export($results);
      //         //随机商品池
      //         // foreach ($result as $key => $value) {
      //         //     $result =  GoodsSku::setGoodsSku(1,$value);
      //         //     //echo '结果:'.$result." \n";
      //         // }
      //         var_export(GoodsSku::allGoodsSkuKeys());
      //         var_export(GoodsSku::getGoodsPoolByName('cs:goods_sku:1:string'));

      // $ounts = 0;
      // for ($i=0; $i < 26; $i++) {
      //     $ounts += count(GoodsSku::getGoodsSkuByPoolId($i));
      // }
      // var_export($ounts);

      // $poolKeysArr = GoodsPool::allGoodsPoolKeys();
      // foreach ($poolKeysArr as $key => $poolKeys) {
      //     $poolIdArrT[]  = GoodsPool::getGoodsPoolByName($poolKeys,['pool_id']);

      // }
      // $poolIdArr = array_column($poolIdArrT,'pool_id');

      // $poolGoodsSkuArr = GoodsSku::allGoodsSkuKeys();
      //var_export($poolGoodsSkuArr);die();
      //foreach ($poolGoodsSkuArr as $key => $value) {
      //$skuNumArr =  GoodsSku::getGoodsPoolByName('cs:goods_sku:16:string');
      //创建协程去获取
      //}
      //  foreach ($skuNumArr as $key => $value) {
      // for ($i=0; $i < 1; $i++) {
      //         $value = $skuNumArr[$i];
      //         $info = Vop::getGoodsInfo($value['sku_num']);

      //         //商品名 价格 编号
      //         $info['price'] = Vop::getPrice($value['sku_num']);

      //         $info['pic'] = Vop::getGoodsPic($value['sku_num'])[$value['sku_num']];
      //      // var_export($info);
      //         $rrsult = GoodsInfo::setGoodsInfo($value['sku_num'],$info);
      //        echo $rrsult;
      //        //var_export(GoodsInfo::allGoodsInfoKeys());
      //      var_export(GoodsInfo::getGoodsInfoBySkuNum($value['sku_num'],['price']));
      // }
      // }
      //    $msgData = Vop::getMessage(2, 'GoodsMessage');
      //    var_export($msgData);
      // var_export(GoodsInfo::allGoodsInfoKeys());
      //cs:goodsInfo:sc0000003845:hash
      //var_export(json_decode(GoodsInfo::getAllGoodsInfoByName('cs:goodsInfo:sc0000012617:hash')['pic'],true));
      //var_export(GoodsInfo::getAllGoodsInfoByName('cs:goodsInfo:sc0000082928:hash'));
      //GoodsInfo::hasKey('cs:goodsInfo:sc0000009401:hash')
      //var_export(GoodsInfo::getGoodsInfoBySkuNum('sc0000009401',['spu_name','price']));
      // $aa = GoodsInfo::allGoodsInfoKeys();
      // // $intersection = array_diff($fruit1, $fruit2, $fruit3);

      // $a = 0;
      // foreach ($aa as $key => $value) {
      //     $fields = GoodsInfo::getAllGoodsInfoByName($value);
      //     if(count($fields) == 3){
      //         if($fields['spu_name'] && $fields['pics'] && $fields['price'] ){
      //             $a+=1;
      //         }
      //     }

      // }
      // echo $a.PHP_EOL;
      //echo date("Y-m-d H:i:s",time());
      // echo password_hash("gz41443219", PASSWORD_DEFAULT);
      //ps -ef | grep live_master | grep -v "grep" | awk '{print $2}'
      //var_export(OrderDelayed::addOrderDelayedTask(['orderSn'=>202006051011009810],(1591326574+1800),'del'));
      //var_export(OrderDelayed::addOrderDelayedTask(['userId'=>10040,'orderSn'=>202006051011009810],(1591326574+1500),'send'));
      //var_export(OrderDelayed::addOrderDelayedTask('bb',time(),['haha']));
      //var_export(OrderDelayed::getOrderDelayedTask());
      // echo   OrderDelayed::delOrderDelayedTask(
      //     json_encode([
      //         'action'=>'del',
      //         'time'=>(1591266500+1800),
      //         'data'=>['orderSn'=>123456]
      //         ])
      //     );

      // $poolGoodsSkuArr = GoodsSku::allGoodsSkuKeys();
      //     $skuNumArr = [];
      //     foreach ($poolGoodsSkuArr as $key => $keyName) {

      //         $resut =  GoodsSku::getGoodsPoolByName($keyName);

      //         if(!$resut){
      //             continue;
      //         }
      //         $skuNumArr = array_merge($skuNumArr,$resut);

      //     }
      //     $skuNumArrCo = array_column($skuNumArr,'sku_num');
      //    echo  count($skuNumArrCo);

      // $queue  = new \app\queue\controller\OrderQueue();

      // $queue->checkPromotionAuth();
      // $userId = 10086;
      // $inviData = ExtensionInvitation::get(['userId' => $userId]);
      // $orderInfo = \app\common\model\Order::where(['orderId'=>298])->find();
      // if ($inviData) {
      //     //订单记录 等级变化
      //     $incPrice = \app\common\model\OrderGoods::computeIncPrice($orderInfo->orderId);
      //     var_export($incPrice);die();
      //     \app\common\model\ExtensionDeal::createData($userId, $inviData['superiorId'], $orderInfo->orderId, $incPrice);
      //     (new \app\common\model\ExtensionUser())->changeGrade($inviData['superiorId']);
      // }
      //$result = (new Order())->payOrder(10121, 202008134998101995);
      //   $aa = Vop::getMessage(16, 'GoodsMessage');
      //   var_export($aa);
      //   foreach ($aa as $value) {

      //     $result = json_decode($value['result_json'], true);
      //     //$price = Vop::getPrice($result['sku_num']);

      //     var_export($result);die();


      // }

      //   var_export($aa);die();
      // $aa = Vop::getGoodsState('sc0000082928');
      // var_export($aa);die();

  //     $data = GoodsCategory::allGoodsCategoryKeys();
  //       foreach ($data as $key => $value) {
  //         GoodsCategory::delKey($value);
  //     }
  //     $result  =   GoodsCategory::allGoodsCategoryKeys();
  // var_export($result);

      //sc0000091651
     // var_export(Vop::getGoodsInfo('sc0000048045'));die();
      // $result  =  Vop::getPrice('sc0000048045');
      //$rate = USDRate();

      // $condition =
      // [
      //     "keyword" => '电脑',
      //     "pageIndex" => 1,
      //     "pageSize" => 10,
      //     "sortType" =>'' //sale_desc price_asc price_desc
      // ];

      // $result = Vop::GoodsSearch($condition);
 
      //$this->exportNoImgThreeCategory();
      //  $aa = bccomp('99', '2',5);
      //  echo $aa;
      //   if($aa > 0){
      //     echo 'askndasklsdl';
      //   }

      // $taskk = new Task();
      // $taskk->upGoodsPool();
   
        $GoodsSku = new \app\common\library\redis\GoodsSku();
        $GoodsInfo = new \app\common\library\redis\GoodsInfo();
        $GoodsCache = new \app\common\library\redis\GoodsCache();
        var_export($GoodsCache::lLenData());
        die();
        while (true) {
            $GoodsCache::rPopData();
        }
        
        var_export($GoodsCache::lLenData());
      //商品池键名
        //$poolGoodsSkuArr = $GoodsSku::allGoodsSkuKeys();
        
      //所有商品缓存键名
        //$allGoodsInfoKeyArr = $GoodsInfo::allGoodsInfoKeys();
       // $allGoodsInfoKeyArr = $GoodsInfo::getAllGoodsKey();
      
      // $GoodsInfo::delKey(CacheKeyMap::goodsKeySet());
      //  $allGoodsInfoKeyArr = $GoodsInfo::getAllGoodsKeyNum();
      //  $aa = new  \app\common\library\task\Task();
      //  $aa->setGoodsCache('sc0000002219');

        \app\queue\common\NoticeLib::clearCache();
        die();
        $info = $GoodsInfo::getAllGoodsInfoBySkuNum('sc0000002219');
        $info = $GoodsInfo::getAllGoodsKey();
        var_export($info);
        die();
        $keyName = CacheKeyMap::goodsInfoHash('sc0000002219');
        $aaa = \app\common\library\redis\GoodsInfo::delKey($keyName);
        $bbb = \app\common\library\redis\GoodsInfo::delGoodsKeyByName($keyName);
        var_export($aaa);
        var_export($bbb);
        die();
      // var_export($allGoodsInfoKeyArr);die();
        $skuNumArr = [];
      // foreach ($poolGoodsSkuArr as $key => $keyName) {
      //   //对应商品池下商品编号数组
      //   $resut =  $GoodsSku::getGoodsPoolByName($keyName);
      //   //var_export(Vop::getPrice($resut[0]));die();
  
      //  // $result = Vop::getGoodsPic($resut[0]['sku_num']);
     
      //   //所有商品编号
      //   $skuNumArr = array_merge($skuNumArr,array_column($resut,'sku_num'));
      // }
      //  $aaaa =$GoodsInfo::getGoodsInfoBySkuNum('sc0000019910',['pics','price','spu_name','wxintroduction']);
      //  var_export($aaaa);die();
      // if(!$aaaa['pics']){
      //   echo 111111111111;
      // }
      // var_export($aaaa);die();
      // $skuNumArrCa = [];
        $a = 0;
        $caca = [];
        $fields = [
        'weight',
        'state',
        'brand_name',
        'ware_qd',
        'product_area',
        'param',
        'lowest_buy',
        'wxintroduction',
        'shouhou',
        ];
        foreach ($allGoodsInfoKeyArr as $keyName) {
          // $info = $GoodsInfo::getAllGoodsInfoByName($keyName);
          // var_export($info);die();
          // try {
          //   if($info['state']){
          
          //     $ppp =  $info['originPrice'] - $info['price'];
          //     $infoaa[] = $ppp;
          //     if(count($infoaa) == 78309){
          //         echo $keyName;var_export($info);die();
          //     }
          //   }
          // } catch (\Throwable $th) {
          //    continue;
          // }
     
    

            foreach ($fields as $key => $fiels) {
                $GoodsInfo::delFieldsByName($keyName, $fiels);
            }
        }
        die();

        arsort($infoaa);
        $caca = [];
        foreach ($infoaa as $key => $value) {
            if (count($caca) == 10) {
                continue;
            } else {
                $keyName = $allGoodsInfoKeyArr[$key];
                $arrEx = explode(':', $keyName);
                $skuNum = $arrEx[2];
                $aa = Vop::getGoodsInfo($skuNum);
                if (!in_array($aa['cat_id_1'], $caca)) {
                    $caca[] = $aa['cat_id_1'];
                    if (count($caca) == 10) {
                          var_export($caca);
                    }
                }
            }
        }
    

 
      //交集
      // $inrerArr = array_intersect($skuNumArr,$skuNumArrCa);
    
      //遍历已存在的缓存 不在新的中 删除掉
      // foreach ($skuNumArrCa as $skuNum) {
      //   if(!in_array($skuNum,$skuNumArr)){
      //       $GoodsInfo::delKeyByName($skuNum);
      //       var_export($skuNum);
      //   }
      // }
    
        var_export(count($skuNumArr));
        var_export(count($skuNumArrCa));
        die();
        echo '用时:' . (microtime(true) - $statr) . " \n";
    }

    public function cateHandle()
    {

        $paths = APP_PATH . '../public/static/copy';

        $data = my_dir($paths);

        $array_img = [];
        $cp_re = [];



        foreach ($data as $key => $value) {
            $new_files = ROOT_PATH . 'public' . DS . 'static' . DS . 'img' . DS . 'category' . DS;
          //取出cid
            $keyName = findNum($key);
          //cid目录
            $new_files = $new_files . $keyName . DS;
          //创建文件夹
            makeDir($new_files);


            foreach ($value as $k => $imgs) {
                //取出cid
                $kName = findNum($k);
                //cid目录
                $new_filess = $new_files . $kName . DS;
                //创建文件夹
                makeDir($new_filess);
                try {
                    foreach ($imgs as $ks => &$img) {
                        $src_img = APP_PATH . '../public/static/copy/' . $key . '/' . $k . '/' . $img;
                        $fileDir = '/static/img/category/' . $keyName . '/' . $kName . '/' . $img;
                        $new_p = APP_PATH . '../public' . $fileDir;
                        $img = [
                        'fileDir' => $fileDir,
                        'img' => $img,
                        ];
                        $resutl = copy($src_img, $new_p);
                        if (!$resutl) {
                            $cp_re = array_merge($cp_re, $src_img);
                        }
                    }
                } catch (\Exception $th) {
                    return $imgs;
                }


                $array_img = array_merge($array_img, $imgs);
            }
        }
        foreach ($array_img as $key => $value) {
            $aaArr =  explode('@', $value['img']);
            $sanCid = findNum($aaArr[0]);
            Category::update(['catImg' => $value['fileDir']], ['catId' => $sanCid]);
        }
        return $cp_re;

      //二
      // $data = Category::where(['catClass'=>2])->select();
      //   foreach ($data as $key => $value) {
      //     $SdkPictureModel = new SdkPicture();
      //     $dataO= $SdkPictureModel->create([
      //       'pic_path'=>$value['catImg'],
      //       'from_id'=>'',
      //     ]);
      //     SdkCategroy::update(['pic_id'=>$dataO->pic_id],['cat_id'=>$value['catId']]);
      //   }
      //   return true;
    }
  //返回没有图片的三级分类
    public function getNoImgThreeCategory()
    {
        $data = Category::where("catImg is null or catImg='' and catClass=2")->select();
      // var_export($data);
      // die();
        return $data;
    }
  //导出没有图片的三级分类
    public function exportNoImgThreeCategory()
    {
      //三级分类
        $datas = $this->getNoImgThreeCategory();

        foreach ($datas as $key => $threeCate) {
            $filesPath = ROOT_PATH . 'public' . DS . 'static' . DS . 'noimg' . DS;
            $firCateName = '';
            $firPath = '';
            $secCateName = '';
            $secPath = '';
            $threeCateName = '';
            $threePath = '';

          //二级分类
            $secCate = Category::get(['catId' => $threeCate['parentId']]);
            if (!$secCate) {
                continue;
            }
          //一级分类
            $firCate = Category::get(['catId' => $secCate['parentId']]);
     
            if (!$firCate) {
                continue;
            }
            try {
                //创建一级目录
                $firCateName = $firCate['catId'] . '(' . $firCate['name'] . ')' . '【一级】';
                $firCateName = str_replace('/', ' ', $firCateName);
          
                $firPath = $filesPath . $firCateName . DS;
                makeDir(trim($firPath));
          
                //创建二级目录
                $secCateName = $secCate['catId'] . '(' . $secCate['name'] . ')' . '【二级】';
                $secCateName = str_replace('/', ' ', $secCateName);
                $secPath = $firPath . $secCateName . DS;
                makeDir(trim($secPath));
                //创建三级目录
            
                $threeCateName = $threeCate['catId'] . '(' . $threeCate['name'] . ')' . '【三级】';
                $threeCateName = str_replace('/', ' ', $threeCateName);
                $threePath = $secPath . $threeCateName . DS;
          
                makeDir(trim($threePath));
            } catch (\Throwable $th) {
                var_export($th);
            }
        }
        return true;
    }


    public function cateHandle2()
    {

        $paths = APP_PATH . '../public/static/copy2';

        $data = my_dir($paths);

        $array_img = [];
        $cp_re = [];



        foreach ($data as $key => $value) {
            $new_files = ROOT_PATH . 'public' . DS . 'static' . DS . 'img' . DS . 'category' . DS;

          //取出cid
            $keyName = findNum($key);

          //cid目录
            $new_files = $new_files . $keyName . DS;
          //创建文件夹
            makeDir($new_files);


            foreach ($value as $k => $imgs) {
                //取出cid
                $kName = findNum($k);
                //cid目录
                $new_filess = $new_files . $kName . DS;

                //创建文件夹
                makeDir($new_filess);

                foreach ($imgs as $names => $imgsName) {
                    try {
                        $src_img = APP_PATH . '../public/static/copy2/' . $key . '/' . $k . '/' . $names . '/' . $imgsName[0];
                        $ksName = findNum($names);
                        $fir  = $ksName . '@2x.png';
                        $fileDir = '/static/img/category/' . $keyName . '/' . $kName . '/' . $fir;

                        $new_p = APP_PATH . '../public' . $fileDir;
                        $img = [
                        'fileDir' => $fileDir,
                        'img' => $fir,
                        ];

                        $resutl = copy($src_img, $new_p);

                        if (!$resutl) {
                            array_push($cp_re, $src_img);
                        }
                        array_push($array_img, $img);
                    } catch (\Exception $th) {
                    }
                }
            }
        }

        foreach ($array_img as $key => $value) {
            $aaArr =  explode('@', $value['img']);
            $sanCid = findNum($aaArr[0]);
            Category::update(['catImg' => $value['fileDir']], ['catId' => $sanCid]);
        }
        return $cp_re;
    }

  /**
   * @name: 批量注册
   * @author: gz
   * @description:
   * @param {type}
   * @return {type}
   */
    public function batchRegList()
    {
        $numRe =  $this->request->get('numRe');
        $incode =  $this->request->get('incode');
        $skuNum =  $this->request->get('skuNum');
        $aa = $this->request->isOptions();
        if ($aa) {
            return show(1);
        }
        return show(1, self::batchReg($numRe, $incode, $skuNum));
    }



      /**
   * @name: 批量注册
   * @author: gz
   * @description:
   * @param {type}
   * @return {type}
   */
//   public static function batchReg($numRe,$incode,$skuNum){
//     // //注册人数

  
//     $registerModel = new \app\api\controller\v1\Register();
//     $AssetsModel = new Assets();
//     $list = [];
//     $resultMsg = '未下单';
//     for ($i=0; $i < $numRe; $i++) {

//           $apiData = $registerModel->getWalletWords()->getdata();
//           $words = $apiData['data']['words'];
//           $dataArr = [
//           'walletWords'=> $words,
//           'incode'=> $incode,
//           'gesturePassword'=>'03678',
//           'payPassWord'=>'000000',
//           ];

//           $result = $registerModel->indexsss_sss($dataArr);
          
//           $dada = $result->getdata();
          
//           if($dada['code'] == 1){
//             //加钱
//             $AssetsModel->addUSDT($dada['data']['userId'],100,'人工充值');
//             ExtensionUser::createData($dada['data']['userId']);
//             if($skuNum){

            
//                   //增加地址
//                     //$RndChinaNameModel= new \app\common\library\RndChinaName();
//                     $addressData = [
//                       'userName'=>time(),
//                       'userPhone'=>randMobile(),
//                       'areaIdPath'=>'8_560_50821_63225',
//                       'addressDetails'=>time().'8_560_50821_63225',
//                       'isDefault'=>1,
//                       'areaName'=>'辽宁 沈阳市 法库县 县城内',
//                     ];

//                     $UserAddressModel = new UserAddress();
//                     $addressId = $UserAddressModel->add($dada['data']['userId'], $addressData);

//                     //确认订单
//                     $conOrdeData = [
//                       'addressId'=>$addressId,
//                       'payType'=>2,
//                       'skuNumArr'=>'[{"skuNum":"'.$skuNum.'","num":1}]',
//                     ];
            
//                     $resultConOr = self::confirmOrder($dada['data']['userId'],$conOrdeData);
                    
//                     if(!$resultConOr){
                     
//                       $resultMsg ='下单失败';
//                     }
//                     //支付订单
//                     $payOrdeData = [
//                       'orderSn'=>$resultConOr,
//                       'payPassWord'=>'000000',
                    
//                     ];
//                     self::payOrder($dada['data']['userId'],$payOrdeData);
//                     $resultMsg =  '下单成功 '. $resultConOr;
              
                
//             }else{
//                 $resultMsg ='未选择下单';
//             }

//             $list[]= [
//                 'walletWords'=> $dada['data']['walletWords'],
//                 'incode'=> $dada['data']['incode'],
//                 'result'=> $resultMsg,
//             ];
        
//           }
                
//     }
  
//    return $list;
    
// }




//      /**
//      * @name:        确认订单
//      * @author:      gz
//      * @description: POST
//      * @param        {type}
//      * @return:
//      */
//     public static function confirmOrder($userId,$dataArr)
//     {

     
//         $OrderModel  = new OrderModel();
     

//         //创建本地订单
//         $result = $OrderModel->addOrder($userId, $dataArr);
       
//         //本地订单创建完毕

//         if (isset($result['code'])) {
//             return 0;
//         }
  
//         return $result->orderSn;
//     }


//     /**
//      * @name:        支付订单
//      * @author:      gz
//      * @description: POST
//      * @param        {type}
//      * @return:
//      */
//     public static function payOrder($userId,$dataArr)
//     {
     

        
//         //订单信息
//         $OrderModel  = new OrderModel();
//         $orderConfition = [
//             'orderSn' => $dataArr['orderSn'],
//             'userId' => $userId
//         ];
       
//         $orderInfo = $OrderModel::get($orderConfition);

//         if (!$orderInfo) {
//             return show(1021);
//         }
//         //账户余额
//         $assetsCondition = [
//             'assetsType' => $orderInfo->payType,
//             'userId' => $userId
//         ];
//         $userAssets = Assets::get($assetsCondition);

//         if (bccomp($userAssets['amount'], $orderInfo->totalMoneyWithPayType, 10) !== 1) {
//             return show(1027);
//         }
        
//         //支付订单
//         $result = $OrderModel->payOrder($userId, $dataArr['orderSn']);
//         return $result;
//     }


      /**
   * @name: 批量注册
   * @author: gz
   * @description:
   * @param {type}
   * @return {type}
   */
    public static function batchReg($numRe, $incode, $skuNum)
    {
      // //注册人数

        $registerModel = new \app\api\controller\v1\Register();
        $list = [];

        for ($i = 0; $i < $numRe; $i++) {
            $apiData = $registerModel->getWalletWords()->getdata();
            $words = $apiData['data']['words'];
            $dataArr = [
            'walletWords' => $words,
            'incode' => $incode,
            'gesturePassword' => '03678',
            'payPassWord' => '000000',
            ];

            $result = $registerModel->indexsss_sss($dataArr);
          
            $dada = $result->getdata();
          
            if ($dada['code'] == 1) {
                $list[] = [
                'walletWords' => $dada['data']['walletWords'],
                'incode' => $dada['data']['incode'],
                'result' => '注册成功',
                ];
            }
        }
  
        return $list;
    }

    public function notice()
    {
        $priceResult  =  Vop::getMessage(28, 'OrderMessage');
        var_export($priceResult);
        die();
    }
}
