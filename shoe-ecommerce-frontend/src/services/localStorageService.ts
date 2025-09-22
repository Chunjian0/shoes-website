/**
 * 本地存储服务 - 替代redisService
 * 提供简单的本地存储功能，支持过期时间和键前缀
 */

class LocalStorageService {
  private prefix: string;

  constructor(prefix = 'yce_cache:') {
    this.prefix = prefix;
    console.log('[LocalStorageService] 本地存储缓存服务已初始化');
    this.cleanExpiredItems();
  }

  /**
   * 获取缓存数据
   * @param key 缓存键
   * @returns 缓存值或null
   */
  async get<T>(key: string): Promise<T | null> {
    try {
      const prefixedKey = this.getKeyWithPrefix(key);
      const item = localStorage.getItem(prefixedKey);
      
      if (!item) {
        console.log(`[LocalStorageService] 缓存未找到: ${prefixedKey}`);
        return null;
      }
      
      const parsed = JSON.parse(item);
      if (parsed.expiry && parsed.expiry < new Date().getTime()) {
        // 缓存已过期，删除并返回null
        localStorage.removeItem(prefixedKey);
        console.log(`[LocalStorageService] 缓存已过期: ${prefixedKey}`);
        return null;
      }
      
      console.log(`[LocalStorageService] 缓存命中: ${prefixedKey}`);
      return parsed.value;
    } catch (error) {
      console.error(`[LocalStorageService] 获取缓存出错: ${key}`, error);
      return null;
    }
  }

  /**
   * 设置缓存数据
   * @param key 缓存键
   * @param value 缓存值
   * @param expiry 过期时间（毫秒）
   */
  async set(key: string, value: any, expiry?: number): Promise<boolean> {
    try {
      // 确保有足够空间
      this.ensureStorageCapacity();
      
      const prefixedKey = this.getKeyWithPrefix(key);
      const item = {
        value,
        expiry: expiry ? new Date().getTime() + expiry : null,
        createdAt: new Date().getTime()
      };
      
      localStorage.setItem(prefixedKey, JSON.stringify(item));
      console.log(`[LocalStorageService] 缓存已设置 (LocalStorage): ${prefixedKey}${expiry ? `, 过期时间: ${expiry}ms` : ''}`);
      return true;
    } catch (error) {
      if (error instanceof DOMException && error.name === 'QuotaExceededError') {
        console.warn(`[LocalStorageService] LocalStorage已满，无法设置缓存: ${key}`);
        this.evictLeastRecentlyUsed();
        return this.set(key, value, expiry);
      }
      console.error(`[LocalStorageService] 设置LocalStorage缓存出错: ${key}`, error);
      return false;
    }
  }

  /**
   * 获取或设置缓存（如果缓存不存在，则通过回调函数获取数据并设置缓存）
   * @param key 缓存键
   * @param fetcher 获取数据的回调函数
   * @param expiry 过期时间（毫秒）
   * @returns 缓存数据
   */
  async getOrSet<T>(key: string, fetcher: () => Promise<T>, expiry?: number): Promise<T> {
    const cachedData = await this.get<T>(key);
    
    if (cachedData !== null) {
      return cachedData;
    }
    
    try {
      console.log(`[LocalStorageService] 从源获取数据: ${key}`);
      const data = await fetcher();
      
      // 设置缓存
      await this.set(key, data, expiry);
      
      return data;
    } catch (error) {
      console.error(`[LocalStorageService] 获取数据出错: ${key}`, error);
      throw error;
    }
  }

  /**
   * 删除缓存
   * @param key 缓存键
   */
  async delete(key: string): Promise<boolean> {
    const prefixedKey = this.getKeyWithPrefix(key);
    localStorage.removeItem(prefixedKey);
    return true;
  }

