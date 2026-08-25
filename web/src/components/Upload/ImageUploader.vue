<template>
  <div class="image-uploader">
    <!-- 上传触发器 -->
    <input
      ref="fileInputRef"
      type="file"
      multiple
      accept="image/*"
      class="hidden"
      @change="handleFileSelect"
    />

    <!-- 队列浮窗 -->
    <NDrawer
      v-model:show="showQueue"
      :width="queueWidth"
      :height="deviceStore.isMobile ? '80%' : ''"
      :placement="deviceStore.isMobile ? 'bottom' : 'right'"
      :mask-closable="false"
      class="upload-queue-drawer"
    >
      <NDrawerContent :title="$t('Upload Queue')" closable :native-scrollbar="false" class="overflow-hidden">
        <!-- 批量操作按钮 -->
        <div class="flex justify-between mb-4">
          <div class="space-x-2">
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="default" 
                  size="small" 
                  @click="selectFiles"
                >
                  <template #icon>
                    <NIcon><folder-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('Select') }}
            </NTooltip>
            
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="default" 
                  size="small" 
                  @click="showRemoteDownload = !showRemoteDownload"
                >
                  <template #icon>
                    <NIcon><download-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('Remote Download') }}
            </NTooltip>
            
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="primary" 
                  size="small" 
                  :disabled="!hasFiles || isUploading" 
                  @click="uploadAll"
                >
                  <template #icon>
                    <NIcon><cloud-upload-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('All Upload') }}
            </NTooltip>
            
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="error" 
                  size="small" 
                  :disabled="!hasFiles" 
                  @click="removeAll"
                >
                  <template #icon>
                    <NIcon><trash-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('All Delete') }}
            </NTooltip>
            
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="warning" 
                  size="small" 
                  :disabled="!isUploading" 
                  @click="cancelAllUploads"
                >
                  <template #icon>
                    <NIcon><close-circle-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('All Cancel') }}
            </NTooltip>
            
            <NTooltip v-if="hasUploadedFiles" placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="info" 
                  size="small" 
                  @click="copyAllImageUrls"
                >
                  <template #icon>
                    <NIcon><copy-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('Copy All Links') }}
            </NTooltip>
          </div>
          <div>
            <NTooltip placement="bottom" trigger="hover">
              <template #trigger>
                <NButton 
                  type="info" 
                  size="small" 
                  @click="openGlobalForm"
                >
                  <template #icon>
                    <NIcon><settings-outline /></NIcon>
                  </template>
                </NButton>
              </template>
              {{ $t('Global Settings') }}
            </NTooltip>
          </div>
        </div>

        <!-- 远程下载区域 -->
        <div v-if="showRemoteDownload" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
          <div class="mb-3">
            <NText class="text-sm font-medium">{{ $t('Remote Download') }}</NText>
          </div>
          <NInput
            v-model:value="remoteUrls"
            type="textarea"
            :placeholder="$t('Please enter image URLs, one per line')"
            :autosize="{ minRows: 3, maxRows: 8 }"
            class="mb-3"
          />
          <div class="flex space-x-2">
            <NButton 
              type="primary" 
              size="small" 
              :loading="isDownloading"
              :disabled="!remoteUrls.trim()"
              @click="startRemoteDownload"
            >
              {{ $t('Start Download') }}
            </NButton>
            <NButton 
              type="default" 
              size="small" 
              @click="showRemoteDownload = false"
            >
              {{ $t('Collapse') }}
            </NButton>
          </div>
          <div v-if="downloadStatus.length > 0" class="mt-3">
            <NText class="text-sm text-gray-600">{{ $t('Download Progress') }}:</NText>
            <div class="mt-2 space-y-1">
              <div v-for="(status, index) in downloadStatus" :key="index" class="text-xs">
                <span :class="getStatusColor(status.status)">{{ status.url }}</span>
                <span class="ml-2 text-gray-500">{{ getStatusText(status.status) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- 上传限制提示 -->
        <div v-if="props.tip" class="mb-4 text-sm text-gray-500">
          {{ props.tip }}
        </div>

        <!-- 文件列表容器 -->
        <div class="files-container" :style="filesContainerStyle">
          <!-- PC和移动端统一使用滚动条组件 -->
          <NScrollbar>
            <div v-if="!hasFiles" class="py-4 text-center text-gray-500">
              {{ $t('No files, please select files to upload') }}
            </div>
            <div 
              v-for="(file, index) in fileQueue" 
              :key="file.id" 
              class="file-item mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800"
            >
              <div class="flex items-center">
                <!-- 缩略图 -->
                <div class="w-16 h-16 mr-3 overflow-hidden rounded-md bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                  <img 
                    v-if="file.thumbnail" 
                    :src="file.thumbnail" 
                    :alt="file.file.name" 
                    class="w-full h-full object-cover"
                  />
                  <NIcon v-else size="24" class="text-gray-400">
                    <image-outline />
                  </NIcon>
                </div>

                <!-- 文件信息 -->
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium truncate">{{ file.file.name }}</div>
                  <div class="text-xs text-gray-500">{{ formatFileSize(file.file.size) }}</div>
                  
                  <!-- 统一高度的状态区域 -->
                  <div class="mt-1 h-6 flex items-center">
                    <!-- 上传进度 -->
                    <div v-if="file.status === 'uploading'" class="w-full">
                      <NProgress 
                        :percentage="file.progress" 
                        :show-indicator="false" 
                        :height="4" 
                        processing
                      />
                    </div>
                    
                    <!-- 状态提示 -->
                    <div v-if="file.status === 'error'" class="text-xs text-red-500 truncate">
                      <!-- 短错误信息用tooltip显示 -->
                      <NTooltip 
                        v-if="file.error && file.error.length <= 100"
                        trigger="hover" 
                        placement="top" 
                        style="max-width: 300px;"
                      >
                        <template #trigger>
                          <span class="cursor-help">{{ file.error }}</span>
                        </template>
                        <div class="tooltip-content">{{ file.error }}</div>
                      </NTooltip>
                      
                      <!-- 长错误信息用modal显示 -->
                      <span 
                        v-else-if="file.error"
                        class="cursor-pointer underline"
                        @click="showErrorDetails(file.error)"
                      >
                        {{ file.error.substring(0, 50) + '...' }}
                      </span>
                      
                      <span v-else>{{ $t('Upload Failed') }}</span>
                    </div>
                    <div v-if="file.status === 'success'" class="text-xs text-green-500 flex items-center">
                      {{ $t('Upload Successful') }}
                    </div>
                    <div v-if="file.status === 'pending'" class="text-xs text-gray-500 flex items-center">
                      <NIcon size="14" class="mr-1"><time-outline /></NIcon>
                      {{ $t('Waiting for upload') }}
                    </div>
                  </div>
                </div>
              </div>

             <div class="flex justify-between mt-2">
               <div>
                 <!-- 添加复制链接和嵌入代码的按钮 -->
                 <div v-if="file.status === 'success'" class="flex space-x-1">
                   <NButton
                     quaternary
                     circle
                     size="small"
                     :title="$t('Copy URL')"
                     @click.stop="copyImageUrl(file)"
                   >
                     <template #icon><NIcon><copy-outline /></NIcon></template>
                   </NButton>
                   <NButton
                     quaternary
                     circle
                     size="small"
                     :title="$t('Embed Codes')"
                     @click.stop="showEmbedCodes(file)"
                   >
                     <template #icon><NIcon><code-outline /></NIcon></template>
                   </NButton>
                 </div>
               </div>
               <!-- 操作按钮 -->
               <div class="flex space-x-1">
                 <NButton
                   quaternary
                   circle
                   size="small"
                   :title="$t('Edit')"
                   :disabled="file.status === 'uploading'"
                   @click="editImage(index)"
                 >
                   <template #icon><NIcon><edit-outline /></NIcon></template>
                 </NButton>
                 <NButton
                   quaternary
                   circle
                   size="small"
                   :title="$t('Settings')"
                   :disabled="file.status === 'uploading'"
                   @click="openFileForm(index)"
                 >
                   <template #icon><NIcon><settings-outline /></NIcon></template>
                 </NButton>
                 <NButton
                   quaternary
                   circle
                   size="small"
                   :title="$t('Upload')"
                   :disabled="file.status === 'uploading' || file.status === 'success'"
                   @click="file.status === 'error' ? retryUpload(index) : uploadSingleFile(index)"
                 >
                   <template #icon><NIcon><cloud-upload-outline /></NIcon></template>
                 </NButton>
                 <NButton
                   quaternary
                   circle
                   size="small"
                   :title="$t('Cancel')"
                   :disabled="file.status !== 'uploading'"
                   @click="cancelUpload(index)"
                 >
                   <template #icon><NIcon><close-circle-outline /></NIcon></template>
                 </NButton>
                 <NButton
                   quaternary
                   circle
                   size="small"
                   :title="$t('Delete')"
                   :disabled="file.status === 'uploading'"
                   @click="removeFile(index)"
                 >
                   <template #icon><NIcon><trash-outline /></NIcon></template>
                 </NButton>
               </div>
             </div>

            </div>
                      </NScrollbar>
          </div>
      </NDrawerContent>
    </NDrawer>

    <!-- 悬浮队列按钮 - 使用Teleport将其移动到body下，确保最高层级 -->
    <Teleport to="body">
      <div 
        v-if="hasFiles && !showQueue" 
        class="floating-queue-button" 
        @mousedown.stop.prevent="openFloatingQueue"
        @click.stop.prevent="openFloatingQueue"
      >
        <NBadge :value="uploadingCount > 0 ? uploadingCount : undefined" :processing="isUploading">
          <NButton circle type="primary">
            <template #icon>
              <NIcon>
                <cloud-upload-outline />
              </NIcon>
            </template>
          </NButton>
        </NBadge>
        <div v-if="isUploading" class="upload-indicator">
          <div class="upload-indicator-text">{{ $t('Uploading') }}</div>
          <NProgress 
            :percentage="overallProgress" 
            :show-indicator="false" 
            processing
            :height="4"
          />
        </div>
      </div>
    </Teleport>

    <!-- 图片编辑模态框 -->
    <NModal
      v-model:show="showEditor"
      preset="card"
      style="width: 90%; max-width: 800px;"
      :title="$t('Image Editing')"
      :mask-closable="false"
    >
      <div v-if="currentEditingFile && showEditor" class="editor-wrapper">
        <div class="cropper-wrapper">
          <cropper
            ref="cropperRef"
            class="cropper"
            :src="currentEditingFile.objectUrl"
            :stencil-props="{
              aspectRatio: currentAspectRatio,
              handlers: {
                eastNorth: true,
                north: true,
                westNorth: true,
                west: true,
                westSouth: true,
                south: true,
                eastSouth: true,
                east: true
              },
              movable: true,
              resizable: true
            }"
            :image-restrictions="{
              minWidth: 100,
              minHeight: 100
            }"
            :image-class="'custom-image'"
            :image-style="{
              filter: `brightness(${imageFilters.brightness}%) contrast(${imageFilters.contrast}%) saturate(${imageFilters.saturate}%)`
            }"
            @change="handleCropChange"
          />
        </div>
        
        <!-- 编辑功能 -->
        <div class="flex flex-wrap justify-between mt-4 mb-4 gap-2 editor-section">
          <div class="space-x-1 flex flex-wrap gap-1 editor-buttons">
            <NButton @click="rotateImage(-90)" size="small">
              <template #icon><NIcon><rotate-left-outline /></NIcon></template>
              {{ $t('Rotate Left') }}
            </NButton>
            <NButton @click="rotateImage(90)" size="small">
              <template #icon><NIcon><rotate-right-outline /></NIcon></template>
              {{ $t('Rotate Right') }}
            </NButton>
            <NButton @click="flipImage('horizontal')" size="small">
              <template #icon><NIcon><swap-horizontal-outline /></NIcon></template>
              {{ $t('Flip Horizontal') }}
            </NButton>
            <NButton @click="flipImage('vertical')" size="small">
              <template #icon><NIcon><swap-vertical-outline /></NIcon></template>
              {{ $t('Flip Vertical') }}
            </NButton>
            <NButton @click="resetRotation()" size="small">
              <template #icon><NIcon><refresh-outline /></NIcon></template>
              {{ $t('Reset Angle') }}
            </NButton>
            <NButton @click="zoomIn()" size="small">
              <template #icon><NIcon><add-outline /></NIcon></template>
              {{ $t('Zoom In') }}
            </NButton>
            <NButton @click="zoomOut()" size="small">
              <template #icon><NIcon><remove-outline /></NIcon></template>
              {{ $t('Zoom Out') }}
            </NButton>
          </div>
        </div>
        
        <!-- 分隔线 -->
        <div class="section-divider"></div>
        
        <!-- 宽高比选择器 -->
        <div class="mb-4 ratio-section">
          <NRadioGroup v-model:value="selectedAspectRatio" name="aspectRatio" class="aspect-ratio-group">
            <NButton 
              :quaternary="selectedAspectRatio !== 'free'" 
              :type="selectedAspectRatio === 'free' ? 'primary' : 'default'" 
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = 'free'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <path fill="currentColor" d="M21,19V5c0-1.1-0.9-2-2-2H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14C20.1,21,21,20.1,21,19z M21,5v14H5V5H21z M17.2,12.5H15v-2h5v5h-2v-2.2l-3,3l-1.4-1.4L17.2,12.5z M6.8,12.5l3-3l1.4,1.4l-3,3H10v2H5v-5h2V12.5z"/>
                </svg>
              </template>
              <span class="ml-1">{{ $t('Free') }}</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '1:1'" 
              :type="selectedAspectRatio === '1:1' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '1:1'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="5" y="5" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">1:1</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '4:3'" 
              :type="selectedAspectRatio === '4:3' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '4:3'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="3" y="6" width="18" height="13.5" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">4:3</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '16:9'" 
              :type="selectedAspectRatio === '16:9' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '16:9'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="2" y="7" width="20" height="11.25" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">16:9</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '16:10'" 
              :type="selectedAspectRatio === '16:10' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '16:10'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="2" y="6" width="20" height="12.5" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">16:10</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '2:3'" 
              :type="selectedAspectRatio === '2:3' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '2:3'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="8" y="3" width="8" height="18" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">2:3</span>
            </NButton>
            <NButton 
              :quaternary="selectedAspectRatio !== '3:2'" 
              :type="selectedAspectRatio === '3:2' ? 'primary' : 'default'"
              class="aspect-ratio-btn" 
              @click="selectedAspectRatio = '3:2'"
            >
              <template #icon>
                <svg viewBox="0 0 24 24" class="aspect-ratio-icon">
                  <rect x="3" y="6" width="18" height="12" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
              </template>
              <span class="ml-1">3:2</span>
            </NButton>
          </NRadioGroup>
        </div>
        
        <!-- 分隔线 -->
        <div class="section-divider"></div>
        
        <!-- 亮度/对比度/饱和度调整 -->
        <div class="mb-4 filter-section">
          <div class="mb-2 flex items-center">
            <span class="inline-block w-20">{{ $t('Brightness') }}:</span>
            <NSlider v-model:value="imageFilters.brightness" :min="0" :max="200" :step="5" class="flex-1" @update:value="updatePreviewFilter" />
            <span class="ml-2 w-10 text-right">{{ imageFilters.brightness }}%</span>
          </div>
          <div class="mb-2 flex items-center">
            <span class="inline-block w-20">{{ $t('Contrast') }}:</span>
            <NSlider v-model:value="imageFilters.contrast" :min="0" :max="200" :step="5" class="flex-1" @update:value="updatePreviewFilter" />
            <span class="ml-2 w-10 text-right">{{ imageFilters.contrast }}%</span>
          </div>
          <div class="mb-2 flex items-center">
            <span class="inline-block w-20">{{ $t('Saturation') }}:</span>
            <NSlider v-model:value="imageFilters.saturate" :min="0" :max="200" :step="5" class="flex-1" @update:value="updatePreviewFilter" />
            <span class="ml-2 w-10 text-right">{{ imageFilters.saturate }}%</span>
          </div>
        </div>
      </div>
      
      <div class="flex justify-end mt-4 space-x-2">
        <NButton @click="resetFilters">{{ $t('Reset Filters') }}</NButton>
        <NButton @click="cancelEdit">{{ $t('Cancel') }}</NButton>
        <NButton type="primary" @click="confirmEdit">{{ $t('Confirm') }}</NButton>
      </div>
    </NModal>

    <!-- 全局设置模态框 -->
    <NModal
      v-model:show="globalFormVisible"
      preset="card"
      style="width: 90%; max-width: 600px;"
      :title="$t('Upload Global Settings')"
      :mask-closable="false"
    >
      <NForm
        ref="formRef"
        :model="globalForm"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
      >
        <NFormItem :label="$t('Storage Location')" path="storage_id">
          <NSelect
            v-model:value="globalForm.storage_id"
            :options="storages"
            :placeholder="$t('Please select storage location')"
            label-field="name"
            value-field="id"
            filterable
          />
        </NFormItem>
        <NFormItem :label="$t('Album')" path="album_id" v-if="userStore.isLoggedIn">
          <NSelect
            v-model:value="globalForm.album_id"
            :options="albums"
            :placeholder="$t('Please select album')"
            label-field="name"
            value-field="id"
            filterable
            clearable
          />
        </NFormItem>
        <NFormItem :label="$t('Expiration Time')" path="expired_at">
          <NDatePicker
            v-model:formatted-value="globalForm.expired_at"
            type="datetime"
            :placeholder="$t('Select expiration time')"
            :is-date-disabled="(timestamp: number) => timestamp < Date.now()"
            value-format="yyyy-MM-dd HH:mm:ss"
            clearable
            style="width: 100%"
          />
        </NFormItem>
        <NFormItem :label="$t('Tags')" path="tags[]">
          <NDynamicTags v-model:value="globalForm['tags[]']" />
        </NFormItem>
        <NFormItem :label="$t('Is Public')" path="is_public">
          <NSwitch
            v-model:value="globalForm.is_public"
            checked-value="1"
            unchecked-value="0"
          />
        </NFormItem>
      </NForm>
      <div class="flex justify-end mt-4">
        <NButton @click="globalFormVisible = false">{{ $t('Cancel') }}</NButton>
        <NButton type="primary" class="ml-2" @click="confirmGlobalForm">{{ $t('Confirm') }}</NButton>
      </div>
    </NModal>

    <!-- 文件设置模态框 -->
    <NModal
      v-model:show="fileFormVisible"
      preset="card"
      style="width: 90%; max-width: 600px;"
      :title="$t('File Settings')"
      :mask-closable="false"
    >
      <NForm
        ref="fileFormRef"
        :model="fileForm"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
      >
        <NFormItem :label="$t('Storage Location')" path="storage_id">
          <NSelect
            v-model:value="fileForm.storage_id"
            :options="storages"
            :placeholder="$t('Please select storage location')"
            label-field="name"
            value-field="id"
            filterable
          />
        </NFormItem>
        <NFormItem :label="$t('Album')" path="album_id" v-if="userStore.isLoggedIn">
          <NSelect
            v-model:value="fileForm.album_id"
            :options="albums"
            :placeholder="$t('Please select album')"
            label-field="name"
            value-field="id"
            filterable
            clearable
          />
        </NFormItem>
        <NFormItem :label="$t('Expiration Time')" path="expired_at">
          <NDatePicker
            v-model:formatted-value="fileForm.expired_at"
            type="datetime"
            :placeholder="$t('Select expiration time')"
            :is-date-disabled="(timestamp: number) => timestamp < Date.now()"
            value-format="yyyy-MM-dd HH:mm:ss"
            clearable
            style="width: 100%"
          />
        </NFormItem>
        <NFormItem :label="$t('Tags')" path="tags[]">
          <NDynamicTags v-model:value="fileForm['tags[]']" />
        </NFormItem>
        <NFormItem :label="$t('Is Public')" path="is_public">
          <NSwitch
            v-model:value="fileForm.is_public"
            checked-value="1"
            unchecked-value="0"
          />
        </NFormItem>
      </NForm>
      <div class="flex justify-end mt-4">
        <NButton @click="fileFormVisible = false">{{ $t('Cancel') }}</NButton>
        <NButton type="primary" class="ml-2" @click="confirmFileForm">{{ $t('Confirm') }}</NButton>
      </div>
    </NModal>

    <!-- 添加嵌入代码模态框 -->
    <NModal
      v-model:show="embedCodesVisible"
      preset="card"
      style="width: 90%; max-width: 600px;"
      :title="$t('Embed Codes')"
    >
      <div v-if="currentEmbedFile">
        <div class="mb-4">
          <p class="text-sm text-gray-500 mb-2">{{ $t('Image') }}: {{ currentEmbedFile.file.name }}</p>
          <NImage
            v-if="currentEmbedFile.response && currentEmbedFile.response.data && currentEmbedFile.response.data.public_url"
            :src="currentEmbedFile.response.data.public_url"
            object-fit="contain"
            :width="150"
            class="rounded mb-3"
          />
        </div>

        <NTabs type="line">
          <NTabPane name="html" :tab="$t('HTML')">
            <NInputGroup>
              <NInput
                :value="getEmbedCode('html')"
                readonly
                type="textarea"
              />
              <NButton
                type="primary"
                ghost
                @click="copyText(getEmbedCode('html'))"
              >
                {{ $t('Copy') }}
              </NButton>
            </NInputGroup>
          </NTabPane>
          
          <NTabPane name="markdown" :tab="$t('Markdown')">
            <NInputGroup>
              <NInput
                :value="getEmbedCode('markdown')"
                readonly
                type="textarea"
              />
              <NButton
                type="primary"
                ghost
                @click="copyText(getEmbedCode('markdown'))"
              >
                {{ $t('Copy') }}
              </NButton>
            </NInputGroup>
          </NTabPane>
          
          <NTabPane name="markdown_with_link" :tab="$t('Markdown with Link')">
            <NInputGroup>
              <NInput
                :value="getEmbedCode('markdown_with_link')"
                readonly
                type="textarea"
              />
              <NButton
                type="primary"
                ghost
                @click="copyText(getEmbedCode('markdown_with_link'))"
              >
                {{ $t('Copy') }}
              </NButton>
            </NInputGroup>
          </NTabPane>
          
          <NTabPane name="bbcode" :tab="$t('BBCode')">
            <NInputGroup>
              <NInput
                :value="getEmbedCode('bbcode')"
                readonly
                type="textarea"
              />
              <NButton
                type="primary"
                ghost
                @click="copyText(getEmbedCode('bbcode'))"
              >
                {{ $t('Copy') }}
              </NButton>
            </NInputGroup>
          </NTabPane>
          
          <NTabPane name="url" :tab="$t('URL')">
            <NInputGroup>
              <NInput
                :value="getEmbedCode('url')"
                readonly
              />
              <NButton
                type="primary"
                ghost
                @click="copyText(getEmbedCode('url'))"
              >
                {{ $t('Copy') }}
              </NButton>
            </NInputGroup>
          </NTabPane>
        </NTabs>
      </div>
      
      <div class="flex justify-end mt-4">
        <NButton @click="embedCodesVisible = false">{{ $t('Close') }}</NButton>
      </div>
    </NModal>

    <!-- 错误详情模态框 -->
    <NModal
      v-model:show="errorModalVisible"
      preset="dialog"
      :title="$t('Upload Error Details')"
      :close-on-esc="true"
      style="width: 90%; max-width: 600px;"
    >
      <div class="error-details-content">
        <div class="text-red-500 mb-2">{{ $t('Error Message') }}:</div>
        <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded overflow-auto max-h-80 whitespace-pre-wrap break-all text-sm">
          {{ currentErrorMessage }}
        </div>
      </div>
    </NModal>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onBeforeUnmount, onMounted, nextTick, Teleport, onUnmounted, watch } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import { 
  NDrawer, 
  NDrawerContent, 
  NButton, 
  NProgress, 
  NScrollbar, 
  NModal, 
  NIcon,
  NSlider,
  NTooltip,
  NRadioGroup,
  NForm,
  NFormItem,
  NSelect,
  NDynamicTags,
  NSwitch,
  NDatePicker,
  NBadge,
  NInput,
  NInputGroup,
  NTabs,
  NTabPane,
  NImage,
  NText,
  useMessage,
  type FormInst,
  type FormRules
} from 'naive-ui'
import { 
  ImageOutline, 
  CloseCircleOutline, 
  TrashOutline, 
  CloudUploadOutline,
  PencilOutline as EditOutline,
  SwapHorizontalOutline,
  SwapVerticalOutline,
  RefreshOutline as RotateLeftOutline,
  RefreshCircleOutline as RotateRightOutline,
  TimeOutline,
  RefreshOutline,
  AddOutline,
  RemoveOutline,
  SettingsOutline,
  CopyOutline,
  CodeOutline,
  FolderOutline,
  DownloadOutline
} from '@vicons/ionicons5'
import { useDeviceStore } from '@/stores/device'
import { useUserStore } from '@/stores/user'
import { useConfigStore } from '@/stores/config'
import { usePhotoStore } from '@/stores/photo'
import { useUploadQueueStore, type UploadFile } from '@/stores/uploadQueue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { getUserAlbums } from '@/api'
import axios from 'axios'
import { v4 as uuidv4 } from 'uuid'
import pasteService from '@/utils/pasteService'
import dragDropService from '@/utils/dragDropService'
import strUtils from '@/utils/str'

