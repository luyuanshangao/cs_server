<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class OrderServices extends BaseModel
{

        /**
     * 根据条件来获取列表的数据的总数
     * @param array $param
     */
    public function getOrderServicesCountByCondition($condition = [])
    {

        return $this->where($condition)
            ->count();
        //echo $this->getLastSql();
    }
}
