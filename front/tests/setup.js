/**
 * Jest 测试设置文件
 * 在每个测试文件之前运行
 */

// 设置测试超时时间
jest.setTimeout(10000);

// 全局 Mock
global.console = {
  ...console,
  // 忽略测试中的 console.warn
  warn: jest.fn(),
  // 忽略测试中的 console.error
  error: jest.fn(),
};

// 模拟 Vue 组件
global.mockVueComponent = (name) => {
  return {
    template: `<div class="${name}-mock"></div>`,
    name: name
  };
};

// 模拟 API 请求
global.mockApiRequest = (url, response) => {
  global.fetch = jest.fn(() =>
    Promise.resolve({
      json: () => Promise.resolve(response),
      ok: true,
      status: 200
    })
  );
};

// 清理函数
afterEach(() => {
  jest.clearAllMocks();
  jest.restoreAllMocks();
});