// 定义属性
const props = defineProps({
  endpoint: { type: String, default: '/api/v2/upload' },
  maxFileSize: { type: Number, default: 5242880 }, // 5MB
  minFileSize: { type: Number, default: 0 },
  maxTotalFileSize: { type: Number, default: 0 },
  maxNumberOfFiles: { type: Number, default: 0 },
  minNumberOfFiles: { type: Number, default: 0 },
  allowedFileTypes: { type: Array, default: () => ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp', '.tif'] },
  concurrency: { type: Number, default: 3 },
  formData: { type: Object, default: () => ({}) },
  headers: { type: Object, default: () => ({}) },
  tip: { type: String, default: '' },
  defaultStorage: { type: String, default: '' },
  continueQueue: { type: Boolean, default: true }
})

// 定义事件
const emit = defineEmits(['upload-success', 'upload-error', 'upload-progress', 'queue-change'])

// 引入stores
const deviceStore = useDeviceStore()
const userStore = useUserStore()
const configStore = useConfigStore()
const uploadQueueStore = useUploadQueueStore()
const message = useMessage()
const i18n = useI18n()

// 文件输入引用
const fileInputRef = ref<HTMLInputElement | null>(null)
const cropperRef = ref<any>(null)

// 状态变量
const showEditor = ref(false)
const currentEditingIndex = ref<number | null>(null)
const currentEditingFile = ref<UploadFile | null>(null)
const cropResult = ref<any>(null)
const uploadCancelTokens = ref<Record<string, any>>({})
const embedCodesVisible = ref(false)
const currentEmbedFile = ref<UploadFile | null>(null)
const errorModalVisible = ref(false)
const currentErrorMessage = ref('')

// 远程下载相关状态
const showRemoteDownload = ref(false)
const remoteUrls = ref('')
const isDownloading = ref(false)
const downloadStatus = ref<Array<{url: string, status: 'pending' | 'downloading' | 'success' | 'error', error?: string}>>([])
const downloadCancelTokens = ref<Record<string, any>>({})

// 使用全局store中的状态
const fileQueue = computed(() => uploadQueueStore.fileQueue)
const showQueue = computed({
  get: () => uploadQueueStore.showQueue,
  set: (value) => uploadQueueStore.setShowQueue(value)
})
const currentUploads = computed({
  get: () => uploadQueueStore.currentUploads,
  set: (value) => {
    // 这里处理当设置currentUploads时，自动更新store中的值
    if (value > uploadQueueStore.currentUploads) {
      uploadQueueStore.incrementCurrentUploads()
    } else if (value < uploadQueueStore.currentUploads) {
      uploadQueueStore.decrementCurrentUploads()
    }
  }
})

// 计算属性 - 使用store中的方法
const hasFiles = computed(() => uploadQueueStore.hasFiles())
const isUploading = computed(() => uploadQueueStore.isUploading())
const uploadingCount = computed(() => uploadQueueStore.uploadingCount())
const overallProgress = computed(() => uploadQueueStore.overallProgress())
const hasUploadedFiles = computed(() => fileQueue.value.some(file => file.status === 'success'))

// 裁剪宽高比设置
const selectedAspectRatio = ref('free')
const currentAspectRatio = computed(() => {
  if (selectedAspectRatio.value === 'free') return undefined
  
  const [width, height] = selectedAspectRatio.value.split(':').map(Number)
  return width / height
})

// 添加全局表单设置和单独文件表单设置的状态变量
const globalFormVisible = ref(false)
const fileFormVisible = ref(false)
const currentFileIndex = ref<number | null>(null)
// 创建全局表单初始值的计算函数
const getInitialStorageId = () => {
  // 优先使用用户设置的默认储存
  const userDefaultStorageId = (userStore.profile?.options as any)?.default_storage_id
  if (userDefaultStorageId) {
    // 检查储存是否还存在
    const storages = configStore.group?.storages || []
    const storageExists = storages.some(s => s.id === userDefaultStorageId)
    if (storageExists) {
      return userDefaultStorageId
    }
  }
  
  // 如果用户没有设置或储存不存在，使用第一个可用储存
  const storages = configStore.group?.storages || []
  return storages.length > 0 ? storages[0].id : undefined
}

const globalForm = ref<any>({
  storage_id: getInitialStorageId(),
  album_id: undefined,
  expired_at: undefined,
  'tags[]': [],
  is_public: false,
})
const fileForm = ref<any>({
  storage_id: undefined,
  album_id: undefined,
  expired_at: undefined,
  'tags[]': [],
  is_public: undefined,
})
const formRef = ref<FormInst | null>(null)
const fileFormRef = ref<FormInst | null>(null)

// 添加存储和相册数据源
const storages = computed(() => configStore.group?.storages || [])
const albums = ref<any>([])

// 添加表单验证规则
const formRules: FormRules = {
  storage_id: [{
    required: true,
    message: i18n.t('Please select storage location'),
    trigger: ['change']
  }],
  'tags[]': {
    trigger: ['change'],
    validator(rule: unknown, value: string[]) {
      if (value.length >= 5) {
        return new Error(i18n.t('Tags cannot exceed 4'))
      }
      return true
    }
  }
}

// 添加queueWidth计算属性
const queueWidth = computed(() => deviceStore.isMobile ? '100%' : '450px')

// 计算文件容器的动态样式
const filesContainerStyle = computed(() => {
  const baseHeight = deviceStore.isMobile ? '80vh' : '100vh'
  const baseOffset = deviceStore.isMobile ? 160 : 190
  const remoteDownloadOffset = showRemoteDownload.value ? (deviceStore.isMobile ? 120 : 160) : 0
  const totalOffset = baseOffset + remoteDownloadOffset
  
  return {
    height: `calc(${baseHeight} - ${totalOffset}px)`,
    transition: 'height 0.3s ease'
  }
})

// 在文件顶部script部分添加上传统计变量
const uploadStats = ref({
  total: 0,
  success: 0,
  failed: 0
})

// 组件挂载时初始化
onMounted(async () => {
  // 初始化默认存储位置
  if (props.defaultStorage) {
    globalForm.value.storage_id = props.defaultStorage
  }
  
  // 获取用户相册
  await fetchUserAlbums()
  
  // 使用粘贴服务注册粘贴事件处理
  pasteService.register(handlePaste)
  
  // 使用拖拽服务注册拖拽事件处理
  dragDropService.register(handleDragDrop, {
    dragText: i18n.t('Drag files here to upload'),
    dragSubtext: props.tip,
  })
})

// 监听远程下载区域展开状态，确保滚动条正确重新计算
watch(showRemoteDownload, () => {
  // 延迟执行以确保DOM更新完成
  nextTick(() => {
    // 触发滚动条重新计算
    const scrollbarElement = document.querySelector('.files-container .NScrollbar')
    if (scrollbarElement && (scrollbarElement as any)?.__scrollbar) {
      (scrollbarElement as any).__scrollbar.sync()
    }
  })
})

// 监听用户profile变化，更新默认储存
watch(() => userStore.profile?.options, () => {
  // 重新设置默认储存
  globalForm.value.storage_id = getInitialStorageId()
}, { deep: true })

// 监听储存变化，如果当前选择的储存被删除，重新设置
watch(() => configStore.group?.storages, () => {
  const currentStorageId = globalForm.value.storage_id
  if (currentStorageId) {
    const storages = configStore.group?.storages || []
    const storageExists = storages.some(s => s.id === currentStorageId)
    if (!storageExists) {
      globalForm.value.storage_id = getInitialStorageId()
    }
  }
}, { deep: true })

// 在组件卸载时移除粘贴事件监听器
onUnmounted(() => {
  // 取消所有上传，但不清空队列
  cancelAllUploads()
  
  // 取消所有下载
  Object.values(downloadCancelTokens.value).forEach((cancelToken: any) => {
    if (cancelToken && cancelToken.cancel) {
      cancelToken.cancel(i18n.t('Component unmounted'))
    }
  })
  downloadCancelTokens.value = {}
  
  // 使用粘贴服务注销事件监听
  pasteService.unregister()
  
  // 使用拖拽服务注销事件监听
  dragDropService.unregister()
})

// 获取单独提取获取相册方法，方便重试和调试
async function fetchUserAlbums() {
  if (!userStore.isLoggedIn) return
  
  try {
    // 正确调用getUserAlbums，使用query参数
    const response = await getUserAlbums({
      query: {
        page: 1,
        per_page: 1000 // 获取足够多的相册
      }
    })
    
    if (response.data && response.data.data) {
      // 直接赋值，不做映射转换，保持原始数据结构
      albums.value = response.data.data.data
    }
  } catch (error) {
    message.error(i18n.t('Failed to get albums, please try again'))
  }
}

// 打开全局表单设置时，确保已加载相册
function openGlobalForm() {
  // 如果相册为空，尝试重新获取
  if (userStore.isLoggedIn && albums.value.length === 0) {
    fetchUserAlbums()
  }
  
  globalFormVisible.value = true
}

// 打开文件表单设置时，确保已加载相册
function openFileForm(index: number) {
  // 如果相册为空，尝试重新获取
  if (userStore.isLoggedIn && albums.value.length === 0) {
    fetchUserAlbums()
  }
  
  currentFileIndex.value = index
  const file = fileQueue.value[index]
  
  // 如果文件已有自定义表单数据，则使用它
  if (file.formData) {
    fileForm.value = { ...file.formData }
  } else {
    // 否则重置表单，使用全局设置
    fileForm.value = {
      storage_id: globalForm.value.storage_id,
      album_id: globalForm.value.album_id,
      expired_at: globalForm.value.expired_at,
      'tags[]': [...(globalForm.value['tags[]'] || [])],
      is_public: globalForm.value.is_public,
    }
  }
  
  fileFormVisible.value = true
}

// 修复updateFileFormData参数类型问题 - 在使用updateFileFormData之前添加类型检查
function confirmFileForm() {
  if (currentFileIndex.value === null) return
  
  // 获取当前文件索引
  const index = currentFileIndex.value
  
  // 无论如何都关闭模态框，避免用户卡在设置界面
  fileFormVisible.value = false
  
  // 检查存储位置是否已选择
  if (!fileForm.value.storage_id) {
    message.error(i18n.t('Please select storage location'))
    currentFileIndex.value = null
    return
  }
  
  // 保存表单数据到文件
  const file = fileQueue.value[index]
  const formDataCopy = JSON.parse(JSON.stringify(fileForm.value)) as Record<string, any>
  file.formData = formDataCopy
  uploadQueueStore.updateFileFormData(file.id, formDataCopy)
  
  currentFileIndex.value = null
  message.success(i18n.t('File settings saved'))
}

// 准备上传的表单数据
function prepareFormData(file: UploadFile): Record<string, any> {
  const baseFormData = { ...props.formData }
  const globalSettings = { ...globalForm.value }
  const fileSettings = file.formData || {}
  
  // 合并表单数据，文件设置 > 全局设置 > props传入的默认值
  const mergedData = { ...baseFormData, ...globalSettings, ...fileSettings }
  
  // 过滤null和undefined值
  return Object.fromEntries(
    Object.entries(mergedData).filter(([_, value]) => value !== null && value !== undefined)
  )
}

// 图片滤镜状态
const imageFilters = ref({
  brightness: 100,
  contrast: 100,
  saturate: 100,
  rotate: 0,
  flipH: 1,
  flipV: 1
})

// 文件处理函数
function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return

  const files = Array.from(input.files)
  
  // 验证文件数量
  if (props.maxNumberOfFiles > 0 && (fileQueue.value.length + files.length) > props.maxNumberOfFiles) {
    message.error(i18n.t('You can upload a maximum of {count} files', {count: props.maxNumberOfFiles}))
    input.value = ''
    return
  }
  
  // 验证文件类型
  const invalidFiles = files.filter(file => {
    const ext = '.' + file.name.split('.').pop()?.toLowerCase()
    return !props.allowedFileTypes.includes(ext) && !props.allowedFileTypes.includes('*')
  })
  
  if (invalidFiles.length > 0) {
    message.error(i18n.t('Unsupported file type') + ': ' + invalidFiles.map(f => f.name).join(', '))
    // 从选择中移除无效文件
    const validFiles = files.filter(file => !invalidFiles.includes(file))
    if (validFiles.length === 0) {
      input.value = ''
      return
    }
  }
  
  // 处理文件
  const uploadFiles: UploadFile[] = []
  files.forEach(file => {
    // 验证文件大小
    if (props.maxFileSize > 0 && file.size > props.maxFileSize) {
      message.error(i18n.t('File {filename} exceeds the maximum limit of {size}', {filename: file.name, size: formatFileSize(props.maxFileSize)}))
      return
    }
    
    if (props.minFileSize > 0 && file.size < props.minFileSize) {
      message.error(i18n.t('File {filename} is less than the minimum limit of {size}', {filename: file.name, size: formatFileSize(props.minFileSize)}))
      return
    }
    
    // 创建文件对象
    const objectUrl = URL.createObjectURL(file)
    const uploadFile: UploadFile = {
      id: uuidv4(),
      file: file,
      objectUrl: objectUrl,
      thumbnail: null,
      status: 'pending',
      progress: 0,
      error: null,
      response: null
    }
    
    // 生成缩略图
    createThumbnail(uploadFile)
    
    // 添加到上传队列数组
    uploadFiles.push(uploadFile)
  })
  
  // 批量添加到全局store
  if (uploadFiles.length > 0) {
    uploadQueueStore.addFiles(uploadFiles)
  }
  
  // 显示队列
  showQueue.value = true
  
  // 清空输入框，以便下次选择相同文件时也能触发change事件
  input.value = ''
  
  // 通知队列变化
  emit('queue-change', fileQueue.value)
  
  // 根据用户设置，判断是否自动上传
  if (userStore.profile?.options?.auto_upload_after_select) {
    // 立即上传所有图片
    nextTick(() => {
      uploadAll()
    })
  }
}

