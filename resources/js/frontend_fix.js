/**
 * 前端Redis缓存数据修复工具
 * 
 * 如何使用：
 * 1. 打开浏览器开发者工具 (F12)
 * 2. 切换到Console标签
 * 3. 复制整个脚本并粘贴
 * 4. 按Enter执行
 * 5. 等待修复完成后刷新页面
 */

(async function() {
  console.log('开始修复Redis缓存数据...');
  
  // 检查redisService是否存在
  if (!window.redisService) {
    console.error('找不到redisService，无法修复缓存');
    return;
  }
  
  // 检查homepageService是否存在
  if (!window.homepageService) {
    console.error('找不到homepageService，无法修复缓存');
    return;
  }
  
  try {
    // 清除相关首页缓存
    console.log('正在清除缓存数据...');
    const cacheKeys = [
      'homepage:formatted_settings',
      'homepage:formatted_featured_products',
      'homepage:formatted_new_products',
      'homepage:formatted_sale_products',
      'homepage:api_data',
      'homepage:templates:featured',
      'homepage:templates:newArrival',
      'homepage:templates:sale'
    ];
    
    for (const key of cacheKeys) {
      await window.redisService.delete(key);
      console.log(`已删除缓存: ${key}`);
    }
    
    // 强制刷新数据
    console.log('正在强制刷新数据...');
    const result = await window.homepageService.getHomePageData(true);
    
    console.log('数据刷新完成', result);
    console.log('已修复的数据:');
    console.log(`- 设置数据: ${result.settings ? '已加载' : '未加载'}`);
    console.log(`- 特色产品: ${result.featuredProducts.length} 个`);
    console.log(`- 新品产品: ${result.newProducts.length} 个`);
    console.log(`- 促销产品: ${result.saleProducts.length} 个`);
    console.log(`- 模板数据: ${
      result.templates.featured.length + 
      result.templates.newArrival.length + 
      result.templates.sale.length
    } 个`);
    
    console.log('修复完成！请刷新页面查看效果');
  } catch (error) {
    console.error('修复过程中发生错误:', error);
  }
})(); 