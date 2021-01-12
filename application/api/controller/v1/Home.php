<?php

namespace app\api\controller\v1;

use app\common\model\Category;
use think\Controller;
use app\common\model\Category as CategoryModel;
use think\Log;

class Home extends Controller
{

    public function index()
    {
       vendor("BitcoinLib");
       $bitcoin = new \BitcoinLib();
       $response = $bitcoin->loadwallet();
var_export($response);die;
  //    $aa = \
  // var_export($aa);
      //     $GoodsSku = new \app\common\library\redis\GoodsSku;
      //  $poolGoodsSkuArr = $GoodsSku::allGoodsSkuKeys();
      //  var_export($poolGoodsSkuArr);
      //   vendor("Eth");
      //   $eth = new \Eth();
      //   // $ethAddress = $eth->genPair();

      // //  $aaa = $eth->getBalanceOfAddress('0x092667851a5ded799d67b05231af91f6024648eb');
      //   $aaa = $eth->eth_gasPrice();
      //   var_export($aaa);
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
}