// 创建缩略图 - 修复预览问题
function createThumbnail(file: UploadFile) {
  const img = new Image()
  img.onload = () => {
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')
    
    const MAX_WIDTH = 160
    const MAX_HEIGHT = 160
    let width = img.width
    let height = img.height
    
    if (width > height) {
      if (width > MAX_WIDTH) {
        height *= MAX_WIDTH / width
        width = MAX_WIDTH
      }
    } else {
      if (height > MAX_HEIGHT) {
        width *= MAX_HEIGHT / height
        height = MAX_HEIGHT
      }
    }
    
    canvas.width = width
    canvas.height = height
    
    if (ctx) {
      ctx.drawImage(img, 0, 0, width, height)
      file.thumbnail = canvas.toDataURL('image/jpeg')
      
      // 使用专门的方法更新缩略图，确保视图更新
      uploadQueueStore.updateFileThumbnail(file.id, file.thumbnail)
    }
  }
  
  img.onerror = () => {
    // 缩略图生成失败，不打印错误信息
  }
  
  img.src = file.objectUrl
}

// 格式化文件大小
function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 B'
  
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// 打开文件选择器
function selectFiles() {
  fileInputRef.value?.click()
}

// 删除文件
function removeFile(index: number) {
  const file = fileQueue.value[index]
  
  // 如果正在上传，先取消上传
  if (file.status === 'uploading') {
    cancelUpload(index)
  }
  
  // 释放对象URL
  URL.revokeObjectURL(file.objectUrl)
  
  // 从store中移除
  uploadQueueStore.removeFile(file.id)
  
  // 通知队列变化
  emit('queue-change', fileQueue.value)
}

