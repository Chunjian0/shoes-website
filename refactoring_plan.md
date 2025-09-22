# Homepage Management UI Refactoring Plan

## Goals
- Fix non-functional "Show Settings" buttons.
- Improve overall layout, styling, and user-friendliness using Tailwind CSS.
- Ensure all existing functionality (sorting, adding/removing items, saving settings, modals, notifications) remains intact.
- Make the UI more consistent and visually appealing.

## Steps

1.  [ ] **Fix "Show Settings" Buttons:**
*   [ ] Analyze JavaScript in `index.blade.php` for toggle button event listeners (`#toggle-featured-banner-settings`, `#toggle-new-products-settings`, `#toggle-sale-products-settings`). Current code seems to lack handlers for these.
*   [ ] Implement event listeners to toggle the `.hidden` class on corresponding containers (`#featured-banner-settings-container`, etc.) and update button text/icons.
*   [ ] Verify toggle functionality for all settings sections (Featured, New Arrivals, Sale Products, Carousel, Homepage).

2.  [x] **Refactor Stock Threshold Section:**
*   [x] Improve card styling and consistency.
*   [x] Adjust layout of label, input, and buttons for better alignment and spacing.

3.  [x] **Refactor Tab Navigation:**
*   [x] Ensure consistent styling for active/inactive tabs.
*   [x] Improve spacing and visual separation.

4.  [ ] **Refactor Tab Content:**
*   [ ] **Featured Products Tab:**
*   [ ] Refactor "Featured Products Banner" settings card.
*   [ ] Refactor "Featured Product Templates" management card.
*   [ ] Improve table styling (padding, alignment, borders, responsiveness).
*   [ ] Standardize button styles (Add, View, Remove).
*   [ ] Review pagination styling.
*   [ ] **New Arrivals Tab:**
*   [ ] Refactor "New Arrivals Section" settings card.
*   [ ] Refactor "New Arrival Product Templates" management card.
*   [ ] Improve table styling.
*   [ ] Standardize button styles.
*   [ ] Review pagination styling.
*   [ ] **Sale Products Tab:**
*   [ ] Refactor "Sale Products Section" settings card.
*   [ ] Refactor "Sale Product Templates" management card.
*   [ ] Improve table styling.
*   [ ] Standardize button styles.
*   [ ] Review pagination styling.
*   [ ] **Banners Tab:**
*   [ ] Refactor "Banner Management" card (Add button).
*   [ ] Refactor "Carousel Settings" card.
*   [ ] Improve banner table styling.
*   [ ] Standardize button styles (Toggle Active, Delete).

5.  [ ] **Refactor Homepage Settings Section:**
*   [ ] Improve card styling.
*   [ ] Ensure included partial (`admin.homepage.partials.homepage-settings`) layout is consistent.

6.  [ ] **Refactor Modals:**
*   [ ] **Product Modal:** Improve structure, search input, table, pagination, button styles.
*   [ ] **Banner Management Modal (View/Sort):** Improve structure, table, text styles. (Note: This modal seems missing in the provided HTML, might need implementation or was removed).
*   [ ] **Add Banner Modal:** Improve form layout, image preview, input styles, button styles.

7.  [ ] **Final Review & Testing:**
*   [ ] Verify all functionalities across all sections and modals.
*   [ ] Check UI responsiveness.
*   [ ] Ensure no JavaScript errors in the console.
*   [ ] Confirm consistent styling and improved visual appeal.