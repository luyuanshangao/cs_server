<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\User as UserModel;
use app\admin\model\ExtensionUser;
use app\admin\model\UserLv as UsesrLvModel;

/**
 * @name: 用户管理
 * @author: gz
 * @description: GET POST
 * @param {type}
 * @return:
 */
class User extends Base
{
    protected $noAuthArr = ['getUserLv','getAssetsType'];
    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];
    //权限白名单

    /**
     * @name: 更新
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function checkVersion()
    {
        $appVersion = input('get.appVersion');
        $appversionData  = file_get_contents(VERSION_PATH . "appversion.json");
        $data = json_decode($appversionData, true);
        if ($data['version'] !== $appVersion) {
            $returnData['status'] = 1;
        } else {
            $returnData['status'] = 0;
        }
        return show(1, $returnData);
    }

    /**
     * @name: User列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $data = $this->request->get();
        $UserModel = new UserModel();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['userId','userName','userLv','createTime'], $data);
       
        if (isset($condition['userLv']) && $condition['userLv'] !== '') {
            switch ($condition['userLv']) {
                case 0:
                        $userIds = ExtensionUser::where([])->column('userId');
                        $condition['userId'] = ['not in',$userIds];
                    break;
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                        $userIds = ExtensionUser::where(['extensionId' => $condition['userLv']])->column('userId');
                        $condition['userId'] = ['in',$userIds];
                default:
                    # code...
                    break;
            }
            unset($condition['userLv']);
        }
        // } else {
        //     $userIds = ExtensionUser::where([])->column('userId');
        //     $condition['userId'] = ['not in',$userIds];
        //     unset($condition['userLv']);
        // }
     
        //用户数据
        $total = $UserModel->getCount($condition);
      
        $list = $UserModel->getList($condition, $this->from, $this->size, true, $this->sort, ['userLv','invitationNum','commissionMoney','dealOrderNum','dealOrderMoney']);
        
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    
    
    /**
     * @name: User获取会员等级
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getUserLv()
    {
        
        $Extension = new \app\common\model\Extension();
        $data = $Extension->field('extensionName as name,extensionId as userLv')->select();
        array_unshift($data, ['name' => '未开通','userLv' => 0]);
        return show(1, $data);
    }
    
    /**
     * @name: 获取AssetsType
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getAssetsType()
    {
        
        $AssetsTypeModel = new \app\common\model\AssetsType();
        $data = $AssetsTypeModel::all();
        return show(1, $data);
    }
   /**
     * @name: User充值
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function addMoney()
    {
        $data = $this->request->post();
        try {
            $AssetsModel = new \app\common\model\Assets();
            $actionName = 'add' . $data['assetsType'];
            $AssetsModel->{$actionName}($data['userId'], $data['amount'], '人工充值');
        } catch (\Exception $th) {
            return show(0);
        }
        return show(1);
    }
}
