# ProductsPage Sync Plan

## Goal
Synchronize the logic of `shoe-ecommerce-frontend/src/pages/ProductsPage.tsx` with `shoe-ecommerce-frontend/src/pages/ProductsPage.js`.

## Tasks & Steps

1.  **Analyze Differences**: Compare the two files to identify logical discrepancies. (DONE)
2.  **Implement Changes in `ProductsPage.tsx`**:
    *   [ ] **Imports**: Add `import { useLocation } from 'react-router-dom';`
    *   [ ] **State**:
        *   Update `filters` state type definition to include `featured?: boolean; new?: boolean; sale?: boolean;`.
        *   Update initial `filters` state: `featured: false, new: false, sale: false`.
    *   [ ] **`getEffectivePrice`**: Change `template.related_products` to `template.linked_products`. Adjust type if necessary (check `ProductTemplate` type).
    *   [ ] **Sorting (`filteredTemplates` useMemo)**:
        *   Align `name_asc`/`name_desc` logic to use `.toLowerCase()`.
        *   Add `popularity` sort case: `result.sort((a: ProductTemplate, b: ProductTemplate) => (b.views || 0) - (a.views || 0));`.
    *   [ ] **API Call (`fetchTemplateData`)**:
        *   Add `if (filters.featured) params.featured = 'true';`
        *   Add `if (filters.new) params.new = 'true';`
        *   Add `if (filters.sale) params.sale = 'true';`
        *   **Important**: Adjust the success check: `if (fetchTemplates.fulfilled.match(resultAction) && resultAction.payload?.data?.templates && resultAction.payload.data.templates.length > 0)` - *Need to confirm the actual API response structure expected by the TS code vs JS code.* Assuming JS reflects the need for `.data.templates`. **If TS expects `payload.data` to be the array directly, this needs adjustment.** For now, follow JS.
        *   Adjust cache storage: `return resultAction.payload;` inside the `if` block for caching.
    *   [ ] **Cache Key (`getCacheKey`)**: Append `:${filters.featured}:${filters.new}:${filters.sale}` to the key string.
    *   [ ] **URL Sync Effect**: Add the `useEffect` hook using `useLocation` to parse URL params (`category`, `price_min`, `price_max`, `sort`, `search`, `featured`, `new`, `sale`) and update `filters`, `searchInputValue`, and `currentPage`.
    *   [ ] **Refresh Handler (`handleRefreshData`)**: Update `cachePattern` string to include `:${filters.featured}:${filters.new}:${filters.sale}`.
    *   [ ] **Reset Handlers (`onReset` in FilterSidebar props)**: Update the object passed to `setFilters` in both desktop and mobile `onReset` handlers to include `featured: false, new: false, sale: false`.
    *   [ ] **Mobile Filter Callbacks**: Verify state updates and `toggleMobileFilter()` calls match JS versions.
    *   [ ] **Rendering (`renderProductGrid`)**: Double-check the logic for "No Matching Filters" and "Processing Data / Empty Page?" conditions (`templates.length > 0` vs `filteredTemplates.length === 0`).
    *   [ ] **Type Safety**: Ensure all changes are type-checked and interfaces/types are updated as needed.
3.  **Review and Test**: Verify the changes function correctly and match the intended JS logic. 