  /**
   * 清空所有缓存
   */
  async clear(): Promise<boolean> {
    console.log(`[LocalStorageService] Clearing cache with prefix: ${this.prefix}`);
    
    let clearedCount = 0;
    const keysToRemove = [];
    
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && key.startsWith(this.prefix)) {
        keysToRemove.push(key);
      }
    }
    
    keysToRemove.forEach(key => {
      localStorage.removeItem(key);
      clearedCount++;
    });
    
    console.log(`[LocalStorageService] Cleared ${clearedCount} items.`);
    return true;
  }

  /**
   * 获取带前缀的键
   * @param key 原键
   * @returns 带前缀的键
   */
  private getKeyWithPrefix(key: string): string {
    return `${this.prefix}${key}`;
  }

  /**
   * 解析缓存项
   * @param key 缓存键
   * @returns 解析后的缓存项或null
   */
  private parseStorageItem(prefixedKey: string): { value: any; expiry: number | null; createdAt: number } | null {
    try {
      const item = localStorage.getItem(prefixedKey);
      if (!item) return null;
      
      return JSON.parse(item);
    } catch (error) {
      console.error(`[LocalStorageService] 解析LocalStorage缓存出错 (key: ${prefixedKey}):`, error);
      return null;
    }
  }

  /**
   * 根据模式删除缓存
   * @param pattern 模式（支持*作为通配符）
   */
  async deletePattern(pattern: string): Promise<number> {
    console.log(`[LocalStorageService] Deleting pattern: ${pattern} with prefix: ${this.prefix}`);
    
    // 将模式转换为正则表达式
    const regexPattern = new RegExp('^' + this.prefix + pattern.replace(/\*/g, '.*') + '$');
    
    let deletedCount = 0;
    const keysToRemove = [];
    
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && regexPattern.test(key)) {
        keysToRemove.push(key);
      }
    }
    
    keysToRemove.forEach(key => {
      localStorage.removeItem(key);
      deletedCount++;
    });
    
    console.log(`[LocalStorageService] Deleted ${deletedCount} items matching pattern.`);
    return deletedCount;
  }

  /**
   * 更新缓存
   * @param key 缓存键
   * @param value 新值
   * @param expiry 新的过期时间（毫秒）
   */
  async update(key: string, value: any, expiry?: number): Promise<boolean> {
    try {
      const prefixedKey = this.getKeyWithPrefix(key);
      const existingItem = this.parseStorageItem(prefixedKey);
      
      if (!existingItem) return false;
      
      const newItem = {
        value,
        expiry: expiry ? new Date().getTime() + expiry : existingItem.expiry,
        createdAt: existingItem.createdAt
      };
      
      localStorage.setItem(prefixedKey, JSON.stringify(newItem));
      return true;
    } catch (error) {
      console.error(`[LocalStorageService] 更新LocalStorage缓存过期时间出错 (key: ${key}):`, error);
      return false;
    }
  }

  /**
   * 清理过期的缓存项
   * @returns 清理的项数
   */
  private cleanExpiredItems(): number {
    const now = new Date().getTime();
    let cleanedCount = 0;
    
    for (let i = 0; i < localStorage.length; i++) {
      const storageKey = localStorage.key(i);
      if (!storageKey || !storageKey.startsWith(this.prefix)) continue;
      
      try {
        const item = localStorage.getItem(storageKey);
        if (!item) continue;
        
        const parsed = JSON.parse(item);
        if (parsed.expiry && parsed.expiry < now) {
          localStorage.removeItem(storageKey);
          cleanedCount++;
          i--; // 因为删除了项目，索引需要减一
        }
      } catch (error) {
        console.error(`[LocalStorageService] 清理时解析LocalStorage缓存出错 (key: ${storageKey}):`, error);
      }
    }
    
    console.log(`[LocalStorageService] 已清理 ${cleanedCount} 个过期缓存项，当前缓存大小: ${localStorage.length}`);
    return cleanedCount;
  }

  /**
   * 确保存储空间足够
   */
  private ensureStorageCapacity(): void {
    if (localStorage.length > 100) {
      // 如果缓存项超过100，先清理过期项
      const cleanedCount = this.cleanExpiredItems();
      
      // 如果清理后仍然超过100，删除最旧的项
      if (localStorage.length - cleanedCount > 100) {
        this.evictLeastRecentlyUsed();
      }
    }
  }

  /**
   * 淘汰最久未使用的缓存项
   */
  private evictLeastRecentlyUsed(): void {
    let oldestTime = Date.now();
    let oldestKey: string | null = null;
    
    // 查找最旧的项
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (!key || !key.startsWith(this.prefix)) continue;
      
      const item = this.parseStorageItem(key);
      if (item && item.createdAt < oldestTime) {
        oldestTime = item.createdAt;
        oldestKey = key;
      }
    }
    
    // 移除最旧的项
    if (oldestKey) {
      localStorage.removeItem(oldestKey);
      console.log(`[LocalStorageService] 缓存空间不足，已移除最久未访问项: ${oldestKey}`);
    }
  }
}

const localStorageService = new LocalStorageService();
export default localStorageService; 