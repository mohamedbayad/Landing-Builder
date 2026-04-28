/**
 * Plugin: Conversion Blocks
 *
 * Rebuilt block catalog for Tailwind-first page building.
 * Categories:
 * - Conversion
 * - Components
 * - Media
 */
export default function conversionBlocksPlugin(editor) {
    if (editor.__tailwindBlocksCatalogReady) {
        return;
    }
    editor.__tailwindBlocksCatalogReady = true;

    const blockManager = editor.BlockManager;

    const upsertBlock = (id, config) => {
        if (blockManager.get(id)) {
            blockManager.remove(id);
        }
        blockManager.add(id, config);
    };

    upsertBlock('newsletter-signup', {
        label: 'Newsletter',
        category: 'Conversion',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"></path>
        </svg>`,
        content: `
            <div class="rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 p-8">
                <h3 class="mb-4 text-2xl font-bold text-white">Subscribe to our Newsletter</h3>
                <p class="mb-6 text-white/90">Get the latest updates and exclusive offers delivered to your inbox.</p>
                <form class="flex flex-col gap-4 sm:flex-row">
                    <input type="email" placeholder="Enter your email" class="flex-1 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-white" />
                    <button type="submit" class="rounded-lg bg-white px-6 py-3 font-semibold text-blue-600 transition-colors duration-300 hover:bg-gray-100">
                        Subscribe
                    </button>
                </form>
            </div>
        `,
    });

    upsertBlock('pricing-table', {
        label: 'Pricing',
        category: 'Conversion',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <text x="12" y="10" text-anchor="middle" font-size="8" fill="currentColor" font-weight="bold">$29</text>
            <line x1="6" y1="13" x2="18" y2="13" stroke="currentColor" stroke-width="1"></line>
            <line x1="6" y1="16" x2="18" y2="16" stroke="currentColor" stroke-width="1"></line>
            <line x1="6" y1="19" x2="18" y2="19" stroke="currentColor" stroke-width="1"></line>
        </svg>`,
        content: `
            <div class="max-w-sm rounded-lg border-2 border-blue-500 bg-white p-8 shadow-xl">
                <div class="text-center">
                    <h3 class="mb-2 text-2xl font-bold text-gray-900">Pro Plan</h3>
                    <div class="mb-4">
                        <span class="text-5xl font-bold text-blue-600">$29</span>
                        <span class="text-gray-600">/month</span>
                    </div>
                    <p class="mb-6 text-gray-600">Perfect for growing businesses</p>
                </div>
                <ul class="mb-8 space-y-3">
                    <li class="flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Unlimited Projects
                    </li>
                    <li class="flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Priority Support
                    </li>
                    <li class="flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Advanced Analytics
                    </li>
                    <li class="flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Custom Domain
                    </li>
                </ul>
                <button class="w-full rounded-lg bg-blue-600 px-4 py-3 font-bold text-white transition-colors hover:bg-blue-700">
                    Choose Plan
                </button>
            </div>
        `,
    });

    upsertBlock('testimonial', {
        label: 'Testimonial',
        category: 'Conversion',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z" fill="currentColor"></path>
            <circle cx="6" cy="6" r="2" fill="currentColor" opacity="0.5"></circle>
        </svg>`,
        content: `
            <div class="max-w-2xl rounded-lg bg-white p-8 shadow-md">
                <div class="mb-4 flex items-center">
                    <img src="https://via.placeholder.com/80" alt="User" class="mr-4 h-16 w-16 rounded-full" />
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">John Smith</h4>
                        <p class="text-gray-600">CEO, Tech Company</p>
                    </div>
                </div>
                <div class="mb-4 flex">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <p class="italic text-gray-700">"This product has completely transformed how we work. The interface is intuitive and the results are outstanding. Highly recommended for anyone looking to streamline their workflow."</p>
            </div>
        `,
    });

    upsertBlock('image-block', {
        label: 'Image',
        category: 'Media',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"></circle>
            <path d="M3 16l5-5 2 2 4-4 7 7V3H3z" fill="currentColor" opacity="0.5"></path>
        </svg>`,
        content: `
            <img src="https://via.placeholder.com/800x400" alt="Placeholder" class="h-auto w-full rounded-lg shadow-md" />
        `,
    });

    upsertBlock('video-block', {
        label: 'Video',
        category: 'Media',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="4" width="20" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <path d="M10 8l6 4-6 4z" fill="currentColor"></path>
        </svg>`,
        content: `
            <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-gray-900">
                <iframe
                    class="h-full w-full"
                    src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
        `,
    });

    upsertBlock('card', {
        label: 'Card',
        category: 'Components',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <rect x="3" y="3" width="18" height="8" fill="currentColor" opacity="0.3"></rect>
            <line x1="6" y1="14" x2="18" y2="14" stroke="currentColor" stroke-width="1.5"></line>
            <line x1="6" y1="17" x2="14" y2="17" stroke="currentColor" stroke-width="1.5"></line>
        </svg>`,
        content: `
            <div class="max-w-sm overflow-hidden rounded-lg bg-white shadow-lg">
                <img src="https://via.placeholder.com/400x200" alt="Card" class="h-48 w-full object-cover" />
                <div class="p-6">
                    <h3 class="mb-2 text-xl font-bold text-gray-900">Card Title</h3>
                    <p class="mb-4 text-gray-700">This is a card component with an image, title, description, and action button.</p>
                    <a href="#" class="inline-block rounded bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                        Learn More
                    </a>
                </div>
            </div>
        `,
    });

    upsertBlock('hero-section', {
        label: 'Hero',
        category: 'Components',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="4" width="20" height="16" rx="2" fill="currentColor" opacity="0.2"></rect>
            <text x="12" y="12" text-anchor="middle" font-size="6" fill="currentColor" font-weight="bold">HERO</text>
            <circle cx="8" cy="17" r="1.5" fill="currentColor"></circle>
            <circle cx="16" cy="17" r="1.5" fill="currentColor"></circle>
        </svg>`,
        content: `
            <section class="relative bg-gradient-to-r from-blue-600 to-purple-700 px-6 py-20 text-white">
                <div class="container mx-auto max-w-4xl text-center">
                    <h1 class="mb-6 text-5xl font-bold md:text-6xl">Welcome to Our Amazing Product</h1>
                    <p class="mb-8 text-xl text-white/90 md:text-2xl">Create stunning websites with ease using our powerful page builder</p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <a href="#" class="rounded-lg bg-white px-8 py-4 font-semibold text-blue-600 transition-colors hover:bg-gray-100">
                            Get Started
                        </a>
                        <a href="#" class="rounded-lg border-2 border-white bg-transparent px-8 py-4 font-semibold text-white transition-colors hover:bg-white/10">
                            Learn More
                        </a>
                    </div>
                </div>
            </section>
        `,
    });

    upsertBlock('features-grid', {
        label: 'Features',
        category: 'Components',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="3" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
            <rect x="9" y="3" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
            <rect x="16" y="3" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
            <rect x="2" y="10" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
            <rect x="9" y="10" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
            <rect x="16" y="10" width="6" height="6" rx="1" fill="currentColor" opacity="0.5"></rect>
        </svg>`,
        content: `
            <div class="px-6 py-12">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-12 text-center">
                        <h2 class="mb-4 text-4xl font-bold text-gray-900">Amazing Features</h2>
                        <p class="text-xl text-gray-600">Everything you need to build stunning websites</p>
                    </div>
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="p-6 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-gray-900">Lightning Fast</h3>
                            <p class="text-gray-600">Optimized for speed and performance</p>
                        </div>
                        <div class="p-6 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-gray-900">Secure</h3>
                            <p class="text-gray-600">Enterprise-grade security built-in</p>
                        </div>
                        <div class="p-6 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-purple-100">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-gray-900">Responsive</h3>
                            <p class="text-gray-600">Perfect on all devices and screens</p>
                        </div>
                    </div>
                </div>
            </div>
        `,
    });
}
