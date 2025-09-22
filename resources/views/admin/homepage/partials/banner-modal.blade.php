<!-- resources/views/admin/homepage/partials/banner-modal.blade.php -->
<div
    id="banner-modal"
    class="fixed inset-0 z-50 overflow-y-auto hidden"
    aria-labelledby="banner-modal-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div id="banner-modal-backdrop" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

    {{-- Modal Panel --}}
    <div class="flex items-end sm:items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- This element is to trick the browser into centering the modal contents. --}}
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            id="banner-modal-panel"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
            {{-- The form submission will be handled by JS event listener --}}
            <form id="banner-form-in-modal">
                @csrf {{-- Include CSRF token --}}
                {{-- Hidden input for method override (used by JS) --}}
                <input type="hidden" name="_method" id="banner-form-method" value="POST">
                {{-- Hidden input to store banner ID for editing (used by JS) --}}
                <input type="hidden" name="banner_id" id="banner-form-id" value="">

                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        {{-- Icon (Optional) --}}
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="banner-modal-title">
                                {{-- Title will be set by JS --}}
                                Add New Banner
                            </h3>
                            <div class="mt-4">
                                {{-- Include the banner form partial --}}
                                {{-- Pass an empty array for create, or potentially the banner data for edit --}}
                                {{-- Note: Passing PHP data directly might be complex with Alpine state. --}}
                                {{-- It might be better to populate the form via JS in openModal() --}}
                                @include('admin.homepage.partials.banner-form', ['banner' => []]) {{-- Always include with empty, populate via JS --}}

                                {{-- Error Message Area --}}
                                <div id="banner-modal-error-area" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-700"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        type="submit"
                        id="banner-modal-save-btn"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                    >
                        <span class="btn-text">Save</span> {{-- Text will be set by JS --}}
                        <span class="btn-loading hidden">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <button
                        type="button"
                        id="banner-modal-cancel-btn"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 

