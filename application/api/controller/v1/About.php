<?php

namespace app\api\controller\v1;

use think\Controller;

class About extends Controller
{

    /**
     * @name: 用户服务协议cs
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function serverLicence()
    {
        $licence = file_get_contents(ROOT_PATH . "var/serverLicence");
        return $this->display($licence);
    }

    /**
     * @name: Coin Shop用户注册协议
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function registerLicence()
    {
        $licence = file_get_contents(ROOT_PATH . "var/registerLicence");
        return $this->display($licence);
    }

    /**
     * @name: 检测是否有最新的版本
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkVersion()
    {
        $currentVersion = $this->request->post('clientVersion');
        $wgtVersion = $this->request->post('wgtVersion');
        $clientType = $this->request->post('clientType');
        //获取最新该客户端的最新升级信息
        $latestInfo =  \app\common\model\Appversion::where([
            'clientType' => ['in',[$clientType,3]],
        ])->order('id desc')->find();
        $data = array(
            'isUpdate' => false, //是否需要更新
            'isRequired' => false, //是否必须更新
            'latestVersion' => $currentVersion, //最新的版本号
            'wgtVersion' => $wgtVersion, //最新的wgt版本号
            'updateNote' => '', //更新的说明
            'updateType' => '', //更新的方式
            'downLink' => ''
        );
        if ($latestInfo && $latestInfo->updateType == 2) {//热更新
            //传过来的版本小于设置的
            if ($wgtVersion < $latestInfo->wgtVersion) {
                $data = array(
                    'isUpdate' => true, //是否需要更新
                    'isRequired' => $latestInfo->isRequired == 1 ? true : false, //是否必须更新
                    'latestVersion' => $currentVersion, //最新的版本号
                    'wgtVersion' => $latestInfo->wgtVersion, //最新的wgt号
                    'updateNote' => $latestInfo->updateNote, //更新的说明
                    'updateType' => 2, //更新方式 1商店安装包更新  2wgt热更新
                    'downLink' =>  $latestInfo->downLink
                );
            }
        } elseif ($latestInfo && $latestInfo->updateType == 1) { //安装包更新
            //传过来的版本小于设置的
            if ($currentVersion < $latestInfo->serverVersion) {
                $data = array(
                    'isUpdate' => true, //是否需要更新
                    'isRequired' => $latestInfo->isRequired == 1 ? true : false, //是否必须更新
                    'latestVersion' => $latestInfo->serverVersion, //最新的版本号
                    'wgtVersion' => $wgtVersion, //最新的wgt号
                    'updateNote' => $latestInfo->updateNote, //更新的说明
                    'updateType' => 1, //更新方式 1商店安装包更新  2wgt热更新
                    'downLink' =>  $latestInfo->downLink
                );
            }
        }
 
        return show(1, $data);
    }
}
