<?php

namespace app\api\controller\v1;

use app\api\controller\Base;

/**
 * @title       图片接口
 * @description 图片接口
 */
class Upload extends Base
{

   
    /**
     * @name:        评论图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function appraisesImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file =  $this->request->file('image');
  
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 2048000,'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/appraises/');
            if ($info) {
                return show(1, ['imgUrl' => "/uploads/appraises/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }

    /**
     * @name:        闲置图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function idleVideo()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file =  $this->request->file('video');
  
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 20480000,'ext' => 'mp4,wmv,avi,rm,rmvb'])->move(ROOT_PATH . 'public' . DS . 'uploads/idleVideo/');
            if ($info) {
                return show(1, ['idleVideoUrl' => "/uploads/idleVideo/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }

    /**
     * @name:        提现二维码图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function walletImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 2048000,'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/walletImg/');
            if ($info) {
                return show(1, ['imgUrl' => "/uploads/walletImg/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }
    /**
     * @name:        提现二维码图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function idleImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 2048000,'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/idleImg/');
            if ($info) {
                return show(1, ['imgUrl' => "/uploads/idleImg/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }
    /**
     * @name:        退款闲置图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function refundDealImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 2048000,'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/refundDealImg/');
            if ($info) {
                return show(1, ['imgUrl' => "/uploads/refundDealImg/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }
    /**
     * @name:        退款闲置图片上传
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function evidenceImg()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->validate(['size' => 2048000,'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads/evidence/');
            if ($info) {
                return show(1, ['imgUrl' => "/uploads/evidence/" . $info->getSaveName()]);
            } else {
                return show(0, '', $file->getError());
            }
        }
        return show(0);
    }
}