// 删除所有文件
function removeAll() {
  // 复制队列数组，以便能安全地遍历并删除
  const filesToRemove = [...fileQueue.value]
  
  // 处理每个文件
  filesToRemove.forEach((file) => {
    // 对于正在上传的文件，先取消上传
    if (file.status === 'uploading') {
      if (uploadCancelTokens.value[file.id]) {
        uploadCancelTokens.value[file.id].cancel(i18n.t('User cancelled upload'))
        delete uploadCancelTokens.value[file.id]
        
        // 减少当前上传计数
        currentUploads.value--
      }
    }
    
    // 释放对象URL
    URL.revokeObjectURL(file.objectUrl)
    
    // 从队列中移除文件
    uploadQueueStore.removeFile(file.id)
  })
  
  // 确保队列被清空
  if (fileQueue.value.length > 0) {
    uploadQueueStore.clearQueue()
  }
  
  // 通知队列变化
  emit('queue-change', fileQueue.value)
}

// 编辑图片
function editImage(index: number) {
  currentEditingIndex.value = index
  currentEditingFile.value = { ...fileQueue.value[index] }
  showEditor.value = true
  
  // 确保默认选择"自由"宽高比
  selectedAspectRatio.value = 'free'
  
  // 打开编辑器后，等待DOM更新完成再应用滤镜
  nextTick(() => {
    // 确保滤镜初始值被应用
    updatePreviewFilter()
  })
}

