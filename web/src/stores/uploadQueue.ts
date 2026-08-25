import { defineStore } from 'pinia'
import { ref } from 'vue'

// 上传文件类型定义
export interface UploadFile {
  id: string
  file: File
  objectUrl: string
  thumbnail: string | null
  status: 'pending' | 'uploading' | 'success' | 'error'
  progress: number
  error: string | null
  response: any | null
  formData?: Record<string, any>
}

export const useUploadQueueStore = defineStore('uploadQueue', () => {
  // 状态
  const fileQueue = ref<UploadFile[]>([])
  const showQueue = ref(false)
  const currentUploads = ref(0)
  
  // 计算属性
  const hasFiles = () => fileQueue.value.length > 0
  const isUploading = () => fileQueue.value.some(file => file.status === 'uploading')
  const uploadingCount = () => fileQueue.value.filter(file => file.status === 'uploading').length
  const overallProgress = () => {
    const uploadingFiles = fileQueue.value.filter(file => file.status === 'uploading')
    if (uploadingFiles.length === 0) return 0
    
    // 计算所有上传中文件的平均进度
    const totalProgress = uploadingFiles.reduce((sum, file) => sum + file.progress, 0)
    return Math.round(totalProgress / uploadingFiles.length)
  }
  
  // 方法
  function addFiles(files: UploadFile[]) {
    fileQueue.value.push(...files)
  }
  
  function removeFile(id: string) {
    const index = fileQueue.value.findIndex(file => file.id === id)
    if (index !== -1) {
      fileQueue.value.splice(index, 1)
    }
  }
  
  function updateFileStatus(id: string, status: UploadFile['status'], progress: number, error: string | null = null, response: any | null = null) {
    const file = fileQueue.value.find(file => file.id === id)
    if (file) {
      file.status = status
      file.progress = progress
      if (error !== null) file.error = error
      if (response !== null) file.response = response
    }
  }
  
  function updateFileFormData(id: string, formData: Record<string, any>) {
    const file = fileQueue.value.find(file => file.id === id)
    if (file) {
      file.formData = formData
    }
  }
  
  function updateFileThumbnail(id: string, thumbnail: string | null) {
    const file = fileQueue.value.find(file => file.id === id)
    if (file) {
      file.thumbnail = thumbnail
      // 触发一个浅复制来确保视图更新
      const index = fileQueue.value.findIndex(f => f.id === id)
      if (index !== -1) {
        fileQueue.value[index] = { ...fileQueue.value[index] }
      }
    }
  }
  
  function clearQueue() {
    // 只清空已完成或失败的文件
    fileQueue.value = fileQueue.value.filter(file => file.status === 'uploading' || file.status === 'pending')
  }
  
  function toggleQueue() {
    showQueue.value = !showQueue.value
  }
  
  function setShowQueue(show: boolean) {
    showQueue.value = show
  }
  
  function incrementCurrentUploads() {
    currentUploads.value++
  }
  
  function decrementCurrentUploads() {
    if (currentUploads.value > 0) {
      currentUploads.value--
    }
  }
  
  return {
    fileQueue,
    showQueue,
    currentUploads,
    hasFiles,
    isUploading,
    uploadingCount,
    overallProgress,
    addFiles,
    removeFile,
    updateFileStatus,
    updateFileFormData,
    updateFileThumbnail,
    clearQueue,
    toggleQueue,
    setShowQueue,
    incrementCurrentUploads,
    decrementCurrentUploads
  }
}) 