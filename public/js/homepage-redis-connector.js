/**
 * Homepage Redis Connector
 * 
 * This script provides a bridge between the Laravel backend and React frontend Redis cache systems.
 * It ensures consistent data representation between both platforms and handles cache invalidation
 * when updates occur in the admin panel.
 */

(function() {
    // Avoid duplicate initialization
    if (window.homepageRedisConnector) return;
    
    /**
     * Homepage Redis Connector Class
     * Manages communication with the React frontend's Redis-like cache service
     */
    class HomepageRedisConnector {
        constructor() {
            this.apiEndpoint = '/api/homepage';
            this.cacheKeys = {
                settings: 'homepage_settings',
                featured: 'featured_products',
                newArrivals: 'new_arrival_products',
                sale: 'sale_products',
                sections: 'homepage_sections',
                autoManagement: 'auto_management_settings'
            };
            this.defaultExpiry = 5 * 60 * 1000; // 5 minutes default
            
            // Initialize listeners
            this.initEventListeners();
        }
        
        /**
         * Initialize event listeners for cache invalidation
         */
        initEventListeners() {
            // Listen for homepage updates from Laravel events
            document.addEventListener('homepage:updated', (event) => {
                this.invalidateCache(event.detail.type);
            });
            
            // Listen for Livewire events
            document.addEventListener('livewire:load', () => {
                if (window.Livewire) {
                    window.Livewire.on('homepageUpdated', (type) => {
                        this.invalidateCache(type);
                    });
                }
            });
        }
        
        /**
         * Invalidate specific cache based on update type
         * @param {string} type - The type of update (featured, newArrivals, etc.)
         */
        invalidateCache(type) {
            const key = this.getCacheKey(type);
            if (key) {
                // Send message to React frontend via postMessage
                this.sendMessageToFrontend('invalidateCache', { key });
                
                // If running in same domain, also try to directly clear localStorage
                this.clearLocalStorageCache(key);
                
                console.log(`Cache invalidated: ${key}`);
            }
        }
        
        /**
         * Get the corresponding cache key for a given update type
         * @param {string} type - The type of update
         * @return {string|null} - The cache key or null if not found
         */
        getCacheKey(type) {
            const typeMapping = {
                'settings': this.cacheKeys.settings,
                'featured_products': this.cacheKeys.featured,
                'new_arrivals': this.cacheKeys.newArrivals,
                'sale_products': this.cacheKeys.sale,
                'sections': this.cacheKeys.sections,
                'auto_management': this.cacheKeys.autoManagement
            };
            
            return typeMapping[type] || null;
        }
        
        /**
         * Send message to React frontend via postMessage
         * @param {string} action - The action to perform
         * @param {object} data - The data to send
         */
        sendMessageToFrontend(action, data) {
            // Send to parent if in iframe
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    source: 'homepage-admin',
                    action,
                    data
                }, '*');
            }
            
            // Get admin origins dynamically
            const adminOrigins = [
                window.frontendAppUrl || window.location.origin, // Use injected URL or fallback to current origin
                window.location.origin // Always include current origin
            ];
            
            // Remove duplicates and invalid origins
            const uniqueOrigins = [...new Set(adminOrigins.filter(origin => origin && origin !== 'null'))];

            uniqueOrigins.forEach(origin => {
                try {
                    window.postMessage({
                        source: 'homepage-admin', // Check if this source name is correct for the receiving end
                        action,
                        data
                    }, origin);
                } catch (e) {
                    console.error('Failed to send message to origin:', origin, e);
                }
            });
        }
        
        /**
         * Directly clear localStorage cache if in same domain
         * @param {string} key - The cache key to clear
         */
        clearLocalStorageCache(key) {
            try {
                // Front-end Redis service prefixes keys
                const prefixedKey = `redis_cache:${key}`;
                localStorage.removeItem(prefixedKey);
                
                // Also try to clear any JSON stringified version
                const keys = Object.keys(localStorage);
                keys.forEach(storageKey => {
                    if (storageKey.includes(key)) {
                        localStorage.removeItem(storageKey);
                    }
                });
            } catch (e) {
                console.warn('Could not access localStorage', e);
            }
        }
        
        /**
         * Manually trigger cache refresh to the frontend
         * @param {string|array} types - The type(s) of cache to refresh
         */
        refreshCache(types) {
            const typesToRefresh = Array.isArray(types) ? types : [types];
            
            typesToRefresh.forEach(type => {
                this.invalidateCache(type);
            });
            
            // Also fetch fresh data
            this.prefetchData(typesToRefresh);
        }
        
        /**
         * Prefetch data to warm the cache
         * @param {array} types - The types of data to prefetch
         */
        prefetchData(types) {
            types.forEach(type => {
                let endpoint = '';
                
                switch(type) {
                    case 'settings':
                        endpoint = `${this.apiEndpoint}/settings`;
                        break;
                    case 'featured_products':
                        endpoint = `${this.apiEndpoint}/featured-products`;
                        break;
                    case 'new_arrivals':
                        endpoint = `${this.apiEndpoint}/new-arrivals`;
                        break;
                    case 'sale_products':
                        endpoint = `${this.apiEndpoint}/sale-products`;
                        break;
                    case 'sections':
                        endpoint = `${this.apiEndpoint}/sections`;
                        break;
                    case 'auto_management':
                        endpoint = `${this.apiEndpoint}/auto-management-settings`;
                        break;
                    default:
                        return;
                }
                
                // Fetch fresh data
                fetch(endpoint)
                    .then(response => response.json())
                    .then(data => {
                        // Send new data to frontend
                        this.sendMessageToFrontend('cacheData', {
                            key: this.getCacheKey(type),
                            data,
                            expiry: this.defaultExpiry
                        });
                        
                        console.log(`Data prefetched for: ${type}`);
                    })
                    .catch(err => {
                        console.error(`Failed to prefetch data for ${type}`, err);
                    });
            });
        }
        
        /**
         * Check if React frontend is available
         * @return {Promise<boolean>} - Whether the frontend is available
         */
        checkFrontendAvailability() {
            return new Promise((resolve) => {
                const messageId = `check_${Date.now()}`;
                
                // Setup message listener
                const messageHandler = (event) => {
                    if (event.data && event.data.source === 'homepage-react' && 
                        event.data.action === 'pong' && event.data.id === messageId) {
                        window.removeEventListener('message', messageHandler);
                        resolve(true);
                    }
                };
                
                window.addEventListener('message', messageHandler);
                
                // Send ping message
                this.sendMessageToFrontend('ping', { id: messageId });
                
                // Timeout after 2 seconds
                setTimeout(() => {
                    window.removeEventListener('message', messageHandler);
                    resolve(false);
                }, 2000);
            });
        }
    }
    
    // Create and expose the connector
    window.homepageRedisConnector = new HomepageRedisConnector();
    
    // Trigger initial check when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        window.homepageRedisConnector.checkFrontendAvailability()
            .then(available => {
                if (available) {
                    console.log('React frontend detected, connector active');
                } else {
                    console.log('React frontend not detected, connector in standby mode');
                }
            });
    });
    
    // Also check on turbolinks:load for applications using Turbolinks
    document.addEventListener('turbolinks:load', () => {
        if (window.homepageRedisConnector) {
            window.homepageRedisConnector.checkFrontendAvailability();
        }
    });
})(); 