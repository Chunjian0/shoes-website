/**
 * CacheService - API响应缓存服务
 * 
 * 这个服务提供了一个灵活的前端缓存系统，可以为不同类型的API请求设置不同的缓存策略。
 * 通过缓存减少重复的API调用，提高应用响应速度和用户体验。
 */

interface CacheItem<T> {
  data: T;
  timestamp: number;
  expiry: number; // milliseconds
}

interface CacheConfig {
  defaultExpiry: number; // milliseconds
  maxSize: number; // maximum number of items in cache
  logEnabled: boolean;
  versionKey: string; // Used for cache invalidation by version
}

// 不同资源类型的默认缓存时间设置
export enum CacheExpiry {
  // 实时性高的资源短时间缓存或不缓存
  NONE = 0, // 不缓存
  CART = 60 * 1000, // 购物车数据缓存1分钟
  SESSION = 5 * 60 * 1000, // 会话相关数据缓存5分钟
  
  // 变化较少的资源可以缓存较长时间
  PRODUCT = 15 * 60 * 1000, // 产品数据缓存15分钟
  CATEGORY = 30 * 60 * 1000, // 类别数据缓存30分钟
  TEMPLATE = 60 * 60 * 1000, // 模板数据缓存60分钟
  
  // 长期不变的资源可以缓存更长时间
  STATIC = 24 * 60 * 60 * 1000 // 静态数据缓存24小时
}

class CacheService {
  private cache: Map<string, CacheItem<any>>;
  private config: CacheConfig;
  private cacheHits: number = 0;
  private cacheMisses: number = 0;
  private lastCleanup: number = Date.now();

  constructor(config?: Partial<CacheConfig>) {
    this.config = {
      defaultExpiry: CacheExpiry.PRODUCT, // Default is 15 minutes
      maxSize: 100, // Default max 100 items
      logEnabled: process.env.NODE_ENV === 'development',
      versionKey: 'cache_v1', // Cache version, change this to invalidate all caches
      ...config
    };
    
    this.cache = new Map<string, CacheItem<any>>();
    
    // Try to load cache from localStorage
    this.loadFromStorage();
    
    // Schedule periodic cleanup
    setInterval(() => this.cleanup(), 60000); // Cleanup every minute
  }

  /**
   * Set cache item with a specified expiry time
   */
  set<T>(key: string, data: T, expiry: number = this.config.defaultExpiry): void {
    // Generate namespaced key
    const namespacedKey = this.getNamespacedKey(key);
    
    // If cache is full, remove oldest item
    if (this.cache.size >= this.config.maxSize) {
      this.removeOldest();
    }
    
    const cacheItem: CacheItem<T> = {
      data,
      timestamp: Date.now(),
      expiry
    };
    
    this.cache.set(namespacedKey, cacheItem);
    this.log(`Cache SET: ${key} (expires in ${expiry/1000}s)`);
    
    // Update localStorage if appropriate
    this.saveToStorage();
  }

  /**
   * Get item from cache if it exists and hasn't expired
   */
  get<T>(key: string): T | null {
    const namespacedKey = this.getNamespacedKey(key);
    const item = this.cache.get(namespacedKey) as CacheItem<T> | undefined;
    
    if (!item) {
      this.cacheMisses++;
      this.log(`Cache MISS: ${key}`);
      return null;
    }
    
    // Check if expired
    if (Date.now() - item.timestamp > item.expiry) {
      this.cache.delete(namespacedKey);
      this.cacheMisses++;
      this.log(`Cache EXPIRED: ${key}`);
      return null;
    }
    
    this.cacheHits++;
    this.log(`Cache HIT: ${key} (age: ${(Date.now() - item.timestamp)/1000}s)`);
    return item.data;
  }

  /**
   * Check if the cache has a valid (non-expired) entry for the key
   */
  has(key: string): boolean {
    const namespacedKey = this.getNamespacedKey(key);
    const item = this.cache.get(namespacedKey);
    
    if (!item) {
      return false;
    }
    
    // Check if expired
    if (Date.now() - item.timestamp > item.expiry) {
      this.cache.delete(namespacedKey);
      return false;
    }
    
    return true;
  }

