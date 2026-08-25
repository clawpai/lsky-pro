/**
 * 判断是否为移动设备
 */
const isMobileDevice = (): boolean => {
  return window.innerWidth <= 768
}

export default {
  isMobileDevice,
}