# Banner Carousel Implementation Plan

## 1. Current System Analysis

### Current Banner Implementation:
- The system currently supports a single banner image with title, subtitle, button text and link
- Banner data is stored in the `settings` table with group 'homepage'
- The frontend fetches this data through the HomepageSettingsContext

### Database Structure:
- Current settings table has fields: id, group, key, value, created_at, updated_at
- Homepage sections use a separate table with: id, title, subtitle, type, content (JSON), layout, position, etc.

## 2. Implementation Approach

To implement a carousel banner system, we'll utilize the existing `homepage_sections` table and add a new section type "banner_carousel". This approach allows us to:

1. Leverage the existing section management system
2. Store multiple banner images and their associated data
3. Maintain backward compatibility with existing code

## 3. Backend Changes

### Add new constants in HomepageSection model:
- Add TYPE_BANNER_CAROUSEL constant
- Add support for carousel layout

### Create banner carousel data structure:
```php
[
    'images' => [
        [
            'id' => 1,
            'image' => '/path/to/image1.jpg',
            'title' => 'Banner Title 1',
            'subtitle' => 'Subtitle 1',
            'button_text' => 'Shop Now',
            'button_link' => '/products',
            'position' => 1
        ],
        // More banner images
    ],
    'settings' => [
        'autoplay' => true,
        'delay' => 5000,
        'transition' => 'slide',
        'show_indicators' => true,
        'show_navigation' => true
    ]
]
```

### Modify HomepageService:
- Update createSection and updateSection methods to handle banner_carousel type
- Add helper method to validate and format carousel data

### Create API endpoint for banner management:
- Get all banners in carousel
- Add new banner
- Update banner
- Delete banner
- Reorder banners

## 4. Frontend Changes

### Create React Components:
1. BannerCarousel - Main component to display rotating banners
2. BannerCarouselSlide - Individual slide component
3. AdminBannerCarouselEditor - Admin UI for managing carousel banners

### Update HomepageSettingsContext:
- Extend to support carousel data structure
- Add methods to handle carousel data fetching

### Update HomePage Component:
- Replace single banner with carousel component when available
- Fallback to static banner when no carousel data exists

## 5. Admin Interface

### Create Admin UI for Carousel Management:
- Banner upload interface with drag-and-drop support
- Form fields for each banner's title, subtitle, button text, link
- Carousel settings (autoplay, delay, transitions)
- Reorder functionality with drag-and-drop
- Preview functionality

## 6. Migration Plan

1. Create new migration to add banner_carousel type support
2. Create admin interface for managing carousel
3. Update frontend to display carousel
4. Test with different number of banners and settings
5. Implement fallback for situations where carousel isn't working

## 7. Implementation Steps

### Step 1: Backend Setup
1. Modify HomepageSection model to support carousel type
2. Update HomepageService methods to handle carousel data
3. Create controller methods for carousel management

### Step 2: Admin Interface
1. Create carousel management interface in blade templates
2. Implement image upload and management functionality
3. Add form validation and error handling

### Step 3: Frontend Implementation
1. Create React carousel component using a library like Swiper
2. Implement context updates to support carousel data
3. Update HomePage to use carousel component

### Step 4: Testing and Optimization
1. Test carousel with various numbers of banners
2. Optimize image loading and transitions
3. Ensure mobile responsiveness

## 8. Technical Specifications

### Technologies to Use:
- Backend: Laravel with existing HomepageService
- Frontend: React with Swiper for carousel
- Image Storage: Laravel's storage system with public disk
- Admin Interface: Laravel Blade templates with Alpine.js for interactivity

### Performance Considerations:
- Lazy load images for better performance
- Optimize image sizes before upload
- Use appropriate caching strategies 