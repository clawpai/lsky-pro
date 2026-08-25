import {
  GithubFilled as GithubFilledIcon,
  QqCircleFilled as QqCircleFilledIcon,
} from "@vicons/antd";
import {AccessibilityOutline as AccessibilityOutlineIcon} from "@vicons/ionicons5";
import {type Component} from "vue";
import defaultAvatar from '@/assets/avatar.png'

/**
 * 获取社会化登录图标
 *
 * @param provider
 */
const getSocialiteIcon = (provider: string): Component => {
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
  getWithoutQueryUrl,
  getUserAvatar,
}