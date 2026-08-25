import { defineAsyncComponent } from 'vue'
import type { AsyncComponentLoader, Component } from 'vue'

// 优先级枚举
export enum PreloadPriority {
  HIGH = 'high',
  MEDIUM = 'medium',
  LOW = 'low',
  AUTO = 'auto'
}

// 支持按需加载组件的通用工具函数
export const lazyLoadComponent = (loader: AsyncComponentLoader) => {
  // 不再使用defineAsyncComponent包装，直接返回loader
  // Vue Router会自动处理异步组件
  return loader
}

// 用于预加载多个组件的工具函数
export const preloadComponents = (componentLoaders: AsyncComponentLoader[]) => {
  return Promise.all(componentLoaders.map(loader => loader()))
}

// 预获取模块
export const prefetchModule = (moduleUrl: string, importance: PreloadPriority = PreloadPriority.AUTO) => {
  // 检查浏览器兼容性
  if (!document.createElement('link').relList.supports('prefetch')) {
    // 不支持prefetch，使用动态导入
    import(/* @vite-ignore */ moduleUrl).catch(() => {
      // 忽略错误，因为这只是预加载
    });
    return;
  }
  
  const link = document.createElement('link');
  link.rel = 'prefetch';
  link.href = moduleUrl;
  link.as = 'script';
  link.setAttribute('importance', importance);
  document.head.appendChild(link);
}

// 批量预加载模块
export const prefetchModules = (moduleUrls: string[]) => {
  // 使用 requestIdleCallback 在浏览器空闲时进行预加载
  if ('requestIdleCallback' in window) {
    moduleUrls.forEach(url => {
      // @ts-ignore
      window.requestIdleCallback(() => prefetchModule(url), { timeout: 2000 });
    });
  } else {
    // Fallback
    setTimeout(() => {
      moduleUrls.forEach(url => prefetchModule(url));
    }, 1000);
  }
}

// 检测网络连接
export const isSlowConnection = (): boolean => {
  const connection = (navigator as any).connection;
  
  if (!connection) return false;
  
  // 检测慢连接
  if (connection.saveData) return true;
  if (connection.effectiveType && ['slow-2g', '2g'].includes(connection.effectiveType)) return true;
  
  return false;
} 