// 处理裁剪变化
function handleCropChange({ coordinates, canvas }: { coordinates: any; canvas: HTMLCanvasElement }) {
  cropResult.value = {
    coordinates,
    canvas
  }
}

// 取消编辑
function cancelEdit() {
  showEditor.value = false
  currentEditingIndex.value = null
  currentEditingFile.value = null
  cropResult.value = null
  resetFilters()
}

// 更新滤镜预览
function updatePreviewFilter() {
  // 获取滤镜值
  const filterValue = `brightness(${imageFilters.value.brightness}%) contrast(${imageFilters.value.contrast}%) saturate(${imageFilters.value.saturate}%)`
  
  // 只选择图片元素，排除背景元素
  const selectors = [
    '.vue-advanced-cropper__image',
    '.vue-preview-image',
    '.vue-rectangle-stencil__preview-image',
    '.vue-circle-stencil__preview-image',
    '.vue-advanced-cropper__image-wrapper img'
  ]
  
  // 为所有找到的元素应用滤镜
  selectors.forEach(selector => {
    const elements = document.querySelectorAll(selector)
    elements.forEach((el: Element) => {
      try {
        (el as HTMLElement).style.filter = filterValue
      } catch (e) {
        // 应用滤镜失败
      }
    })
  })
  
  // 确保编辑区域内的图像也应用滤镜
  nextTick(() => {
    try {
      // 更新cropper组件的图像样式
      if (cropperRef.value && cropperRef.value.$el) {
        const cropperImages = cropperRef.value.$el.querySelectorAll('img')
        cropperImages.forEach((img: HTMLElement) => {
          img.style.filter = filterValue
        })
      }
    } catch (e) {
      // 应用滤镜失败
    }
  })
}

// 重置滤镜
function resetFilters() {
  imageFilters.value = {
    brightness: 100,
    contrast: 100,
    saturate: 100,
    rotate: 0,
    flipH: 1,
    flipV: 1
  }
  
  // 重置后更新预览
  updatePreviewFilter()
}

