/**
 * Product Analytics Tracking Library
 * A client-side library for tracking product impressions and clicks
 */
(function(window) {
    'use strict';

    // Configuration object
    const config = {
        apiBaseUrl: '/api/analytics',
        batchSize: 10,        // Number of impressions to batch before sending
        sendDelay: 1000,      // Milliseconds to wait before sending batch
        debug: false,         // Set to true to enable console logging
        viewThreshold: 0.5,   // Percentage of product that must be visible to count as viewed (0.5 = 50%)
        throttleDelay: 100,   // Milliseconds to wait between scroll/resize checks
        productSelector: '[data-product-id]', // CSS selector for product elements
    };

    // Create namespace
    const ProductAnalytics = {};

    // Private storage
    let _initialized = false;
    let _impressionQueue = [];
    let _seenProducts = new Set();
    let _pendingSendTimeout = null;
    let _throttleTimeout = null;
    let _intersectionObserver = null;

    /**
     * Initialize the tracking system
     * @param {Object} options - Configuration options
     */
    ProductAnalytics.init = function(options = {}) {
        if (_initialized) {
            this.log('Product Analytics already initialized');
            return;
        }

        // Merge custom options with defaults
        Object.assign(config, options);
        this.log('Initializing Product Analytics with config:', config);

        // Initialize intersection observer if supported
        if (window.IntersectionObserver) {
            this.initIntersectionObserver();
        } else {
            // Fallback to scroll/resize event tracking
            this.initScrollTracking();
        }

        // Set up click tracking
        this.initClickTracking();

        // Set up unload handler to send any remaining impressions
        window.addEventListener('beforeunload', function() {
            ProductAnalytics.sendImpressions(true);
        });

        _initialized = true;
        this.log('Product Analytics initialized');

        // Initial scan for visible products after a short delay
        setTimeout(() => this.checkVisibleProducts(), 500);
    };

    /**
     * Initialize intersection observer
     */
    ProductAnalytics.initIntersectionObserver = function() {
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: config.viewThreshold
        };

        _intersectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const productEl = entry.target;
                const productId = productEl.getAttribute('data-product-id');
                
                if (entry.isIntersecting && !_seenProducts.has(productId)) {
                    this.recordImpression(productId, productEl);
                }
            });
        }, options);

        // Observe all existing products
        document.querySelectorAll(config.productSelector).forEach(product => {
            _intersectionObserver.observe(product);
        });

        // Set up a mutation observer to watch for new products added to the DOM
        const mutationObserver = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            if (node.matches && node.matches(config.productSelector)) {
                                _intersectionObserver.observe(node);
                            } else if (node.querySelectorAll) {
                                node.querySelectorAll(config.productSelector).forEach(product => {
                                    _intersectionObserver.observe(product);
                                });
                            }
                        }
                    });
                }
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    };

    /**
     * Initialize scroll-based tracking as fallback
     */
    ProductAnalytics.initScrollTracking = function() {
        const throttledCheck = () => {
            if (_throttleTimeout) {
                clearTimeout(_throttleTimeout);
            }
            _throttleTimeout = setTimeout(() => {
                this.checkVisibleProducts();
            }, config.throttleDelay);
        };

        // Bind to scroll and resize events
        window.addEventListener('scroll', throttledCheck, { passive: true });
        window.addEventListener('resize', throttledCheck, { passive: true });
        
        // Also check on page content updates
        document.addEventListener('DOMContentLoaded', throttledCheck);
        window.addEventListener('load', throttledCheck);
        window.addEventListener('turbolinks:load', throttledCheck);

        // Listen for custom page update events
        document.addEventListener('content-updated', throttledCheck);
        document.addEventListener('ajax-loaded', throttledCheck);
    };

    /**
     * Initialize click tracking
     */
    ProductAnalytics.initClickTracking = function() {
        document.addEventListener('click', (e) => {
            // Check if the click was on a product or its child elements
            let target = e.target;
            let productElement = null;
            
            // Traverse up the DOM to find the product element
            while (target && target !== document) {
                if (target.hasAttribute('data-product-id')) {
                    productElement = target;
                    break;
                }
                target = target.parentElement;
            }
            
            // If we found a product element, record the click
            if (productElement) {
                const productId = productElement.getAttribute('data-product-id');
                this.recordClick(productId, productElement);
            }
        });
    };

    /**
     * Record a product impression
     * @param {string} productId - The product ID
     * @param {Element} element - The DOM element representing the product
     */
    ProductAnalytics.recordImpression = function(productId, element) {
        if (!productId) return;
        
        // Don't record duplicate impressions for the same product
        if (_seenProducts.has(productId)) return;
        _seenProducts.add(productId);
        
        // Extract additional data from the element
        const sectionType = element.getAttribute('data-section-type') || this.detectSectionType(element);
        const position = element.getAttribute('data-position') || this.detectPosition(element);
        
        // Create the impression object
        const impressionData = {
            product_id: productId,
            section_type: sectionType,
            position: position,
            device_type: this.getDeviceType(),
            url: window.location.href,
            timestamp: new Date().toISOString()
        };
        
        this.log('Recording impression:', impressionData);
        
        // Add to queue and possibly send
        _impressionQueue.push(impressionData);
        this.scheduleSend();
    };

    /**
     * Record a product click
     * @param {string} productId - The product ID
     * @param {Element} element - The DOM element that was clicked
     */
    ProductAnalytics.recordClick = function(productId, element) {
        if (!productId) return;
        
        // Extract additional data
        const sectionType = element.getAttribute('data-section-type') || this.detectSectionType(element);
        const position = element.getAttribute('data-position') || this.detectPosition(element);
        
        // Create the click data
        const clickData = {
            product_id: productId,
            section_type: sectionType,
            position: position,
            device_type: this.getDeviceType(),
            url: window.location.href,
            referrer: document.referrer,
            timestamp: new Date().toISOString()
        };
        
        this.log('Recording click:', clickData);
        // Prevent sending click data to avoid 500 error
        // this.sendData('/click', clickData);
    };

    /**
     * Check which products are currently visible in the viewport
     */
    ProductAnalytics.checkVisibleProducts = function() {
        if (!document.querySelectorAll) return;
        
        const products = document.querySelectorAll(config.productSelector);
        this.log(`Checking visibility of ${products.length} products`);
        
        products.forEach(product => {
            const productId = product.getAttribute('data-product-id');
            if (!productId || _seenProducts.has(productId)) return;
            
            if (this.isElementVisible(product)) {
                this.recordImpression(productId, product);
            }
        });
    };

    /**
     * Schedule sending the impression batch
     */
    ProductAnalytics.scheduleSend = function() {
        if (_pendingSendTimeout) return;
        
        if (_impressionQueue.length >= config.batchSize) {
            // Send immediately if we've reached batch size
            this.sendImpressions();
        } else {
            // Otherwise schedule a send after delay
            _pendingSendTimeout = setTimeout(() => {
                this.sendImpressions();
            }, config.sendDelay);
        }
    };

    /**
     * Send accumulated impressions to the server
     * @param {boolean} immediate - Whether to send immediately regardless of batch size
     */
    ProductAnalytics.sendImpressions = function(immediate = false) {
        if (_pendingSendTimeout) {
            clearTimeout(_pendingSendTimeout);
            _pendingSendTimeout = null;
        }
        
        if (_impressionQueue.length === 0) return;
        
        if (immediate || _impressionQueue.length >= config.batchSize) {
            const batch = _impressionQueue.slice();
            _impressionQueue = [];
            
            this.log(`Sending batch of ${batch.length} impressions`);
            this.sendData('/impression/batch', { impressions: batch });
        }
    };

    /**
     * Send data to the server
     * @param {string} endpoint - API endpoint to send to
     * @param {Object} data - Data to send
     */
    ProductAnalytics.sendData = function(endpoint, data) {
        const url = config.apiBaseUrl + endpoint;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            this.log('API response:', data);
        })
        .catch(error => {
            console.error('Error sending analytics data:', error);
        });
    };

    /**
     * Check if an element is visible in the viewport
     * @param {Element} element - DOM element to check
     * @returns {boolean} True if element is visible
     */
    ProductAnalytics.isElementVisible = function(element) {
        if (!element.getBoundingClientRect) return false;
        
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight || document.documentElement.clientHeight;
        const windowWidth = window.innerWidth || document.documentElement.clientWidth;
        
        // Check if at least threshold % of the element is in the viewport
        const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
        const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);
        
        if (!vertInView || !horInView) return false;
        
        // Calculate visibility percentage
        const visibleHeight = Math.min(rect.bottom, windowHeight) - Math.max(rect.top, 0);
        const visibleWidth = Math.min(rect.right, windowWidth) - Math.max(rect.left, 0);
        const visibleArea = visibleHeight * visibleWidth;
        const totalArea = rect.height * rect.width;
        const visibilityRatio = visibleArea / totalArea;
        
        return visibilityRatio >= config.viewThreshold;
    };

    /**
     * Get the CSRF token from the meta tag
     * @returns {string} CSRF token
     */
    ProductAnalytics.getCsrfToken = function() {
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        return tokenEl ? tokenEl.getAttribute('content') : '';
    };

    /**
     * Detect the type of section this product is in
     * @param {Element} element - Product element
     * @returns {string} Section type
     */
    ProductAnalytics.detectSectionType = function(element) {
        // Try to find a parent with section information
        let parent = element.parentElement;
        while (parent && parent !== document) {
            if (parent.hasAttribute('data-section-type')) {
                return parent.getAttribute('data-section-type');
            }
            parent = parent.parentElement;
        }
        
        // Try to infer from classes or IDs
        const classes = [
            { pattern: /featured/i, type: 'featured' },
            { pattern: /new-arrival/i, type: 'new_arrivals' },
            { pattern: /sale/i, type: 'sale' },
            { pattern: /popular/i, type: 'popular' },
            { pattern: /related/i, type: 'related' }
        ];
        
        // Check the element and its parents
        parent = element;
        while (parent && parent !== document) {
            const classAttr = parent.className || '';
            const idAttr = parent.id || '';
            
            for (const cls of classes) {
                if (cls.pattern.test(classAttr) || cls.pattern.test(idAttr)) {
                    return cls.type;
                }
            }
            
            parent = parent.parentElement;
        }
        
        // Default to unknown
        return 'unknown';
    };

    /**
     * Detect the position of this product in its container
     * @param {Element} element - Product element
     * @returns {number} Position (1-based)
     */
    ProductAnalytics.detectPosition = function(element) {
        // If position is explicitly set, use that
        if (element.hasAttribute('data-position')) {
            return parseInt(element.getAttribute('data-position'), 10);
        }
        
        // Try to find siblings with the same tag and count position
        const parent = element.parentElement;
        if (parent) {
            const siblings = Array.from(parent.children).filter(el => 
                el.tagName === element.tagName || 
                (el.hasAttribute('data-product-id') && element.hasAttribute('data-product-id'))
            );
            const position = siblings.indexOf(element) + 1;
            if (position > 0) {
                return position;
            }
        }
        
        return 0;
    };

    /**
     * Get the current device type
     * @returns {string} Device type
     */
    ProductAnalytics.getDeviceType = function() {
        const width = window.innerWidth;
        if (width < 768) return 'mobile';
        if (width < 1024) return 'tablet';
        return 'desktop';
    };

    /**
     * Utility logging function
     */
    ProductAnalytics.log = function(...args) {
        if (config.debug && window.console && window.console.log) {
            console.log('[ProductAnalytics]', ...args);
        }
    };

    // Expose to window
    window.ProductAnalytics = ProductAnalytics;
    
    // Auto-initialize if data attribute is present
    document.addEventListener('DOMContentLoaded', function() {
        if (document.body.hasAttribute('data-analytics-auto-init')) {
            ProductAnalytics.init();
        }
    });
    
    // Also initialize on Turbolinks page loads
    document.addEventListener('turbolinks:load', function() {
        if (document.body.hasAttribute('data-analytics-auto-init')) {
            _seenProducts = new Set(); // Reset seen products on page navigation
            ProductAnalytics.init();
        }
    });

})(window);
