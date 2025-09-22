<div>
    <nav class="bg-white shadow" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Navigation link -->
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-xl font-bold text-indigo-600">GSMS</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <!-- use wire:click Make page switching -->
                        <a href="#" 
                           wire:click.prevent="changePage('dashboard')"
                           class="@if($currentPage === 'dashboard') border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="#"
                           wire:click.prevent="changePage('customers')"
                           class="@if($currentPage === 'customers') border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Customers
                        </a>
                        <!-- More navigation links -->
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="-mr-2 flex items-center sm:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <svg class="h-6 w-6" x-show="!mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" x-show="mobileMenuOpen" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" class="sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="#"
                   wire:click.prevent="changePage('dashboard')"
                   class="@if($currentPage === 'dashboard') bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Dashboard
                </a>
                <a href="#"
                   wire:click.prevent="changePage('customers')"
                   class="@if($currentPage === 'customers') bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    Customers
                </a>
                <!-- More mobile menu items -->
            </div>
        </div>
    </nav>

    <!-- Content area -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div wire:loading class="fixed top-0 left-0 right-0 bottom-0 w-full h-full z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
                <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
                <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
                <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
            </div>

            <div wire:loading.remove>
                @if($currentPage === 'dashboard')
                    @livewire('dashboard')
                @elseif($currentPage === 'customers')
                    @livewire('customers')
                @endif
            </div>
        </div>
    </main>
</div>

<style>
.loader {
    border-top-color: #3498db;
    -webkit-animation: spinner 1.5s linear infinite;
    animation: spinner 1.5s linear infinite;
}

@-webkit-keyframes spinner {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
}

@keyframes spinner {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