// 确认编辑
function confirmEdit() {
  if (!cropResult.value || currentEditingIndex.value === null || !currentEditingFile.value) {
    showEditor.value = false
    return
  }
  
  // 获取裁剪后的canvas
  const canvas = cropResult.value.canvas
  
  // 创建一个新的canvas来应用滤镜
  const filteredCanvas = document.createElement('canvas')
  filteredCanvas.width = canvas.width
  filteredCanvas.height = canvas.height
  const ctx = filteredCanvas.getContext('2d')
  
  if (!ctx) {
    showEditor.value = false
    return
  }
  
  // 应用滤镜
  ctx.filter = `brightness(${imageFilters.value.brightness}%) contrast(${imageFilters.value.contrast}%) saturate(${imageFilters.value.saturate}%)`
  
  // 将裁剪后的图像绘制到应用滤镜的canvas上
  ctx.drawImage(canvas, 0, 0)
  
  // 转换为Blob
  filteredCanvas.toBlob((blob: Blob | null) => {
    if (!blob || currentEditingIndex.value === null) return
    
    // 保留原始文件名
    const originalFileName = currentEditingFile.value?.file.name || ''
    const fileExt = originalFileName.split('.').pop() || 'jpg'
    const newFile = new File([blob], originalFileName, { type: `image/${fileExt}` })
    
    // 更新队列中的文件
    const index = currentEditingIndex.value
    const oldFile = fileQueue.value[index]
    
    // 释放旧的对象URL
    URL.revokeObjectURL(oldFile.objectUrl)
    
    // 创建新的对象URL
    const objectUrl = URL.createObjectURL(newFile)
    
    // 创建更新后的文件对象
    const updatedFile: UploadFile = {
      ...oldFile,
      file: newFile,
      objectUrl: objectUrl,
      thumbnail: null, // 重新生成缩略图
      status: 'pending',
      progress: 0,
      error: null,
      response: null
    }
    
    // 使用更新后的文件替换原来的文件
    fileQueue.value[index] = updatedFile
    
    // 生成新缩略图
    createThumbnail(updatedFile)
    
    // 关闭编辑器
    showEditor.value = false
    currentEditingIndex.value = null
    currentEditingFile.value = null
    cropResult.value = null
    
    // 重置滤镜
    resetFilters()
    
    // 通知队列变化
    emit('queue-change', fileQueue.value)
  }, 'image/jpeg', 0.95)
}

// 上传单个文件，完全重写逻辑，确保单独上传不触发队列处理
function uploadFile(index: number, continueQueue: boolean = false) {
  const file = fileQueue.value[index]
  
  // 如果已经上传成功或正在上传，不再重复上传
  if (file.status === 'success' || file.status === 'uploading') return
  
  // 检查并行上传限制
  if (currentUploads.value >= props.concurrency) {
    message.info(i18n.t('Current uploads: {count}, please wait', {count: props.concurrency}))
    return
  }
  
  // 注意：不在这里增加总数统计，而是在uploadAll中一次性设置

  // 创建FormData
  const formData = new FormData()
  formData.append('file', file.file)
  
  // 添加合并后的表单数据
  const mergedData = prepareFormData(file)
  for (const key in mergedData) {
    if (Array.isArray(mergedData[key])) {
      for (const item of mergedData[key]) {
        // 检查key是否已经包含[]，如果包含则不再添加
        const appendKey = key.endsWith('[]') ? key : `${key}[]`
        formData.append(appendKey, item)
      }
    } else {
      // 特殊处理布尔值
      if (typeof mergedData[key] === 'boolean') {
        formData.append(key, mergedData[key] ? '1' : '0')
      } else {
        formData.append(key, mergedData[key])
      }
    }
  }
  
  // 创建取消令牌
  const cancelToken = axios.CancelToken.source()
  uploadCancelTokens.value[file.id] = cancelToken
  
  // 更新文件状态到store
  uploadQueueStore.updateFileStatus(file.id, 'uploading', 0)
  
  // 增加当前上传计数
  currentUploads.value++
  
  // 执行上传
  axios.post(props.endpoint, formData, {
    headers: {
      ...props.headers,
      'Content-Type': 'multipart/form-data'
    },
    cancelToken: cancelToken.token,
    onUploadProgress: (progressEvent) => {
      if (progressEvent.total) {
        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        uploadQueueStore.updateFileStatus(file.id, 'uploading', percentCompleted)
        emit('upload-progress', file, percentCompleted)
      }
    }
  })
  .then(response => {
    const responseData = response.data

    // 检查业务错误 (status: "error")
    if (responseData && responseData.status === 'error') {
      // 更新文件状态为错误
      uploadQueueStore.updateFileStatus(file.id, 'error', 0, responseData.message || i18n.t('Upload failed'))
      
      // 移除取消令牌
      delete uploadCancelTokens.value[file.id]
      
      // 减少当前上传计数
      currentUploads.value--

      // 增加失败统计
      if (continueQueue) {
        uploadStats.value.failed++
      }
      
      // 触发错误事件
      emit('upload-error', file, new Error(responseData.message || i18n.t('Upload failed')))
      message.error(i18n.t('File {filename} upload failed: {reason}', {filename: file.file.name, reason: responseData.message || i18n.t('Upload failed')}))
      
      // 只有在批量上传模式下才处理队列
      if (continueQueue && props.continueQueue) {
        // 尝试从队列中上传下一个文件
        processQueue()
        
        // 检查是否所有文件都处理完了
        checkUploadCompletion()
      }
      
      return
    }
    
    // 更新文件状态
    uploadQueueStore.updateFileStatus(file.id, 'success', 100, null, responseData)
    
    // 移除取消令牌
    delete uploadCancelTokens.value[file.id]
    
    // 减少当前上传计数
    currentUploads.value--

    // 增加成功统计
    if (continueQueue) {
      uploadStats.value.success++
    }
    
    // 触发成功事件
    emit('upload-success', file, responseData)
    
    // 只有在批量上传模式下才处理队列
    if (continueQueue && props.continueQueue) {
      // 尝试从队列中上传下一个文件
      processQueue()
      
      // 检查是否所有文件都处理完了
      checkUploadCompletion()
    }
  })
  .catch(error => {
    // 如果是取消的请求，不做错误处理
    if (axios.isCancel(error)) {
      return
    }
    
    // 检查是否是验证错误 (422状态码)
    if (error.response && error.response.status === 422) {
      const responseData = error.response.data
      let errorMessage = i18n.t('Form validation failed')
      
      if (responseData && responseData.message) {
        errorMessage = responseData.message
      } else if (responseData && responseData.data && responseData.data.errors) {
        // 提取第一个错误信息
        const firstErrorKey = Object.keys(responseData.data.errors)[0]
        if (firstErrorKey && responseData.data.errors[firstErrorKey].length > 0) {
          errorMessage = responseData.data.errors[firstErrorKey][0]
        }
      }
      
      // 更新文件状态为错误，不会重试
      uploadQueueStore.updateFileStatus(file.id, 'error', 0, errorMessage)

      // 增加失败统计
      if (continueQueue) {
        uploadStats.value.failed++
      }
      
      // 显示错误消息
      message.error(i18n.t('File {filename} upload failed: {reason}', {filename: file.file.name, reason: errorMessage}))
    } else {
      // 处理其他错误
      uploadQueueStore.updateFileStatus(file.id, 'error', 0, error.message || i18n.t('Upload failed'))

      // 增加失败统计
      if (continueQueue) {
        uploadStats.value.failed++
      }
      
      message.error(i18n.t('File {filename} upload failed: {reason}', {filename: file.file.name, reason: (error.message || i18n.t('Upload failed'))}))
    }
    
    // 移除取消令牌
    delete uploadCancelTokens.value[file.id]
    
    // 减少当前上传计数
    currentUploads.value--
    
    // 只有在批量上传模式下才处理队列
    if (continueQueue && props.continueQueue) {
      // 尝试从队列中上传下一个文件
      processQueue()
      
      // 检查是否所有文件都处理完了
      checkUploadCompletion()
    }
    
    // 触发错误事件
    emit('upload-error', file, error)
  })
}

// 添加检查上传完成的函数
function checkUploadCompletion() {
  // 检查是否所有预定的上传都已完成
  if (uploadStats.value.total > 0 && 
      uploadStats.value.success + uploadStats.value.failed >= uploadStats.value.total &&
      currentUploads.value === 0) {
    
    // 有成功的文件才显示总结
    if (uploadStats.value.success > 0) {
      if (uploadStats.value.failed > 0) {
        // 部分成功部分失败
        message.success(i18n.t('Upload completed: {success} succeeded, {failed} failed', { 
          success: uploadStats.value.success, 
          failed: uploadStats.value.failed 
        }))
      } else {
        // 全部成功
        message.success(i18n.t('All {count} files uploaded successfully', { 
          count: uploadStats.value.success 
        }))
      }
    }
    
    // 重置统计
    setTimeout(() => {
      uploadStats.value = {
        total: 0,
        success: 0,
        failed: 0
      }
    }, 500)
  }
}

