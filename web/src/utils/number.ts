/**
 * 格式化数字
 * @param number
 * @param decimals
 * @param decPoint
 * @param thousandsSep
 */
const format = (number: number, decimals: number = 0, decPoint: string = '.', thousandsSep: string = ','): string => {
  // 保证小数部分的精度
  const fixedNumber = number.toFixed(decimals)

  // 拆分整数部分和小数部分
  let [integerPart, decimalPart] = fixedNumber.split('.');

  // 处理千位分隔符
  integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep)

  // 如果有小数部分，则拼接上小数部分
  return decimalPart ? `${integerPart}${decPoint}${decimalPart}` : integerPart
}

/**
 * 将给定字节值作为字符串返回文件大小表示
 * @param bytes
 * @param decimals
 */
const fileSize = (bytes: number, decimals: number = 2): string => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))

  const size = bytes / Math.pow(k, i)
  return `${size.toFixed(decimals)} ${sizes[i]}`
}

/**
 * 格式化货币
 * @param amount 金额 (number)
 * @param currency 货币代码 (如 "USD", "CNY")
 * @param locale 语言环境代码 (如 "en-US", "zh-CN")
 * @returns 格式化后的货币字符串
 */
const currency = (amount: number, currency: string = 'CNY', locale: string = 'zh-CN'): string => {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency,
  }).format(amount)
}

export default {
  format,
  fileSize,
  currency,
}