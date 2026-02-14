<x-app-layout>
    <div class="py-8 bg-gray-50 dark:bg-gray-900 min-h-screen font-sans" x-data="mediaLibrary()">
        
        <!-- Header -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Media Library</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all your uploaded assets from Templates and Builder.</p>
                </div>
                <div>
                     <button @click="$refs.fileInput.click()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Upload Image
                    </button>
                    <input type="file" x-ref="fileInput" class="hidden" accept="image/*" @change="uploadFile($event)">
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
                
                <!-- Search -->
                <div class="relative w-full md:w-64">
                    <input type="text" x-model.debounce.500ms="filters.search" @input="fetchMedia()" placeholder="Search filename..." 
                           class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Filters Group -->
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    <select x-model="filters.source" @change="fetchMedia()" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All Sources</option>
                        <option value="manual">Manual Uploads</option>
                        <option value="zip">Template ZIPs</option>
                        <option value="grapesjs">Builder Uploads</option>
                    </select>

                    <select x-model="filters.range" @change="fetchMedia()" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm py-2 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500">
                         <option value="all">All Time</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Loading -->
            <div x-show="isLoading" class="flex justify-center py-20">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
            </div>

            <!-- Empty State -->
            <div x-show="!isLoading && assets.length === 0" class="text-center py-20 bg-white dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No media found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or upload a new image.</p>
            </div>

            <!-- Grid -->
            <div x-show="!isLoading && assets.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                <template x-for="asset in assets" :key="asset.id">
                    <div class="group relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                        
                        <!-- Preview -->
                        <div class="aspect-w-16 aspect-h-12 bg-gray-100 dark:bg-gray-900 relative">
                             <img :src="asset.url" class="object-cover w-full h-full" loading="lazy">
                             
                             <!-- Overlay Actions -->
                             <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                <button @click="copyUrl(asset.url)" class="p-2 bg-white rounded-full text-gray-700 hover:text-indigo-600 transition-colors" title="Copy URL">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                                <button @click="deleteAsset(asset)" class="p-2 bg-white rounded-full text-gray-700 hover:text-red-600 transition-colors" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                             </div>
                        </div>

                        <!-- Info -->
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="asset.filename"></p>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-500 capitalize" x-text="formatSize(asset.size)"></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                      :class="{
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': asset.source === 'grapesjs',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': asset.source === 'zip',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': asset.source === 'manual'
                                      }"
                                      x-text="asset.source">
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Pagination -->
             <div x-show="!isLoading && meta.last_page > 1" class="mt-8 flex justify-center">
                 <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                        Previous
                    </button>
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span>
                    </span>
                    <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                        Next
                    </button>
                </nav>
            </div>

        </div>
    </div>

    <script>
        function mediaLibrary() {
            return {
                isLoading: true,
                assets: [],
                meta: { current_page: 1, last_page: 1 },
                filters: {
                    search: '',
                    source: 'all',
                    range: '30d'
                },

                init() {
                    this.fetchMedia();
                },

                fetchMedia(page = 1) {
                    this.isLoading = true;
                    const params = new URLSearchParams({
                        page: page,
                        ...this.filters
                    });

                    fetch('{{ route("media.list") }}?' + params.toString())
                        .then(res => res.json())
                        .then(data => {
                            this.assets = data.data;
                            this.meta = { current_page: data.current_page, last_page: data.last_page };
                            this.isLoading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.isLoading = false;
                        });
                },

                changePage(page) {
                    if (page >= 1 && page <= this.meta.last_page) {
                        this.fetchMedia(page);
                    }
                },

                uploadFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Optimistic UI or minimal loading?
                    // Let's show a toast or something. For now just standard upload.
                    const formData = new FormData();
                    formData.append('file', file);

                    fetch('{{ route("media.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(asset => {
                        this.assets.unshift(asset); // Add to top
                        // Reset input
                        event.target.value = '';
                    })
                    .catch(err => alert('Upload failed'));
                },

                deleteAsset(asset) {
                    if (!confirm('Are you sure you want to delete this file? This cannot be undone.')) return;

                    fetch(`/api/media/${asset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.assets = this.assets.filter(a => a.id !== asset.id);
                        }
                    });
                },

                copyUrl(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        // Ideally show a toast
                        alert('URL copied to clipboard!');
                    });
                },

                formatSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                }
            }
        }
    </script>
</x-app-layout>
