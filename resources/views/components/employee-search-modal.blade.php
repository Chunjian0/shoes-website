@props(['id' => 'employee-search-modal'])

<div x-data="employeeSearchModal()"
     x-init="init()"
     x-show="open"
     class="fixed z-50 inset-0 overflow-y-auto"
     x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity"
             aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
             role="dialog"
             aria-modal="true"
             aria-labelledby="modal-headline">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                            Search for employees
                        </h3>
                        <div class="mt-4">
                            <div class="relative">
                                <input type="text"
                                       x-model="searchQuery"
                                       @input="search"
                                       class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                       placeholder="Enter the employee's name or work number...">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Search results -->
                            <div class="mt-4 max-h-60 overflow-y-auto">
                                <template x-if="searchResults.length > 0">
                                    <ul class="divide-y divide-gray-200">
                                        <template x-for="employee in searchResults" :key="employee.id">
                                            <li class="py-3 flex items-center justify-between hover:bg-gray-50 cursor-pointer"
                                                @click="selectEmployee(employee)">
                                                <div class="flex items-center">
                                                    <template x-if="employee.avatar_url">
                                                        <img :src="employee.avatar_url"
                                                             class="h-8 w-8 rounded-full object-cover"
                                                             :alt="employee.name">
                                                    </template>
                                                    <template x-if="!employee.avatar_url">
                                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <span class="text-indigo-800 font-medium text-sm" x-text="employee.name.charAt(0)"></span>
                                                        </div>
                                                    </template>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900" x-text="employee.name"></p>
                                                        <p class="text-sm text-gray-500" x-text="employee.employee_id"></p>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                          :class="{
                                                              'bg-green-100 text-green-800': employee.status === 'active',
                                                              'bg-red-100 text-red-800': employee.status === 'inactive'
                                                          }"
                                                          x-text="employee.status === 'active' ? 'On-the-job' : 'Leaving'">
                                                    </span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                                <template x-if="searchResults.length === 0 && searchQuery">
                                    <div class="py-3 text-center text-gray-500">
                                        No matching employee found
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                        @click="close"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    closure
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function employeeSearchModal() {
    return {
        open: false,
        searchQuery: '',
        searchResults: [],
        selectedEmployee: null,
        debounceTimeout: null,

        init() {
            window.addEventListener('open-employee-search', () => {
                this.open = true;
            });
        },

        async search() {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(async () => {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }

                try {
                    const response = await fetch(`/api/employees/search?q=${encodeURIComponent(this.searchQuery)}`);
                    if (!response.ok) throw new Error('Search failed');
                    this.searchResults = await response.json();
                } catch (error) {
                    console.error('Search Error:', error);
                    this.searchResults = [];
                }
            }, 300);
        },

        selectEmployee(employee) {
            this.selectedEmployee = employee;
            window.dispatchEvent(new CustomEvent('employee-selected', {
                detail: employee
            }));
            this.close();
        },

        close() {
            this.open = false;
            this.searchQuery = '';
            this.searchResults = [];
        }
    }
}
</script>
@endpush 