// 取消上传
function cancelUpload(index: number) {
  const file = fileQueue.value[index]
  
  if (file.status !== 'uploading') return
  
  // 调用取消令牌
  if (uploadCancelTokens.value[file.id]) {
    uploadCancelTokens.value[file.id].cancel(i18n.t('User cancelled upload'))
    delete uploadCancelTokens.value[file.id]
    
    // 减少当前上传计数
    currentUploads.value--
  }
  
  // 更新文件状态 - 将状态设置为cancelled而不是pending
  uploadQueueStore.updateFileStatus(file.id, 'pending', 0, i18n.t('Upload cancelled'))
  
  // 不再自动继续处理队列，让用户手动点击上传按钮
  // processQueue()
}

// 取消所有上传
function cancelAllUploads() {
  fileQueue.value.forEach((file, index) => {
    if (file.status === 'uploading') {
      cancelUpload(index)
    }
  })
}

// 上传所有文件
function uploadAll() {
  // 检查是否有等待上传的文件 - 只上传pending状态的文件，不再重试error状态的文件
  const pendingFiles = fileQueue.value.filter(file => file.status === 'pending')
  
  if (pendingFiles.length === 0) {
    message.info(i18n.t('No files to upload'))
    return
  }
  
  // 重置上传统计
  uploadStats.value = {
    total: pendingFiles.length,
    success: 0,
    failed: 0
  }
  
  // 先上传最大并发数的文件，启用继续队列处理
  const initialUploads = Math.min(props.concurrency, pendingFiles.length)
  
  for (let i = 0; i < initialUploads; i++) {
    const index = fileQueue.value.findIndex(file => file.status === 'pending')
    if (index !== -1) {
      // 上传时启用继续队列处理，这里必须设为true才能进行批量上传
      uploadFile(index, true)
    }
  }
}

// 暴露方法给父组件
defineExpose({
  selectFiles,
  uploadAll,
  cancelAllUploads,
  removeAll,
  getFiles: () => fileQueue.value,
  showUploadQueue: () => { showQueue.value = true },
  getUploadingCount: () => uploadingCount.value,
  isUploading: () => isUploading.value
})

// 组件销毁前清理
onBeforeUnmount(() => {
  // 取消所有上传，但不清空队列
  cancelAllUploads()
})

// 图片编辑 - 旋转
function rotateImage(degrees: number) {
  if (!cropperRef.value) return
  
  // 使用cropperRef的rotate方法旋转图片
  cropperRef.value.rotate(degrees)
}

// 图片编辑 - 翻转
function flipImage(direction: 'horizontal' | 'vertical') {
  if (!cropperRef.value) return
  
  // 使用cropperRef的flip方法翻转图片
  if (direction === 'horizontal') {
    cropperRef.value.flip(true, false)
  } else {
    cropperRef.value.flip(false, true)
  }
}

// 重置旋转和翻转
function resetRotation() {
  if (!cropperRef.value) return
  
  // 重置旋转和翻转
  cropperRef.value.reset()
}

// 放大
function zoomIn() {
  if (!cropperRef.value) return
  
  try {
    // 放大图片，正确使用zoom方法，第一个参数是缩放因子
    const factor = 1.1; // 放大10%
    cropperRef.value.zoom(factor);
  } catch (e) {
    // 放大失败
  }
}

// 缩小
function zoomOut() {
  if (!cropperRef.value) return
  
  try {
    // 缩小图片，使用小于1的缩放因子
    const factor = 0.9; // 缩小10%
    cropperRef.value.zoom(factor);
  } catch (e) {
    // 缩小失败
  }
}

// 添加重试上传方法
function retryUpload(index: number) {
  const file = fileQueue.value[index]
  
  // 将文件状态重置为待上传
  uploadQueueStore.updateFileStatus(file.id, 'pending', 0, null)
  
  // 开始上传 - 只上传当前文件，不触发队列处理
  uploadSingleFile(index)
}

// 确认全局表单设置
function confirmGlobalForm() {
  // 无论如何都关闭模态框，避免用户卡在设置界面
  globalFormVisible.value = false
  
  // 检查存储位置是否已选择
  if (!globalForm.value.storage_id) {
    message.error(i18n.t('Please select storage location'))
    return
  }
  
  message.success(i18n.t('Global settings saved'))
}

// 处理上传队列 - 专门修复单独上传问题
function processQueue() {
  // 如果当前上传数已达到并发限制，不再启动新的上传
  if (currentUploads.value >= props.concurrency) return
  
  // 查找下一个等待上传的文件 - 只查找pending状态的文件
  const index = fileQueue.value.findIndex(file => file.status === 'pending')
  
  if (index !== -1) {
    // 延迟执行以避免状态更新冲突，上传时继续处理队列
    setTimeout(() => {
      uploadFile(index, true)
    }, 100)
  }
}

// 添加专门处理单个文件上传的方法
function uploadSingleFile(index: number) {
  // 调用uploadFile但设置continueQueue为false，这样就不会进行队列处理和统计
  uploadFile(index, false)
}

// 在脚本部分添加新方法
function openFloatingQueue(event: Event) {
  // 阻止事件冒泡
  event.stopPropagation()
  event.preventDefault()
  
  // 打开队列
  showQueue.value = true
}

// 复制图片URL
function copyImageUrl(file: UploadFile) {
  if (!file.response || !file.response.data || !file.response.data.public_url) {
    message.error(i18n.t('Image URL not available'))
    return
  }
  
  copyText(file.response.data.public_url)
}

// 复制所有已上传图片的链接
function copyAllImageUrls() {
  const uploadedFiles = fileQueue.value.filter(file => 
    file.status === 'success' && 
    file.response && 
    file.response.data && 
    file.response.data.public_url
  )
  
  if (uploadedFiles.length === 0) {
    message.error(i18n.t('No uploaded images found'))
    return
  }
  
  const urls = uploadedFiles.map(file => file.response.data.public_url)
  const urlsText = urls.join('\n')
  
  strUtils.copyText(urlsText)
    .then(() => {
      message.success(i18n.t('Copied {count} image links', { count: uploadedFiles.length }))
    })
    .catch(() => {
      message.error(i18n.t('Failed to copy'))
    })
}

// 显示嵌入代码
function showEmbedCodes(file: UploadFile) {
  if (!file.response || !file.response.data || !file.response.data.public_url) {
    message.error(i18n.t('Image URL not available'))
    return
  }
  
  currentEmbedFile.value = file
  embedCodesVisible.value = true
}

// 获取不同格式的嵌入代码
function getEmbedCode(type: string): string {
  if (!currentEmbedFile.value || !currentEmbedFile.value.response || !currentEmbedFile.value.response.data) {
    return ''
  }
  
  const url = currentEmbedFile.value.response.data.public_url
  const name = currentEmbedFile.value.response.data.name || currentEmbedFile.value.file.name
  
  switch (type) {
    case 'html':
      return `<img src="${url}" alt="${name}" />`
    case 'markdown':
      return `![${name}](${url})`
    case 'markdown_with_link':
      return `[![${name}](${url})](${url})`
    case 'bbcode':
      return `[img]${url}[/img]`
    case 'url':
    default:
      return url
  }
}

function copyText(text: string) {
  strUtils.copyText(text)
    .then(() => {
      message.success(i18n.t('Copied to clipboard'))
    })
    .catch(() => {
      message.error(i18n.t('Failed to copy'))
    })
}

// 显示错误详情
function showErrorDetails(errorMessage: string) {
  currentErrorMessage.value = errorMessage
  errorModalVisible.value = true
}

// 添加粘贴上传相关方法
function handlePaste(e: Event) {
  const clipboardEvent = e as ClipboardEvent;
  // 如果不允许游客上传且未登录，则不处理粘贴事件
  if (!configStore.configs?.app.guest_upload && !userStore.isLoggedIn) {
    message.error(i18n.t('Please log in first before uploading'))
    return
  }

  // 获取剪贴板数据
  const items = clipboardEvent.clipboardData?.items
  if (!items) return

  let hasImage = false
  for (let i = 0; i < items.length; i++) {
    const item = items[i]
    
    // 检查是否是图片类型
    if (item.type.indexOf('image') !== -1) {
      hasImage = true
      const file = item.getAsFile()
      if (file) {
        // 创建文件列表并调用处理方法
        const fileList = new DataTransfer()
        fileList.items.add(file)
        
        const input = document.createElement('input')
        input.type = 'file'
        input.files = fileList.files
        
        // 触发处理文件的方法
        handleFileSelect({ target: input } as unknown as Event)
        
        // 阻止默认粘贴行为
        clipboardEvent.preventDefault()
        break
      }
    }
  }
  
  // 如果有图片，则打开上传队列窗口
  if (hasImage) {
    showQueue.value = true
  }
}