{{-- Banner Modal JavaScript --}}
<script>
    (function() { // Use IIFE to avoid polluting global scope
        const modal = document.getElementById('banner-modal');
        const backdrop = document.getElementById('banner-modal-backdrop');
        const modalPanel = document.getElementById('banner-modal-panel');
        const modalTitle = document.getElementById('banner-modal-title');
        const form = document.getElementById('banner-form-in-modal');
        const methodInput = document.getElementById('banner-form-method');
        const idInput = document.getElementById('banner-form-id');
        const saveButton = document.getElementById('banner-modal-save-btn');
        const cancelButton = document.getElementById('banner-modal-cancel-btn');
        const errorArea = document.getElementById('banner-modal-error-area');
        
        // --- Updated Selectors to match banner-form.blade.php ---
        const titleInput = form.querySelector('#banner-title'); 
        const subtitleInput = form.querySelector('#banner-subtitle');
        const buttonTextInput = form.querySelector('#banner-button-text');
        const buttonLinkInput = form.querySelector('#banner-button-link');
        const isActiveCheckbox = form.querySelector('#banner-active');
        const imageInput = form.querySelector('#banner-image'); // Corrected image input ID
        // Note: Image preview is handled by Alpine in the form, so direct JS manipulation is removed here.
        // We also need a way to handle existing image on edit.

        let isEditMode = false;

        // --- Function Definition --- 
        window.openBannerModal = function(isEditing = false, bannerData = null) {
            console.log(`Opening banner modal. Edit mode: ${isEditing}`, bannerData);
            isEditMode = isEditing;
            form.reset(); 
            errorArea.classList.add('hidden');
            errorArea.textContent = '';
            
            // Clear file input
            if (imageInput) imageInput.value = ''; 
            // TODO: Need a robust way to reset/set the Alpine image preview state here
            // For now, Alpine might retain the previous preview if not handled

            if (isEditMode && bannerData) {
                modalTitle.textContent = 'Edit Banner';
                methodInput.value = 'PUT';
                idInput.value = bannerData.id;
                form.action = `/admin/banners/${bannerData.id}`;

                // Populate form fields using corrected selectors
                if(titleInput) titleInput.value = bannerData.title || '';
                if(subtitleInput) subtitleInput.value = bannerData.subtitle || '';
                if(buttonTextInput) buttonTextInput.value = bannerData.button_text || '';
                if(buttonLinkInput) buttonLinkInput.value = bannerData.button_link || '';
                if(isActiveCheckbox) isActiveCheckbox.checked = bannerData.is_active ?? true;
                
                // TODO: Set the Alpine component's initial imageUrl for edit mode.
                // This might require dispatching an event or accessing Alpine directly.
                // Example (conceptual - requires Alpine setup to listen):
                // modal.dispatchEvent(new CustomEvent('set-banner-image', { detail: { url: bannerData.image_url } }));

            } else {
                modalTitle.textContent = 'Add New Banner';
                methodInput.value = 'POST';
                idInput.value = '';
                form.action = '/admin/banners/quick-create';
                if(isActiveCheckbox) isActiveCheckbox.checked = true;
                // TODO: Reset Alpine component's imageUrl for create mode.
                // modal.dispatchEvent(new CustomEvent('set-banner-image', { detail: { url: null } }));
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        // --- Close Modal Function ---
        function closeBannerModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // --- Form Submission Handler ---
        async function handleFormSubmit(event) {
            event.preventDefault();
            toggleButtonLoading(saveButton, true, 'Saving...');
            errorArea.classList.add('hidden');
            errorArea.textContent = '';

            let url = form.action;
            let method = 'POST'; 
            const formData = new FormData(form);
            
            if (methodInput.value === 'PUT' || methodInput.value === 'PATCH') {
                formData.append('_method', methodInput.value);
            }

            console.log(`Submitting banner form to ${url} via ${method} (Method Override: ${methodInput.value})`);

            try {
                const response = await fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    let errorMessage = data.message || `HTTP error ${response.status}`;
                    if (data.errors) {
                        errorMessage = Object.values(data.errors).flat().join(' ');
                    }
                    throw new Error(errorMessage);
                }

                if (window.NotificationSystem && window.NotificationSystem.Toast) {
                    window.NotificationSystem.Toast.success(data.message || 'Banner saved successfully.');
                } else {
                    alert('Banner saved successfully.');
                }
                closeBannerModal();
                document.body.dispatchEvent(new CustomEvent('banner-saved'));

            } catch (error) {
                console.error('Banner form submission error:', error);
                errorArea.textContent = error.message || 'An unexpected error occurred.';
                errorArea.classList.remove('hidden');
                if (window.NotificationSystem && window.NotificationSystem.Toast) {
                    window.NotificationSystem.Toast.error(error.message || 'Failed to save banner.');
                }
            } finally {
                toggleButtonLoading(saveButton, false);
            }
        }

        // --- Event Listeners ---
        if(cancelButton) cancelButton.addEventListener('click', closeBannerModal);
        if(backdrop) backdrop.addEventListener('click', closeBannerModal);
        if(form) form.addEventListener('submit', handleFormSubmit);

        // Remove the image preview listener as it's handled by Alpine
        /*
        if (imageInput && imagePreview) { 
            imageInput.addEventListener('change', function(event) { ... });
        }
        */
        
        // Simple button loading state handler
        function toggleButtonLoading(button, isLoading, loadingText = 'Saving...') {
            const btnText = button.querySelector('.btn-text');
            const btnLoading = button.querySelector('.btn-loading');
            if (!button || !btnText || !btnLoading) return;

            if (isLoading) {
                button.disabled = true;
                button.classList.add('disabled:opacity-50');
                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
            } else {
                button.disabled = false;
                button.classList.remove('disabled:opacity-50');
                btnText.classList.remove('hidden');
                btnLoading.classList.add('hidden');
            }
        }

    })();
</script> 