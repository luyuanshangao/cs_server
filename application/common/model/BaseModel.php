<?php

/*
 * @Descripttion:
 * @Author: gz
 */

namespace app\common\model;

use think\Model;

class BaseModel extends Model
{


    /**
     * @name:        根据条件来获取列表的数据的总数
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function getCount($condition = [], $conditionOr = [])
    {

        return $this->where($condition)->whereOr($conditionOr)
            ->count();
        //echo $this->getLastSql();
    }

    /**
     * @name:        根据来获取列表的数据
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function getList($condition = [], $from = 0, $size = 10, $field = true, $order = '', $getAttr = [], $conditionOr = [])
    {

        $result = $this->where($condition)
            ->whereOr($conditionOr)
            ->field($field)
            ->limit($from, $size)
            ->order($order)
            ->select();
        if (!empty($getAttr)) {
            $re = [];
            foreach ($result as $value) {
                $re[] = $value->append($getAttr)->toArray();
            }
            return $re;
        } else {
            foreach ($result as $key => $value) {
                $result[$key] = $value->toArray();
            }
        }

        //echo $this->getLastSql();exit;
        return $result;
    }

    /**
     * @name:        根据有Id修改信息 无Id 新增信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function saveData($data, $pk = 'id')
    {
        if (isset($data[$pk])) {
            if (is_numeric($data[$pk]) && $data[$pk] > 0) {
                $save = $this->allowField(true)->save($data, [$pk => $data[$pk]]);
            } else {
                $save = $this->allowField(true)->save($data);
            }
        } else {
            $save = $this->allowField(true)->save($data);
        }

        return $save;
    }

    /**
     * @name:        通过查询条件获取单条数据对象
     * @author:      gz
     * @description:
     * @param        array     $map    查询条件
     * @param        bool|true $field  字段
     * @param        array     $append 追加已经定义获取器字段
     * @param        bool|true $status
     * @return       $this|array|false|\PDOStatement|string|Model
     */
    public function getInfoByMap($map = [], $field = true, $append = [])
    {

        $object = $this->where($map)->field($field)->find();
        if (!empty($object) && !empty($append)) {
            return $object->append($append);
        } else {
            return $object;
        }
    }

    /**
     * @name:        通过查询条件获取单条数据(数组)
     * @author:      gz
     * @description:
     * @param        array     $map
     * @param        bool|true $field
     * @param        array     $append
     * @param        bool|true $status
     * @return       array
     */
    public function getArrayByMap($map = [], $field = true, $append = [], $status = false)
    {
        if ($status && !isset($map['status'])) {
            $map['status'] = 1;
        }
        $object = $this->where($map)->field($field)->find();
        if (!empty($object) && !empty($append)) {
            $return = $object->append($append);
        } else {
            $return = $object;
        }
        return empty($return) ? [] : $return->toArray();
    }

    /**
     * @name:        通过查询条件获取单条数据的某个字段值
     * @author:      gz
     * @description:
     * @param        array     $map
     * @param        bool|true $field
     * @param        array     $append
     * @param        bool|true $status
     * @return       array
     */
    public function getFieldByMap($map = [], $field = 'id', $getAttr = false, $status = false)
    {
        if ($status && !isset($map['status'])) {
            $map['status'] = 1;
        }
        $object = $this->where($map)->field($field)->find();
        if ($object) {
            if ($getAttr) {
                $return = $object->$field;
            } else {
                $return = $object->getData($field);
            }
        } else {
            $return = [];
        }

        return empty($return) ? [] : $return;
    }

    /**
     * @name:        通过查询条件获取多条数据(数组)
     * @author:      gz
     * @description:
     * @param        array     $map
     * @param        bool|true $field
     * @param        array     $append 这需要在模型里增加获取器
     * @param        bool|true $status
     * @return       array
     */
    public function getListByMap($map = [], $field = true, $order = '', $limit = '', $append = [], $status = false)
    {
        if ($status && !isset($map['status'])) {
            $map['status'] = 1;
        }
        $object_list = $this->where($map)->field($field)->order($order)->limit($limit)->select();
        $list = [];
        if (!empty($object_list)) {
            foreach ($object_list as $item => $value) {
                if (!empty($append)) {
                    $list[] = $value->append($append)->toArray();
                } else {
                    $list[] = $value->toArray();
                }
            }
        }
        return $list;
    }

    /**
     * @name:        判断字段是否存在
     * @author:      gz
     * @description:
     * @param        $column
     * @param        string $table
     * @return       bool
     */
    protected function hasColumn($column, $table = "")
    {
        $table = isset($table) ? $table : $this->table;
        if (empty($table) || $column) {
            $this->error = "hasColumn方法参数缺失";
            return false;
        }
        $sql = "SELECT * FROM information_schema.columns WHERE table_schema=CurrentDatabase AND table_name = '{$table}' AND column_name = '{$column}'";
        return $this->query($sql) ? true : false;
    }
}