  /**
   * Remove item from cache
   */
  remove(key: string): void {
    const namespacedKey = this.getNamespacedKey(key);
    this.cache.delete(namespacedKey);
    this.log(`Cache REMOVE: ${key}`);
    this.saveToStorage();
  }

  /**
   * Remove all items from cache matching a pattern
   * @param pattern - A regular expression pattern to match against cache keys
   */
  removePattern(pattern: RegExp): void {
    const keysToRemove: string[] = [];
    
    this.cache.forEach((_, key) => {
      // Remove the namespace prefix for pattern matching
      const originalKey = key.substring(this.config.versionKey.length + 1);
      if (pattern.test(originalKey)) {
        keysToRemove.push(key);
      }
    });
    
    keysToRemove.forEach(key => this.cache.delete(key));
    this.log(`Cache REMOVE PATTERN: ${pattern} (removed ${keysToRemove.length} items)`);
    
    if (keysToRemove.length > 0) {
      this.saveToStorage();
    }
  }

  /**
   * Clear all cache
   */
  clear(): void {
    this.cache.clear();
    this.cacheHits = 0;
    this.cacheMisses = 0;
    this.log('Cache CLEARED');
    this.saveToStorage();
  }

  /**
   * Get cache statistics
   */
  getStats(): { size: number, hits: number, misses: number, hitRate: number } {
    const total = this.cacheHits + this.cacheMisses;
    const hitRate = total === 0 ? 0 : this.cacheHits / total;
    
    return {
      size: this.cache.size,
      hits: this.cacheHits,
      misses: this.cacheMisses,
      hitRate
    };
  }

  /**
   * Remove expired items from cache
   */
  private cleanup(): void {
    // Skip cleanup if the last one was less than a minute ago
    if (Date.now() - this.lastCleanup < 60000) {
      return;
    }
    
    this.lastCleanup = Date.now();
    const initialSize = this.cache.size;
    const keysToDelete: string[] = [];
    
    this.cache.forEach((item, key) => {
      if (Date.now() - item.timestamp > item.expiry) {
        keysToDelete.push(key);
      }
    });
    
    keysToDelete.forEach(key => this.cache.delete(key));
    
    if (keysToDelete.length > 0) {
      this.log(`Cache CLEANUP: Removed ${keysToDelete.length} expired items`);
      this.saveToStorage();
    }
  }

  /**
   * Remove oldest item from cache
   */
  private removeOldest(): void {
    if (this.cache.size === 0) return;
    
    let oldestKey: string | null = null;
    let oldestTimestamp = Date.now();
    
    this.cache.forEach((item, key) => {
      if (item.timestamp < oldestTimestamp) {
        oldestTimestamp = item.timestamp;
        oldestKey = key;
      }
    });
    
    if (oldestKey) {
      this.cache.delete(oldestKey);
      this.log(`Cache EVICT: Removed oldest item (${oldestKey})`);
    }
  }

  /**
   * Generate namespaced cache key
   */
  private getNamespacedKey(key: string): string {
    return `${this.config.versionKey}:${key}`;
  }

  /**
   * Log cache operations (only in development)
   */
  private log(message: string): void {
    if (this.config.logEnabled) {
      console.log(`%c[CacheService] ${message}`, 'color: #8a2be2');
    }
  }

  /**
   * Save cache to localStorage for persistence
   */
  private saveToStorage(): void {
    try {
      // Only store non-sensitive and size-appropriate data
      const storableCache = new Map<string, CacheItem<any>>();
      
      this.cache.forEach((item, key) => {
        // Skip session and cart data
        if (key.includes('cart') || key.includes('session') || key.includes('auth')) {
          return;
        }
        
        // Check the serialized size (roughly)
        const itemSize = JSON.stringify(item.data).length;
        if (itemSize > 100000) { // Skip items larger than ~100KB
          return;
        }
        
        storableCache.set(key, item);
      });
      
      // Convert Map to Array for serialization
      const cacheArray = Array.from(storableCache.entries());
      
      // Store in localStorage with size limit
      const serialized = JSON.stringify(cacheArray);
      if (serialized.length < 5000000) { // 5MB localStorage limit in most browsers
        localStorage.setItem('cache_data', serialized);
      }
    } catch (e) {
      console.error('Error saving cache to localStorage:', e);
    }
  }

