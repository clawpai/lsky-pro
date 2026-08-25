/**
 * 复制文本（兼容不支持 clipboard API 的浏览器）
 * @param content
 */
const copyTextFallback = (content: string): Promise<void> => {
  return new Promise((resolve, reject) => {
    const textArea = document.createElement("textarea");
    textArea.value = content
    textArea.style.position = "fixed"; // 避免滚动屏幕
    textArea.style.opacity = "0"; // 隐藏文本框
    document.body.appendChild(textArea)
    textArea.select()

    try {
      const successful = document.execCommand("copy")
      document.body.removeChild(textArea)
      successful ? resolve() : reject("Copy command was unsuccessful")
    } catch (err) {
      document.body.removeChild(textArea)
      reject("Copy command failed")
    }
  })
}

/**
 * 复制文本
 * @param content
 */
const copyText = (content: string): Promise<void> => {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    return navigator.clipboard.writeText(content)
  }

  return copyTextFallback(content)
}

export default {
  copyText
}