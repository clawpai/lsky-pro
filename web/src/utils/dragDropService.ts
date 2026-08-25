import eventBus, { EVENTS } from './eventBus'

interface DragDropConfig {
  dragText?: string
  dragSubtext?: string
}

class DragDropService {
  private registered: boolean = false
  private dragEnterHandler: EventListener | null = null
  private dragOverHandler: EventListener | null = null
  private dragLeaveHandler: EventListener | null = null
  private dropHandler: EventListener | null = null
  private dragCounter: number = 0 // 用于追踪拖拽进入/离开的计数
  private config: DragDropConfig = {}

  register(fileHandler: (files: FileList) => void, config: DragDropConfig = {}): boolean {
    if (this.registered) {
      return false
    }

    // 保存配置
    this.config = {
      dragText: config.dragText || '拖拽文件到此处上传',
      dragSubtext: config.dragSubtext || '支持图片格式: jpg, png, gif, webp',
      ...config
    }

    this.dragEnterHandler = (e: Event) => {
      e.preventDefault()
      this.dragCounter++
      
      if (this.dragCounter === 1) {
        this.showDragOverlay()
      }
    }

    this.dragOverHandler = (e: Event) => {
      e.preventDefault()
      const dragEvent = e as DragEvent
      if (dragEvent.dataTransfer) {
        dragEvent.dataTransfer.dropEffect = 'copy'
      }
    }

    // 拖拽离开处理
    this.dragLeaveHandler = (e: Event) => {
      e.preventDefault()
      this.dragCounter--
      
      if (this.dragCounter === 0) {
        this.hideDragOverlay()
      }
    }

    // 放置处理
    this.dropHandler = (e: Event) => {
      e.preventDefault()
      this.dragCounter = 0
      this.hideDragOverlay()
      
      const dragEvent = e as DragEvent
      const files = dragEvent.dataTransfer?.files
      
      if (files && files.length > 0) {
        fileHandler(files)
      }
    }

    document.addEventListener('dragenter', this.dragEnterHandler)
    document.addEventListener('dragover', this.dragOverHandler)
    document.addEventListener('dragleave', this.dragLeaveHandler)
    document.addEventListener('drop', this.dropHandler)
    
    this.registered = true
    return true
  }

  unregister(): boolean {
    if (!this.registered) {
      return false
    }

    // 移除事件监听
    if (this.dragEnterHandler) {
      document.removeEventListener('dragenter', this.dragEnterHandler)
      this.dragEnterHandler = null
    }
    
    if (this.dragOverHandler) {
      document.removeEventListener('dragover', this.dragOverHandler)
      this.dragOverHandler = null
    }
    
    if (this.dragLeaveHandler) {
      document.removeEventListener('dragleave', this.dragLeaveHandler)
      this.dragLeaveHandler = null
    }
    
    if (this.dropHandler) {
      document.removeEventListener('drop', this.dropHandler)
      this.dropHandler = null
    }

    // 确保隐藏拖拽提示
    this.hideDragOverlay()
    this.dragCounter = 0
    this.registered = false
    return true
  }

  isRegistered(): boolean {
    return this.registered
  }

  private showDragOverlay(): void {
    let overlay = document.getElementById('drag-drop-overlay')
    
    if (!overlay) {
      overlay = document.createElement('div')
      overlay.id = 'drag-drop-overlay'
      overlay.innerHTML = `
        <div class="drag-drop-content">
          <div class="drag-drop-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.89 22 5.99 22H18C19.1 22 20 21.1 20 20V8L14 2ZM18 20H6V4H13V9H18V20Z" fill="currentColor"/>
              <path d="M12 14L8 10H11V6H13V10H16L12 14Z" fill="currentColor"/>
            </svg>
          </div>
          <div class="drag-drop-text">${this.config.dragText}</div>
          <div class="drag-drop-subtext">${this.config.dragSubtext}</div>
        </div>
      `

      overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999999;
        backdrop-filter: blur(4px);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
      `

      const content = overlay.querySelector('.drag-drop-content') as HTMLElement
      if (content) {
        content.style.cssText = `
          display: flex;
          flex-direction: column;
          align-items: center;
          color: white;
          text-align: center;
          padding: 40px;
          border: 3px dashed rgba(255, 255, 255, 0.5);
          border-radius: 12px;
          background: rgba(255, 255, 255, 0.1);
          backdrop-filter: blur(8px);
          max-width: 400px;
          animation: dragBounce 0.6s ease-out;
        `
      }

      // 图标样式
      const icon = overlay.querySelector('.drag-drop-icon') as HTMLElement
      if (icon) {
        icon.style.cssText = `
          margin-bottom: 16px;
          opacity: 0.8;
        `
      }
      
      // 文本样式
      const text = overlay.querySelector('.drag-drop-text') as HTMLElement
      if (text) {
        text.style.cssText = `
          font-size: 20px;
          font-weight: 600;
          margin-bottom: 8px;
        `
      }
      
      // 子文本样式
      const subtext = overlay.querySelector('.drag-drop-subtext') as HTMLElement
      if (subtext) {
        subtext.style.cssText = `
          font-size: 14px;
          opacity: 0.7;
        `
      }
      
      // 添加动画样式
      const style = document.createElement('style')
      style.textContent = `
        @keyframes dragBounce {
          0% { transform: scale(0.8); opacity: 0; }
          50% { transform: scale(1.05); }
          100% { transform: scale(1); opacity: 1; }
        }
      `
      document.head.appendChild(style)
      
      document.body.appendChild(overlay)
    }
    
    // 显示覆盖层
    setTimeout(() => {
      if (overlay) {
        overlay.style.opacity = '1'
      }
    }, 10)
  }

  private hideDragOverlay(): void {
    const overlay = document.getElementById('drag-drop-overlay')
    if (overlay) {
      overlay.style.opacity = '0'
      setTimeout(() => {
        if (overlay.parentNode) {
          overlay.parentNode.removeChild(overlay)
        }
      }, 300)
    }
  }

  openUploadQueue(): void {
    eventBus.emit(EVENTS.OPEN_UPLOAD_QUEUE)
  }
}

const dragDropService = new DragDropService()
export default dragDropService 