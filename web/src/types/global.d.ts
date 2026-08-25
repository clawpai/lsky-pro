// 将Window接口扩展以支持全局变量
declare global {
  interface Window {
    [key: string]: any;
  }
}

export {}; 