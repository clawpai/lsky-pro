/**
 * 从对象中剔除值为空、null、undefined、NaN、false、空数组和空对象的属性
 */
const removeEmptyProperties = (obj: any) => {
  return Object.fromEntries(
    Object.entries(obj).filter(([key, value]) => {
      if (value === null || value === undefined || value === '') return false; // 排除 null、undefined 和 空字符串
      if (Array.isArray(value) && value.length === 0) return false; // 排除 空数组
      if (typeof value === 'object' && value !== null && Object.keys(value).length === 0) return false; // 排除 空对象
      if (Number.isNaN(value)) return false; // 排除 NaN
      if (value === false) return false; // 排除 false
      return true; // 保留其他值
    })
  );
}

export default {
  removeEmptyProperties,
}