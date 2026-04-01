/**
 * Plugin: Conversion Blocks
 *
 * Registers ready-to-use, high-converting landing page section blocks
 * using TailwindCSS. Grouped by category for the block manager sidebar.
 *
 * Categories:
 *   - Hero Sections
 *   - Testimonials
 *   - FAQ / Accordion
 *   - Pricing
 *   - Trust / Social Proof
 *   - CTA Sections
 *   - Features
 */
export default function conversionBlocksPlugin(editor, opts = {}) {

    const bm = editor.BlockManager;

    // ═══════════════════════════════════════════════════════════════
    //  HERO SECTIONS
    // ═══════════════════════════════════════════════════════════════

    bm.add('hero-centered', {
        label: 'Hero — Centered',
        category: 'Hero',
        attributes: { class: 'gjs-fonts gjs-f-hero-c' },
        content: `
            <section class="relative py-24 px-6 bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white overflow-hidden">
                <div class="max-w-4xl mx-auto text-center relative z-10">
                    <span class="inline-block px-4 py-1.5 mb-6 text-xs font-bold uppercase tracking-widest text-indigo-300 bg-indigo-500/10 rounded-full border border-indigo-500/20">Limited Time Offer</span>
                    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-6 tracking-tight">Transform Your Results<br class="hidden md:block"> <span class="text-indigo-400">Starting Today</span></h1>
                    <p class="text-lg md:text-xl text-gray-300 max-w-2xl mx-auto mb-10 leading-relaxed">Discover the proven system that's helped 10,000+ customers achieve incredible results. No risk, no commitment, full guarantee.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        <a href="#" class="cta inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-lg rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
                            Get Started Now
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white text-sm font-medium underline underline-offset-4 transition-colors">See how it works →</a>
                    </div>
                    <p class="mt-6 text-xs text-gray-500">✓ Free shipping · ✓ 30-day guarantee · ✓ Cancel anytime</p>
                </div>
            </section>
        `,
    });

    bm.add('hero-split', {
        label: 'Hero — Split',
        category: 'Hero',
        attributes: { class: 'gjs-fonts gjs-f-hero-s' },
        content: `
            <section class="py-20 px-6 bg-white">
                <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="inline-block px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 rounded-full mb-4">⭐ #1 Best Seller</span>
                        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight mb-6">The Only Product You'll Ever Need</h1>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">Stop wasting money on solutions that don't work. Our proven formula delivers visible results in just 14 days — or your money back.</p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Clinically tested ingredients</li>
                            <li class="flex items-center gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Free shipping worldwide</li>
                            <li class="flex items-center gap-3 text-gray-700"><svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 30-day money-back guarantee</li>
                        </ul>
                        <a href="#" class="cta inline-flex items-center gap-2 px-8 py-4 bg-gray-900 hover:bg-gray-800 text-white font-bold rounded-xl transition-all shadow-lg hover:-translate-y-0.5">
                            Order Now — 40% Off
                        </a>
                        <p class="mt-4 text-sm text-gray-400">⏰ Sale ends in 24 hours</p>
                    </div>
                    <div class="relative">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=600&fit=crop" alt="Product" class="rounded-2xl shadow-2xl w-full object-cover"/>
                    </div>
                </div>
            </section>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  TESTIMONIALS
    // ═══════════════════════════════════════════════════════════════

    bm.add('testimonials-grid', {
        label: 'Testimonials Grid',
        category: 'Testimonials',
        content: `
            <section class="py-20 px-6 bg-gray-50">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">What Our Customers Say</h2>
                        <p class="text-lg text-gray-500 max-w-2xl mx-auto">Join 10,000+ satisfied customers who transformed their lives</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-1 mb-4">
                                <span class="text-yellow-400 text-lg">★★★★★</span>
                            </div>
                            <p class="text-gray-600 mb-6 leading-relaxed">"I've tried dozens of alternatives and nothing comes close. The results were visible within the first week. Absolutely incredible product."</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">SM</div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Sarah M.</p>
                                    <p class="text-xs text-gray-400">Verified Buyer</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-1 mb-4">
                                <span class="text-yellow-400 text-lg">★★★★★</span>
                            </div>
                            <p class="text-gray-600 mb-6 leading-relaxed">"Game changer! I was skeptical at first but now I can't imagine going back. The quality is outstanding and customer service is top-notch."</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-sm">JD</div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">James D.</p>
                                    <p class="text-xs text-gray-400">Verified Buyer</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-1 mb-4">
                                <span class="text-yellow-400 text-lg">★★★★★</span>
                            </div>
                            <p class="text-gray-600 mb-6 leading-relaxed">"Best purchase I've made this year. The value for money is unmatched. Already recommended it to all my friends and family."</p>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-sm">LR</div>
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">Lisa R.</p>
                                    <p class="text-xs text-gray-400">Verified Buyer</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  FAQ / ACCORDION
    // ═══════════════════════════════════════════════════════════════

    bm.add('faq-section', {
        label: 'FAQ Accordion',
        category: 'FAQ',
        content: `
            <section class="py-20 px-6 bg-white">
                <div class="max-w-3xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Frequently Asked Questions</h2>
                        <p class="text-lg text-gray-500">Everything you need to know before ordering</p>
                    </div>
                    <div class="space-y-4">
                        <details class="group bg-gray-50 rounded-xl border border-gray-200 overflow-hidden" open>
                            <summary class="flex items-center justify-between cursor-pointer px-6 py-5 font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                How quickly will I see results?
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-5 text-gray-600 leading-relaxed">Most customers report visible improvements within 7-14 days of consistent use. Individual results may vary based on your specific situation.</div>
                        </details>
                        <details class="group bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                            <summary class="flex items-center justify-between cursor-pointer px-6 py-5 font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                What's your return policy?
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-5 text-gray-600 leading-relaxed">We offer a 30-day money-back guarantee. If you're not satisfied, simply contact us and we'll process a full refund — no questions asked.</div>
                        </details>
                        <details class="group bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                            <summary class="flex items-center justify-between cursor-pointer px-6 py-5 font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                Is shipping really free?
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-5 text-gray-600 leading-relaxed">Yes! We offer free standard shipping on all orders. Express and international shipping options are available at checkout for a small fee.</div>
                        </details>
                        <details class="group bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                            <summary class="flex items-center justify-between cursor-pointer px-6 py-5 font-semibold text-gray-900 hover:text-indigo-600 transition-colors">
                                How do I contact support?
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="px-6 pb-5 text-gray-600 leading-relaxed">Our support team is available 24/7. Reach us via email at support@example.com or through the live chat button on this page.</div>
                        </details>
                    </div>
                </div>
            </section>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  PRICING
    // ═══════════════════════════════════════════════════════════════

    bm.add('pricing-cards', {
        label: 'Pricing Cards',
        category: 'Pricing',
        content: `
            <section class="py-20 px-6 bg-gray-900 text-white">
                <div class="max-w-5xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Choose Your Bundle</h2>
                        <p class="text-gray-400 text-lg">Save more when you buy more — all bundles include free shipping</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 hover:border-gray-600 transition-colors">
                            <h3 class="text-lg font-bold mb-2">Starter</h3>
                            <p class="text-sm text-gray-400 mb-6">1x Bottle</p>
                            <div class="mb-6">
                                <span class="text-4xl font-extrabold">$39</span>
                                <span class="text-gray-500 line-through ml-2">$59</span>
                            </div>
                            <a href="#" class="cta block w-full text-center py-3 px-6 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-xl transition-colors">
                                Add to Cart
                            </a>
                        </div>
                        <div class="bg-indigo-600 rounded-2xl p-8 border-2 border-indigo-400 relative shadow-xl shadow-indigo-500/20 scale-105">
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-400 text-gray-900 text-xs font-extrabold px-4 py-1 rounded-full uppercase tracking-wider">Most Popular</span>
                            <h3 class="text-lg font-bold mb-2">Best Value</h3>
                            <p class="text-sm text-indigo-200 mb-6">3x Bottles — Save 40%</p>
                            <div class="mb-6">
                                <span class="text-4xl font-extrabold">$89</span>
                                <span class="text-indigo-300 line-through ml-2">$177</span>
                            </div>
                            <a href="#" class="cta block w-full text-center py-3 px-6 bg-white text-indigo-700 font-bold rounded-xl hover:bg-gray-100 transition-colors shadow-lg">
                                Add to Cart
                            </a>
                        </div>
                        <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700 hover:border-gray-600 transition-colors">
                            <h3 class="text-lg font-bold mb-2">Family Pack</h3>
                            <p class="text-sm text-gray-400 mb-6">5x Bottles — Save 55%</p>
                            <div class="mb-6">
                                <span class="text-4xl font-extrabold">$129</span>
                                <span class="text-gray-500 line-through ml-2">$295</span>
                            </div>
                            <a href="#" class="cta block w-full text-center py-3 px-6 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-xl transition-colors">
                                Add to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  TRUST / SOCIAL PROOF
    // ═══════════════════════════════════════════════════════════════

    bm.add('trust-badges', {
        label: 'Trust Badges Row',
        category: 'Trust & Proof',
        content: `
            <section class="py-8 px-6 bg-gray-50 border-y border-gray-200">
                <div class="max-w-5xl mx-auto flex flex-wrap justify-center items-center gap-8 md:gap-16">
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-10 h-10 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Secure Payment</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-10 h-10 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Free Shipping</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-10 h-10 text-amber-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">30-Day Returns</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <svg class="w-10 h-10 text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">10,000+ Reviews</span>
                    </div>
                </div>
            </section>
        `,
    });

    bm.add('social-proof-bar', {
        label: 'Social Proof Bar',
        category: 'Trust & Proof',
        content: `
            <section class="py-6 px-6 bg-indigo-600 text-white">
                <div class="max-w-6xl mx-auto flex flex-wrap justify-center items-center gap-8 md:gap-16 text-center">
                    <div>
                        <p class="text-3xl font-extrabold">10K+</p>
                        <p class="text-xs uppercase tracking-wider text-indigo-200 mt-1">Happy Customers</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold">4.9/5</p>
                        <p class="text-xs uppercase tracking-wider text-indigo-200 mt-1">Average Rating</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold">50+</p>
                        <p class="text-xs uppercase tracking-wider text-indigo-200 mt-1">Countries Served</p>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold">99%</p>
                        <p class="text-xs uppercase tracking-wider text-indigo-200 mt-1">Satisfaction Rate</p>
                    </div>
                </div>
            </section>
        `,
    });

    bm.add('guarantee-block', {
        label: 'Guarantee Badge',
        category: 'Trust & Proof',
        content: `
            <section class="py-16 px-6 bg-white">
                <div class="max-w-2xl mx-auto text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-emerald-50 rounded-full mb-6">
                        <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-gray-900 mb-4">100% Money-Back Guarantee</h3>
                    <p class="text-gray-600 leading-relaxed max-w-xl mx-auto">We're so confident you'll love our product that we offer a full 30-day refund guarantee. Try it risk-free — if you're not 100% satisfied, contact us and we'll refund every penny. No hassle, no questions asked.</p>
                </div>
            </section>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  CTA SECTIONS
    // ═══════════════════════════════════════════════════════════════

    bm.add('cta-banner', {
        label: 'CTA Banner',
        category: 'Call to Action',
        content: `
            <section class="py-16 px-6 bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
                <div class="max-w-4xl mx-auto text-center">
                    <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Ready to Transform Your Life?</h2>
                    <p class="text-lg text-indigo-100 mb-8 max-w-2xl mx-auto">Join thousands of happy customers. Order now and save 40% — limited time only.</p>
                    <a href="#" class="cta inline-flex items-center gap-2 px-10 py-4 bg-white text-indigo-700 font-bold text-lg rounded-xl hover:bg-gray-100 transition-all shadow-xl hover:-translate-y-0.5">
                        Claim Your Discount
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </section>
        `,
    });

    bm.add('sticky-cta-bar', {
        label: 'Sticky CTA Bar',
        category: 'Call to Action',
        content: `
            <div class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-sm border-t border-gray-700 py-3 px-4 shadow-2xl" data-sticky-cta="true">
                <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
                    <div class="hidden sm:block">
                        <p class="text-white font-bold text-sm">🔥 Limited Time — 40% Off</p>
                        <p class="text-gray-400 text-xs">Free shipping on all orders today</p>
                    </div>
                    <a href="#" class="cta flex-shrink-0 inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-all shadow-lg">
                        Order Now — Save 40%
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        `,
    });

    // ═══════════════════════════════════════════════════════════════
    //  FEATURES
    // ═══════════════════════════════════════════════════════════════

    bm.add('features-grid', {
        label: 'Features Grid',
        category: 'Features',
        content: `
            <section class="py-20 px-6 bg-white">
                <div class="max-w-6xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Why Choose Us?</h2>
                        <p class="text-lg text-gray-500 max-w-2xl mx-auto">Built different. Designed better. Proven results.</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="text-center p-8 rounded-2xl hover:bg-gray-50 transition-colors">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-xl mb-5">
                                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Lightning Fast</h3>
                            <p class="text-gray-500 leading-relaxed">See results in as little as 7 days. Our formula is engineered for maximum speed and effectiveness.</p>
                        </div>
                        <div class="text-center p-8 rounded-2xl hover:bg-gray-50 transition-colors">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 rounded-xl mb-5">
                                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Proven Quality</h3>
                            <p class="text-gray-500 leading-relaxed">Backed by science and tested by thousands. We only use the highest quality ingredients.</p>
                        </div>
                        <div class="text-center p-8 rounded-2xl hover:bg-gray-50 transition-colors">
                            <div class="inline-flex items-center justify-center w-14 h-14 bg-amber-100 rounded-xl mb-5">
                                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">Customer Love</h3>
                            <p class="text-gray-500 leading-relaxed">4.9/5 stars from 10,000+ reviews. Our customers don't just buy — they recommend.</p>
                        </div>
                    </div>
                </div>
            </section>
        `,
    });

    bm.add('comparison-table', {
        label: 'Comparison Table',
        category: 'Features',
        content: `
            <section class="py-20 px-6 bg-gray-50">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-10">How We Compare</h2>
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-6 py-4 text-sm font-bold text-gray-500 uppercase">Feature</th>
                                    <th class="px-6 py-4 text-sm font-bold text-indigo-600 uppercase text-center">Us</th>
                                    <th class="px-6 py-4 text-sm font-bold text-gray-400 uppercase text-center">Others</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">Premium Ingredients</td>
                                    <td class="px-6 py-4 text-center text-emerald-500 text-lg">✓</td>
                                    <td class="px-6 py-4 text-center text-red-400 text-lg">✗</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">Money-Back Guarantee</td>
                                    <td class="px-6 py-4 text-center text-emerald-500 text-lg">✓</td>
                                    <td class="px-6 py-4 text-center text-red-400 text-lg">✗</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">Free Shipping</td>
                                    <td class="px-6 py-4 text-center text-emerald-500 text-lg">✓</td>
                                    <td class="px-6 py-4 text-center text-red-400 text-lg">✗</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 font-medium">24/7 Support</td>
                                    <td class="px-6 py-4 text-center text-emerald-500 text-lg">✓</td>
                                    <td class="px-6 py-4 text-center text-gray-400 text-sm">Limited</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `,
    });

    console.log('[GrapesJS] Conversion Blocks plugin loaded. (' +
        bm.getAll().filter(b => ['Hero','Testimonials','FAQ','Pricing','Trust & Proof','Call to Action','Features'].includes(b.getCategoryLabel())).length +
        ' blocks added)');
}