  /**
   * Load cache from localStorage on init
   */
  private loadFromStorage(): void {
    try {
      const cached = localStorage.getItem('cache_data');
      if (cached) {
        const cacheArray = JSON.parse(cached);
        
        if (Array.isArray(cacheArray)) {
          // Convert the array back to Map
          const restoredCache = new Map<string, CacheItem<any>>(cacheArray);
          
          // Only keep non-expired items
          restoredCache.forEach((item, key) => {
            if (Date.now() - item.timestamp <= item.expiry) {
              this.cache.set(key, item);
            }
          });
          
          this.log(`Cache LOADED: Restored ${this.cache.size} items from localStorage`);
        }
      }
    } catch (e) {
      console.error('Error loading cache from localStorage:', e);
    }
  }
}

// Create a singleton instance
const cacheService = new CacheService();

// API-specific helper methods
export const apiCache = {
  /**
   * Get data for cart API with short cache time
   */
  async getCart<T>(key: string, fetchFn: () => Promise<T>): Promise<T> {
    const cacheKey = `cart:${key}`;
    const cachedData = cacheService.get<T>(cacheKey);
    
    if (cachedData) {
      return cachedData;
    }
    
    const data = await fetchFn();
    cacheService.set(cacheKey, data, CacheExpiry.CART);
    return data;
  },
  
  /**
   * Invalidate all cart cache when cart is updated
   */
  invalidateCart(): void {
    cacheService.removePattern(/^cart:/);
  },
  
  /**
   * Get product data with medium cache time
   */
  async getProduct<T>(id: string | number, fetchFn: () => Promise<T>): Promise<T> {
    const cacheKey = `product:${id}`;
    const cachedData = cacheService.get<T>(cacheKey);
    
    if (cachedData) {
      return cachedData;
    }
    
    const data = await fetchFn();
    cacheService.set(cacheKey, data, CacheExpiry.PRODUCT);
    return data;
  },
  
  /**
   * Get product list with medium cache time
   */
  async getProductList<T>(params: string, fetchFn: () => Promise<T>): Promise<T> {
    const cacheKey = `products:${params}`;
    const cachedData = cacheService.get<T>(cacheKey);
    
    if (cachedData) {
      return cachedData;
    }
    
    const data = await fetchFn();
    cacheService.set(cacheKey, data, CacheExpiry.PRODUCT);
    return data;
  },
  
  /**
   * Invalidate product cache when products are updated
   */
  invalidateProduct(id?: string | number): void {
    if (id) {
      cacheService.remove(`product:${id}`);
    } else {
      cacheService.removePattern(/^product:/);
    }
    // Also invalidate product lists as they might contain this product
    cacheService.removePattern(/^products:/);
  },
  
  /**
   * Get template data with long cache time
   */
  async getTemplate<T>(id: string | number, fetchFn: () => Promise<T>): Promise<T> {
    const cacheKey = `template:${id}`;
    const cachedData = cacheService.get<T>(cacheKey);
    
    if (cachedData) {
      return cachedData;
    }
    
    const data = await fetchFn();
    cacheService.set(cacheKey, data, CacheExpiry.TEMPLATE);
    return data;
  },
  
  /**
   * Get general data with default cache time
   */
  async get<T>(key: string, fetchFn: () => Promise<T>, expiry?: number): Promise<T> {
    const cacheKey = `general:${key}`;
    const cachedData = cacheService.get<T>(cacheKey);
    
    if (cachedData) {
      return cachedData;
    }
    
    const data = await fetchFn();
    cacheService.set(cacheKey, data, expiry);
    return data;
  },
  
  // Direct access to cache service methods
  remove: (key: string) => cacheService.remove(key),
  clear: () => cacheService.clear(),
  getStats: () => cacheService.getStats(),
};

export default cacheService; 