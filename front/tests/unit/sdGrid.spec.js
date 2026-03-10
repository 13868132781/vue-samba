import { mount } from '@vue/test-utils';
import sdGrid from '@/coms/sdGrid/sdGrid.js';

describe('sdGrid 组件', () => {
  
  const mockColumns = [
    { key: 'id', title: 'ID', width: 80 },
    { key: 'name', title: '姓名', width: 150 },
    { key: 'email', title: '邮箱', width: 200 }
  ];
  
  const mockData = {
    list: [
      { id: 1, name: '用户 1', email: 'user1@example.com' },
      { id: 2, name: '用户 2', email: 'user2@example.com' },
      { id: 3, name: '用户 3', email: 'user3@example.com' }
    ],
    total: 3
  };
  
  test('渲染数据表格', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: mockData
      }
    });
    
    // 检查表头
    expect(wrapper.find('thead').exists()).toBe(true);
    expect(wrapper.findAll('th').length).toBe(3);
    
    // 检查数据行
    const rows = wrapper.findAll('tbody tr');
    expect(rows.length).toBe(3);
  });
  
  test('渲染列标题', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: mockData
      }
    });
    
    const headers = wrapper.findAll('th');
    expect(headers[0].text()).toBe('ID');
    expect(headers[1].text()).toBe('姓名');
    expect(headers[2].text()).toBe('邮箱');
  });
  
  test('显示数据总数', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: mockData
      }
    });
    
    // 应该显示总数信息
    expect(wrapper.text()).toContain('3');
  });
  
  test('空数据状态', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: { list: [], total: 0 }
      }
    });
    
    const rows = wrapper.findAll('tbody tr');
    expect(rows.length).toBe(0);
  });
  
  test('分页组件渲染', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: mockData,
        pagination: {
          page: 1,
          pagesize: 10,
          total: 100
        }
      }
    });
    
    expect(wrapper.find('.pagination').exists()).toBe(true);
  });
  
  test('排序功能', async () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [
          { key: 'name', title: '姓名', sortable: true }
        ],
        data: mockData
      }
    });
    
    const sortableHeader = wrapper.find('th.sortable');
    await sortableHeader.trigger('click');
    
    expect(wrapper.emitted().sort).toBeTruthy();
  });
  
  test('行点击事件', async () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: mockColumns,
        data: mockData
      }
    });
    
    const firstRow = wrapper.find('tbody tr');
    await firstRow.trigger('click');
    
    expect(wrapper.emitted().rowClick).toBeTruthy();
  });
  
  test('复选框选择', async () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [
          { key: 'checkbox', type: 'checkbox' },
          { key: 'name', title: '姓名' }
        ],
        data: mockData,
        checkbox: true
      }
    });
    
    const checkbox = wrapper.find('input[type="checkbox"]');
    await checkbox.trigger('change');
    
    expect(wrapper.emitted().selectionChange).toBeTruthy();
  });
  
  test('自定义单元格渲染', () => {
    const wrapper = mount(sdGrid, {
      props: {
        columns: [
          { 
            key: 'name', 
            title: '姓名',
            render: (h, row) => `<strong>${row.name}</strong>`
          }
        ],
        data: mockData
      }
    });
    
    const cell = wrapper.find('tbody td');
    expect(cell.html()).toContain('<strong>');
  });
});
