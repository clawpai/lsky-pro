import mitt from 'mitt'

// 创建一个全局事件总线实例
const eventBus = mitt()

// 定义事件类型常量
export const EVENTS = {
  TRIGGER_UPLOAD_BUTTON: 'trigger-upload-button',
  OPEN_UPLOAD_QUEUE: 'open-upload-queue'
}

export default eventBus 