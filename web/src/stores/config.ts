import {ref} from "vue";
import {defineStore} from "pinia";
import type {GetConfigsResponse, GetGroupResponse, GetPagesResponse} from "@/api";

export const useConfigStore = defineStore('config', () => {
  const configs = ref<GetConfigsResponse['data']>()
  const group = ref<GetGroupResponse['data']>()
  const pages = ref<GetPagesResponse['data']['data']>()

  function setConfigs(data: GetConfigsResponse['data']) {
    configs.value = data
    // 配置更新后，重新加载自定义资源
    loadCustomAssets()
  }

  function setGroup(data: GetGroupResponse['data']) {
    group.value = data
  }

  function setPages(data: GetPagesResponse['data']['data']) {
    pages.value = data
  }

  // 国家下拉选项
  function getSelectOptionCountryCodes() {
    return configs.value?.app.countries.map(country => {
      return {label: `+${country.code} ${country.name}`, value: country.id}
    })
  }

  /**
   * 动态加载自定义CSS和JS
   */
  function loadCustomAssets() {
    if (!configs.value) return;

    // 加载自定义CSS
    if (configs.value.site.custom_css && configs.value.site.custom_css.trim()) {
      try {
        const styleId = 'custom-css';
        // 移除已存在的自定义样式
        const existingStyle = document.getElementById(styleId);
        if (existingStyle) {
          existingStyle.remove();
        }
        
        // 创建新的样式元素
        const styleElement = document.createElement('style');
        styleElement.id = styleId;
        styleElement.type = 'text/css';
        styleElement.textContent = configs.value.site.custom_css;
        document.head.appendChild(styleElement);
        
      } catch (error) {
        console.error('加载自定义CSS时出错:', error);
      }
    } else {
      // 如果没有自定义CSS，移除已存在的样式
      const existingStyle = document.getElementById('custom-css');
      if (existingStyle) {
        existingStyle.remove();
      }
    }

    // 加载自定义JS
    if (configs.value.site.custom_js && configs.value.site.custom_js.trim()) {
      try {
        const scriptId = 'custom-js';
        // 移除已存在的自定义脚本
        const existingScript = document.getElementById(scriptId);
        if (existingScript) {
          existingScript.remove();
        }
        
        // 创建新的脚本元素
        const scriptElement = document.createElement('script');
        scriptElement.id = scriptId;
        scriptElement.type = 'text/javascript';
        
        const wrappedJs = `
try {
  ${configs.value.site.custom_js}
} catch (error) {
  console.error('执行自定义JS时出错:', error);
}
        `;
        
        scriptElement.textContent = wrappedJs;
        document.body.appendChild(scriptElement);
        
      } catch (error) {
        console.error('加载自定义JS时出错:', error);
      }
    } else {
      // 如果没有自定义JS，移除已存在的脚本
      const existingScript = document.getElementById('custom-js');
      if (existingScript) {
        existingScript.remove();
      }
    }
  }

  return {
    configs,
    group,
    pages,
    setConfigs,
    setGroup,
    setPages,
    getSelectOptionCountryCodes,
    loadCustomAssets,
  }
})