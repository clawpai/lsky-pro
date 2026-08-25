<?php

namespace App;

/**
 * 短信网关枚举
 */
enum SmsProvider: string
{
    /**
     * 阿里云
     * @link https://www.aliyun.com
     */
    case Aliyun = 'aliyun';

    /**
     * 阿里云 Rest
     * @link https://www.aliyun.com
     */
    case AliyunRest = 'aliyunrest';

    /**
     * 阿里云国际
     * @link https://www.alibabacloud.com/help/zh/doc-detail/160524.html
     */
    case AliyunIntl = 'aliyunintl';

    /**
     * 云片
     * @link https://www.yunpian.com
     */
    case Yunpian = 'yunpian';

    /**
     * Submail
     * @link https://www.mysubmail.com
     */
    case Submail = 'submail';

    /**
     * 螺丝帽
     * @link https://luosimao.com
     */
    case Luosimao = 'luosimao';

    /**
     * 容联云通讯
     * @link http://www.yuntongxun.com
     */
    case Yuntongxun = 'yuntongxun';

    /**
     * 互亿无线
     * @link http://www.ihuyi.com
     */
    case Huyi = 'huyi';

    /**
     * 聚合数据
     * @link https://www.juhe.cn
     */
    case Juhe = 'juhe';

    /**
     * SendCloud
     * @link http://www.sendcloud.net
     */
    case SendCloud = 'sendcloud';

    /**
     * 百度云
     * @link https://cloud.baidu.com
     */
    case Baidu = 'baidu';

    /**
     * 华信短信平台
     * @link http://www.ipyy.com
     */
    case Huaxin = 'huaxin';

    /**
     * 253云通讯（创蓝）
     * @link https://www.253.com
     */
    case Chuanglan = 'chuanglan';

    /**
     * 创蓝云智
     * @link https://www.chuanglan.com
     */
    case Chuanglanv1 = 'chuanglanv1';

    /**
     * 融云
     * @link http://www.rongcloud.cn
     */
    case RongCloud = 'rongcloud';

    /**
     * 天毅无线
     * @link http://www.85hu.com
     */
    case Tianyiwuxian = 'tianyiwuxian';

    /**
     * Twilio
     * @link https://www.twilio.com
     */
    case Twilio = 'twilio';

    /**
     * Tiniyo
     * @link https://www.tiniyo.com
     */
    case Tiniyo = 'tiniyo';

    /**
     * 腾讯云 SMS
     * @link https://cloud.tencent.com/product/sms
     */
    case QCloud = 'qcloud';

    /**
     * 华为云 SMS
     * @link https://www.huaweicloud.com/product/msgsms.html
     */
    case Huawei = 'huawei';

    /**
     * 网易云信
     * @link https://yunxin.163.com/sms
     */
    case Yunxin = 'yunxin';

    /**
     * 云之讯
     * @link https://www.ucpaas.com/index.html
     */
    case Yunzhixun = 'yunzhixun';

    /**
     * 凯信通
     * @link http://www.kingtto.cn
     */
    case Kingtto = 'kingtto';

    /**
     * 七牛云
     * @link https://www.qiniu.com
     */
    case Qiniu = 'qiniu';

    /**
     * UCloud
     * @link https://www.ucloud.cn
     */
    case UCloud = 'ucloud';

    /**
     * 短信宝
     * @link http://www.smsbao.com
     */
    case Smsbao = 'smsbao';

    /**
     * 摩杜云
     * @link https://www.moduyun.com
     */
    case Moduyun = 'moduyun';

    /**
     * 融合云（助通）
     * @link https://www.ztinfo.cn/products/sms
     */
    case Rongheyun = 'rongheyun';

    /**
     * 蜘蛛云
     * @link https://zzyun.com
     */
    case Zzyun = 'zzyun';

    /**
     * 融合云信
     * @link https://maap.wo.cn
     */
    case Maap = 'maap';

    /**
     * 天瑞云
     * @link http://cms.tinree.com
     */
    case Tinree = 'tinree';

    /**
     * 时代互联
     * @link https://www.now.cn
     */
    case Nowcn = 'nowcn';

    /**
     * 火山引擎
     * @link https://console.volcengine.com/sms
     */
    case Volcengine = 'volcengine';

    /**
     * 移动云MAS（黑名单模式）
     * @link https://mas.10086.cn
     */
    case Yidongmasblack = 'yidongmasblack';
}
