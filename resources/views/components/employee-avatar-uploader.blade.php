@props([
    'modelId' => null,
    'modelType' => 'App\\Models\\User',
    'maxFiles' => 1,
    'images' => [],
    'tempId' => null
])

@php
    \Log::info('Avatar upload component initialization', [
        'model_id' => $modelId,
        'model_type' => $modelType,
        'max_files' => $maxFiles,
        'images' => $images,
        'temp_id' => $tempId
    ]);
@endphp

<div x-data="{
    images: @js($images),
    uploading: false,
    maxFiles: @js($maxFiles),
    dragover: false,
    
    init() {
        console.log('Avatar upload component initialization', {
            images: this.images,
            maxFiles: this.maxFiles
        });
        
        this.$watch('images', value => {
            if (value.length >= this.maxFiles) {
                this.$refs.fileInput.disabled = true;
            } else {
                this.$refs.fileInput.disabled = false;
            }
        });
    },

    async uploadFile(file) {
        if (this.images.length >= this.maxFiles) {
            Swal.fire({
                title: '上传失败',
                text: '已达到最大文件数限制',
                icon: 'error',
                confirmButtonText: '确定'
            });
            return;
        }

        const formData = new FormData();
        formData.append('file', file);
        formData.append('model_type', '{{ $modelType }}');
        formData.append('collection_name', 'avatar');
        @if($modelId)
            formData.append('model_id', {{ $modelId }});
        @endif
        @if($tempId)
            formData.append('temp_id', '{{ $tempId }}');
        @endif

        try {
            this.uploading = true;
            const response = await fetch('{{ route("media.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '上传失败');
            }

            const data = await response.json();
            console.log('Upload successfully', data);
            
            // 如果已经关联到用户，调用专门的updateAvatar接口
            @if($modelId)
            const updateAvatarResponse = await fetch('{{ route("employees.avatar.update", ["employee" => $modelId]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    media_id: data.id
                })
            });
            
            if (!updateAvatarResponse.ok) {
                const updateError = await updateAvatarResponse.json();
                throw new Error(updateError.message || '更新头像失败');
            }
            @endif
            
            this.images = [data];
            
            Toast.fire({
                icon: 'success',
                title: '头像上传成功'
            });

        } catch (error) {
            console.error('Upload failed', error);
            Swal.fire({
                title: '上传失败',
                text: error.message,
                icon: 'error',
                confirmButtonText: '确定'
            });
        } finally {
            this.uploading = false;
        }
    },

    async deleteImage(id) {
        const result = await Swal.fire({
            title: '确认删除',
            text: '确定要删除这个头像吗？',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '确定删除',
            cancelButtonText: '取消'
        });
        
        if (!result.isConfirmed) {
            return;
        }

        try {
            console.log('Start deleting pictures', { id });
            
            @if($modelId)
            // 如果已关联到用户，使用专门的deleteAvatar接口
            const response = await fetch('{{ route("employees.avatar.delete", ["employee" => $modelId]) }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            @else
            // 否则直接删除媒体文件
            const response = await fetch(`/media/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            @endif

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || '删除失败');
            }

            console.log('Delete successfully');
            this.images = [];
            
            Toast.fire({
                icon: 'success',
                title: '头像已删除'
            });

        } catch (error) {
            console.error('Deletion failed', error);
            Swal.fire({
                title: '删除失败',
                text: error.message,
                icon: 'error',
                confirmButtonText: '确定'
            });
        }
    }
}"
class="mt-1 relative">
    <!-- Preview area -->
    <div class="grid grid-cols-1 gap-4 mb-4">
        <template x-for="(image, index) in images" :key="image.id">
            <div class="relative aspect-square w-32 h-32 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full shadow-sm overflow-hidden border border-gray-200">
                <img :src="image.url" :alt="image.name" class="w-full h-full object-cover">
                <!-- Delete button -->
                <button type="button" 
                    @click="deleteImage(image.id)"
                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 focus:outline-none transform hover:scale-110 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <!-- Upload area -->
    <div x-show="images.length < maxFiles"
         @dragover.prevent="dragover = true"
         @dragleave.prevent="dragover = false"
         @drop.prevent="dragover = false; uploadFile($event.dataTransfer.files[0])"
         class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
         :class="{ 'border-indigo-500 bg-indigo-50': dragover }">
        <div class="space-y-1 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-gray-600">
                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                    <span>Upload avatar</span>
                    <input type="file" class="sr-only" accept="image/*" @change="uploadFile($event.target.files[0]); $event.target.value = null" x-ref="fileInput">
                </label>
                <p class="pl-1">Or drag the file here</p>
            </div>
            <p class="text-xs text-gray-500">PNG, JPG, GIF maximum 2MB</p>
        </div>
    </div>

    <!-- Uploading status -->
    <div x-show="uploading" 
        class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
        <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div> 