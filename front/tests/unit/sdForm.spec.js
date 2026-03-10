import { mount } from '@vue/test-utils';
import sdForm from '@/coms/sdForm.js';

describe('sdForm 组件', () => {
  
  test('渲染基本表单', () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text' },
          { name: 'password', label: '密码', type: 'password' }
        ]
      }
    });
    
    expect(wrapper.find('input[name="username"]').exists()).toBe(true);
    expect(wrapper.find('input[name="password"]').exists()).toBe(true);
  });
  
  test('渲染必填字段标记', () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text', required: true }
        ]
      }
    });
    
    // 应该显示必填标记
    const label = wrapper.find('label');
    expect(label.text()).toContain('*');
  });
  
  test('表单验证 - 必填字段', async () => {
    const onSubmit = jest.fn();
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text', required: true }
        ],
        onSubmit
      }
    });
    
    // 提交空表单
    await wrapper.find('form').trigger('submit.prevent');
    
    // 验证失败，不应调用 onSubmit
    expect(onSubmit).not.toHaveBeenCalled();
  });
  
  test('表单验证 - 邮箱格式', async () => {
    const onSubmit = jest.fn();
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'email', label: '邮箱', type: 'email', required: true }
        ],
        onSubmit
      }
    });
    
    // 输入无效邮箱
    await wrapper.find('input[name="email"]').setValue('invalid-email');
    await wrapper.find('form').trigger('submit.prevent');
    
    expect(onSubmit).not.toHaveBeenCalled();
  });
  
  test('表单提交成功', async () => {
    const onSubmit = jest.fn();
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text' },
          { name: 'email', label: '邮箱', type: 'email' }
        ],
        onSubmit
      }
    });
    
    // 填写表单
    await wrapper.find('input[name="username"]').setValue('testuser');
    await wrapper.find('input[name="email"]').setValue('test@example.com');
    
    // 提交
    await wrapper.find('form').trigger('submit.prevent');
    
    expect(onSubmit).toHaveBeenCalledWith({
      username: 'testuser',
      email: 'test@example.com'
    });
  });
  
  test('表单重置功能', async () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'username', label: '用户名', type: 'text', default: 'default_user' }
        ]
      }
    });
    
    // 修改值
    await wrapper.find('input[name="username"]').setValue('changed');
    expect(wrapper.find('input[name="username"]').element.value).toBe('changed');
    
    // TODO: 测试重置功能（如果组件支持）
  });
  
  test('动态字段渲染', async () => {
    const wrapper = mount(sdForm, {
      props: {
        fields: [
          { name: 'field1', type: 'text' },
          { name: 'field2', type: 'number' },
          { name: 'field3', type: 'select', options: [{value: 1, label: '选项 1'}] }
        ]
      }
    });
    
    expect(wrapper.findAll('input').length).toBeGreaterThanOrEqual(2);
  });
});
