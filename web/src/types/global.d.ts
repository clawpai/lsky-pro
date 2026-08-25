// 将Window接口扩展以支持全局变量
declare global {
  interface Window {
    [key: string]: any;
  }
}

// 静态图片资源模块声明（登录图标等）
declare module '*.png' {
  const src: string;
  export default src;
}

export {}; 