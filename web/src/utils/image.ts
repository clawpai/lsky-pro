import { copyImageToClipboard } from 'copy-image-clipboard'

/**
 * 复制图片到剪贴板
 * @param imageSource
 */
const copyImage = (imageSource: string): Promise<Blob> => {
  return copyImageToClipboard(imageSource);
}

export default {
  copyImage
}