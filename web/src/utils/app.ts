import {
  GithubFilled as GithubFilledIcon,
  QqCircleFilled as QqCircleFilledIcon,
} from "@vicons/antd";
import {AccessibilityOutline as AccessibilityOutlineIcon} from "@vicons/ionicons5";
import {type Component, defineComponent, h} from "vue";
import defaultAvatar from '@/assets/avatar.png'
import qqIcon from '@/assets/icons/qq.png'
import wxIcon from '@/assets/icons/wx.png'
import sinaIcon from '@/assets/icons/sina.png'
import baiduIcon from '@/assets/icons/baidu.png'
import douyinIcon from '@/assets/icons/douyin.png'
import huaweiIcon from '@/assets/icons/huawei.png'
import googleIcon from '@/assets/icons/google.png'
import microsoftIcon from '@/assets/icons/microsoft.png'
import facebookIcon from '@/assets/icons/facebook.png'
import twitterIcon from '@/assets/icons/twitter.png'
import dingtalkIcon from '@/assets/icons/dingtalk.png'
import giteeIcon from '@/assets/icons/gitee.png'
import githubIcon from '@/assets/icons/github.png'

/**
 * 聚合登录方式对应的官方图标
 */
const aggregateCCIconMap: Record<string, string> = {
  qq: qqIcon,
  wx: wxIcon,
  sina: sinaIcon,
  baidu: baiduIcon,
  douyin: douyinIcon,
  huawei: huaweiIcon,
  google: googleIcon,
  microsoft: microsoftIcon,
  facebook: facebookIcon,
  twitter: twitterIcon,
  dingtalk: dingtalkIcon,
  gitee: giteeIcon,
  github: githubIcon,
}

/**
 * 聚合登录方式的显示名称（qq → QQ，wx → 微信 …）
 */
const aggregateCCNameMap: Record<string, string> = {
  qq: 'QQ',
  wx: '微信',
  sina: '微博',
  baidu: '百度',
  douyin: '抖音',
  huawei: '华为',
  google: '谷歌',
  microsoft: '微软',
  facebook: 'Facebook',
  twitter: 'Twitter',
  dingtalk: '钉钉',
  gitee: 'Gitee',
  github: 'GitHub',
}

/**
 * 获取社会化登录按钮显示名称：
 * 聚合 CC 驱动显示平台名（QQ/微信…），其余驱动显示驱动配置名称
 */
const getSocialiteDisplayName = (socialite: {provider: string, type?: string | null, name: string}): string => {
  if (socialite.provider === 'cc' && socialite.type) {
    return aggregateCCNameMap[socialite.type] || socialite.type
  }
  return socialite.name
}

/**
 * 将图片资源包装为 Vue 组件（供 NIcon 渲染）
 */
const imageIcon = (src: string): Component => {
  return defineComponent({
    name: 'SocialiteImageIcon',
    setup() {
      return () => h('img', {
        src,
        style: {width: '1em', height: '1em', borderRadius: '4px', objectFit: 'contain'},
        alt: '',
      })
    },
  })
}

/**
 * 获取社会化登录图标
 *
 * @param provider 登录驱动标识（github/qq/cc）
 * @param type 聚合登录方式（cc 驱动时为 qq/wx/sina 等）
 */
const getSocialiteIcon = (provider: string, type?: string | null): Component => {
  // 聚合 CC 登录：按登录方式显示官方图标
  if (provider === 'cc') {
    const icon = (type && aggregateCCIconMap[type]) ? aggregateCCIconMap[type] : undefined
    return icon ? imageIcon(icon) : AccessibilityOutlineIcon
  }

  return {
    qq: QqCircleFilledIcon,
    github: GithubFilledIcon,
  }[provider] || AccessibilityOutlineIcon
}

/**
 * 获取不包含 query 参数的 url
 */
const getWithoutQueryUrl = (): string => {
  return window.location.origin + window.location.pathname
}

/**
 * 获取用户头像
 * @param avatar
 */
const getUserAvatar = (avatar: string | undefined): string => {
  return avatar || defaultAvatar
}

export default {
  getSocialiteIcon,
  getSocialiteDisplayName,
  getWithoutQueryUrl,
  getUserAvatar,
}