// 拖拽上传
function handleDragDrop(files: FileList) {
  // 如果不允许游客上传且未登录，则不处理拖拽事件
  if (!configStore.configs?.app.guest_upload && !userStore.isLoggedIn) {
    message.error(i18n.t('Please login before uploading images'))
    return
  }

  if (!files || files.length === 0) return

  // 创建文件列表并调用处理方法
  const fileList = new DataTransfer()
  for (let i = 0; i < files.length; i++) {
    fileList.items.add(files[i])
  }
  
  const input = document.createElement('input')
  input.type = 'file'
  input.files = fileList.files
  
  // 触发处理文件的方法
  handleFileSelect({ target: input } as unknown as Event)
  
  // 打开上传队列窗口
  showQueue.value = true
}

// 远程下载
function startRemoteDownload() {
  if (!remoteUrls.value.trim()) {
    message.error(i18n.t('Please enter image URLs'))
    return
  }

  const urls = remoteUrls.value
    .split('\n')
    .map(url => url.trim())
    .filter(url => url.length > 0)

  if (urls.length === 0) {
    message.error(i18n.t('No valid URLs found'))
    return
  }

  isDownloading.value = true
  downloadStatus.value = urls.map(url => ({ url, status: 'pending' }))

  // 并发下载图片
  const downloadPromises = urls.map((url, index) => downloadRemoteImage(url, index))
  
  Promise.allSettled(downloadPromises).finally(() => {
    isDownloading.value = false
    
    // 延迟清空状态和输入框
    setTimeout(() => {
      downloadStatus.value = []
      remoteUrls.value = ''
      showRemoteDownload.value = false
    }, 2000)
  })
}

async function downloadRemoteImage(url: string, index: number) {
  try {
    // 更新状态为下载中
    downloadStatus.value[index].status = 'downloading'
    
    // 创建取消令牌
    const cancelToken = axios.CancelToken.source()
    downloadCancelTokens.value[url] = cancelToken
    
    // 验证URL格式
    const urlPattern = /^https?:\/\/.+\.(jpg|jpeg|png|gif|bmp|webp|tif)(\?.*)?$/i
    if (!urlPattern.test(url)) {
      throw new Error(i18n.t('Invalid image URL format'))
    }
    
    // 下载图片
    const response = await axios.get(url, {
      responseType: 'blob',
      timeout: 30000, // 30秒超时
      cancelToken: cancelToken.token,
      headers: {
        'Accept': 'image/*',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
    })
    
    // 检查响应类型
    if (!response.data.type.startsWith('image/')) {
      throw new Error(i18n.t('Downloaded file is not a valid image'))
    }
    
    // 从URL中提取文件名
    const urlParts = new URL(url)
    let filename = urlParts.pathname.split('/').pop() || `remote_image_${Date.now()}.jpg`
    
    // 确保文件名有扩展名
    if (!filename.includes('.')) {
      const mimeType = response.data.type
      const extension = mimeType.split('/')[1] || 'jpg'
      filename += `.${extension}`
    }
    
    // 创建File对象
    const file = new File([response.data], filename, { type: response.data.type })
    
    // 验证文件大小
    if (props.maxFileSize > 0 && file.size > props.maxFileSize) {
      throw new Error(i18n.t('File size exceeds the maximum limit of {size}', { size: formatFileSize(props.maxFileSize) }))
    }
    
    if (props.minFileSize > 0 && file.size < props.minFileSize) {
      throw new Error(i18n.t('File size is less than the minimum limit of {size}', { size: formatFileSize(props.minFileSize) }))
    }
    
    // 创建上传文件对象
    const objectUrl = URL.createObjectURL(file)
    const uploadFile: UploadFile = {
      id: uuidv4(),
      file: file,
      objectUrl: objectUrl,
      thumbnail: null,
      status: 'pending',
      progress: 0,
      error: null,
      response: null
    }
    
    // 生成缩略图
    createThumbnail(uploadFile)
    
    // 添加到上传队列
    uploadQueueStore.addFiles([uploadFile])
    
    // 更新下载状态为成功
    downloadStatus.value[index].status = 'success'
    
    // 通知队列变化
    emit('queue-change', fileQueue.value)
    
    // 清理取消令牌
    delete downloadCancelTokens.value[url]
    
  } catch (error: any) {
    // 如果是取消的请求，不做错误处理
    if (axios.isCancel(error)) {
      return
    }
    
    console.error('Remote download failed:', error)
    
    // 更新下载状态为错误
    downloadStatus.value[index].status = 'error'
    downloadStatus.value[index].error = error.message || i18n.t('Download failed')
    
    // 清理取消令牌
    delete downloadCancelTokens.value[url]
  }
}

// 获取状态文本
function getStatusText(status: string): string {
  switch (status) {
    case 'pending':
      return i18n.t('Waiting')
    case 'downloading':
      return i18n.t('Downloading')
    case 'success':
      return i18n.t('Success')
    case 'error':
      return i18n.t('Failed')
    default:
      return ''
  }
}

// 获取状态颜色
function getStatusColor(status: string): string {
  switch (status) {
    case 'pending':
      return 'text-gray-500'
    case 'downloading':
      return 'text-blue-500'
    case 'success':
      return 'text-green-500'
    case 'error':
      return 'text-red-500'
    default:
      return 'text-gray-500'
  }
}


</script>

<style scoped>
.transform-container {
  transform-origin: center center;
  transition: transform 0.3s ease;
}

.editor-wrapper {
  @apply w-full;
}

.cropper-wrapper {
  @apply w-full h-96 sm:h-[400px] md:h-[450px] mb-4 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden relative;
}

.cropper {
  @apply h-full;
}

.editor-section {
  @apply mt-4 mb-4;
}

.editor-buttons {
  @apply flex flex-wrap gap-2;
}

.section-divider {
  @apply my-4 h-1 bg-gray-100 dark:bg-gray-700 rounded-full;
}

.aspect-ratio-group {
  @apply flex flex-wrap gap-2 justify-center;
}

.aspect-ratio-btn {
  @apply min-w-[70px] h-10 flex items-center justify-center;
}

.aspect-ratio-icon {
  @apply w-5 h-5;
}

.files-container {
  @apply overflow-hidden relative;
}

.file-item {
  @apply mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800;
}

.floating-queue-button {
  position: fixed !important;
  right: 20px !important;
  bottom: 100px !important;
  top: auto !important;
  transform: none !important;
  z-index: 99999 !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  cursor: pointer !important;
  background-color: rgba(255, 255, 255, 0.8) !important;
  padding: 10px !important;
  border-radius: 8px !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
  pointer-events: all !important;
  transition: all 0.3s ease !important;
}

.upload-indicator {
  @apply mt-2 bg-black/60 dark:bg-white/20 text-white px-2 py-1 rounded-full text-xs whitespace-nowrap w-24 text-center;
}

.upload-indicator-text {
  @apply mb-1;
}

@media (max-width: 768px) {
  .cropper-wrapper {
    @apply max-h-[46vh];
  }
  
  .editor-buttons {
    @apply mb-2;
  }
  
  .editor-buttons :deep(.NButton) {
    @apply mb-2 px-2 text-xs;
  }
  
  .aspect-ratio-group {
    @apply gap-1.5;
  }
  
  .aspect-ratio-btn {
    @apply min-w-[60px] h-9 text-xs;
  }
  
  .aspect-ratio-icon {
    @apply w-4 h-4 mr-0.5;
  }
  
  .editor-section, .ratio-section, .filter-section {
    @apply py-2;
  }
  
  .filter-section .inline-block {
    @apply text-sm;
  }
  
  .floating-queue-button {
    @apply right-0 bottom-24 top-auto;
  }
}

/* 为队列drawer增加层级 */
.upload-queue-drawer {
  z-index: 100000 !important;
}

/* 确保按钮内部元素也能接收点击事件 */
.floating-queue-button * {
  pointer-events: all !important;
}

/* 黑暗模式适配 */
.dark .floating-queue-button {
  background-color: rgba(50, 50, 50, 0.8) !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4) !important;
}

/* 悬浮效果 */
.floating-queue-button:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3) !important;
}

/* 为抽屉内的滚动区域增加自定义样式 */
:deep(.n-drawer-content-wrapper) {
  overflow: hidden !important;
}

:deep(.n-drawer-body.NScrollbar>.NScrollbar-container) {
  overflow: hidden !important;
}

/* 确保文件列表容器内的滚动条正常工作 */
.files-container :deep(.NScrollbar) {
  height: 100% !important;
}

.files-container :deep(.NScrollbar-container) {
  height: 100% !important;
}

.files-container :deep(.NScrollbar-content) {
  min-height: 100% !important;
}

/* 添加错误提示 tooltip 相关样式 */
:deep(.tooltip-content) {
  white-space: pre-wrap;
  word-break: break-all;
  font-size: 12px;
  line-height: 1.5;
  text-align: left;
}
</style> 