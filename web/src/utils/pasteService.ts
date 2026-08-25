import eventBus, { EVENTS } from './eventBus'

// 定义全局粘贴服务
class PasteService {
  private registered: boolean = false
  private pasteHandler: EventListener | null = null

  // 注册粘贴事件处理函数
  register(handler: EventListener): boolean {
    // 如果已经注册了，返回false
    if (this.registered) {
      return false
    }

    // 记录处理函数并注册事件
    this.pasteHandler = handler
    document.addEventListener('paste', handler)
    this.registered = true
    return true
  }

  // 注销粘贴事件处理函数
  unregister(): boolean {
    // 如果没有注册，返回false
    if (!this.registered || !this.pasteHandler) {
      return false
    }

    // 移除事件监听
    document.removeEventListener('paste', this.pasteHandler)
    this.pasteHandler = null
    this.registered = false
    return true
  }

  // 检查是否已注册
  isRegistered(): boolean {
    return this.registered
  }

  // 触发上传队列显示
  openUploadQueue(): void {
    eventBus.emit(EVENTS.OPEN_UPLOAD_QUEUE)
  }
}

// 创建全局单例实例
const pasteService = new PasteService()

export default pasteService 