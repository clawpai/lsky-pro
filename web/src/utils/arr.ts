/**
 * 随机获取数组中的某一项
 *
 * @param arr
 */
const randomItem = <T>(arr: T[]): T => arr[Math.floor(Math.random() * arr.length)]

export default {
  randomItem,
}