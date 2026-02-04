<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\TemplatePage;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SaaS Startup Template
        $saas = Template::firstOrCreate(
            ['name' => 'SaaS Startup'],
            [
                'description' => 'A modern, high-conversion template for SaaS products.',
                'preview_image_path' => '/images/templates/saas-startup.jpg',
                'is_active' => true,
            ]
        );

        TemplatePage::firstOrCreate(
            ['template_id' => $saas->id, 'slug' => 'index'],
            [
                'type' => 'index',
                'name' => 'Home',
                'html' => '<div class="bg-gray-50 min-h-screen flex flex-col items-center justify-center">
                    <h1 class="text-4xl font-bold text-gray-900">Welcome to SaaS Startup</h1>
                    <p class="mt-4 text-xl text-gray-600">Build your future with us.</p>
                    <a href="checkout" class="mt-8 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Get Started</a>
                </div>',
                'css' => '',
                'js' => '',
            ]
        );

        TemplatePage::firstOrCreate(
            ['template_id' => $saas->id, 'slug' => 'checkout'],
            [
                'type' => 'checkout',
                'name' => 'Checkout',
                'html' => '<div class="bg-white min-h-screen flex flex-col items-center justify-center">
                    <h1 class="text-3xl font-bold">Checkout</h1>
                    <p>Complete your purchase.</p>
                </div>',
            ]
        );

        TemplatePage::firstOrCreate(
            ['template_id' => $saas->id, 'slug' => 'thank-you'],
            [
                'type' => 'thankyou',
                'name' => 'Thank You',
                'html' => '<div class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4">
                    <div class="bg-white p-8 rounded-2xl shadow-xl text-center max-w-md w-full border border-gray-100">
                        <div class="mb-6">
                             <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                             </div>
                             <h1 class="text-3xl font-extrabold text-gray-900">Thank You!</h1>
                             <p class="mt-2 text-gray-500">Your order has been successfully placed. We have sent a confirmation email to your inbox.</p>
                        </div>
                        <div class="border-t border-gray-100 pt-6">
                            <p class="text-sm text-gray-400">Order ID: <span class="font-mono text-gray-600">#{{ request()->get("lead") ?? "12345" }}</span></p>
                            <a href="/" class="mt-6 inline-block w-full px-6 py-3 bg-gray-900 text-white font-bold rounded-lg hover:bg-gray-800 transition-colors">Return Home</a>
                        </div>
                        <!-- Download Invoice button will be injected automatically -->
                    </div>
                </div>',
            ]
        );


        // 2. E-book Launch Template
        $ebook = Template::firstOrCreate(
            ['name' => 'E-book Launch'],
            [
                'description' => 'Perfect for selling digital products and e-books.',
                'preview_image_path' => '/images/templates/ebook-launch.jpg',
                'is_active' => true,
            ]
        );

        TemplatePage::firstOrCreate(
            ['template_id' => $ebook->id, 'slug' => 'index'],
            [
                'type' => 'index',
                'name' => 'Home',
                'html' => '<div class="bg-indigo-900 min-h-screen flex flex-col items-center justify-center text-white">
                    <h1 class="text-5xl font-extrabold">Ultimate Guide to Success</h1>
                    <p class="mt-6 text-2xl text-indigo-200">Get the e-book now.</p>
                    <button class="mt-8 px-8 py-4 bg-yellow-500 text-gray-900 font-bold rounded-full hover:bg-yellow-400">Buy Now $19</button>
                </div>',
            ]
        );

        // 3. My Custom Template (User Content)
        $custom = Template::firstOrCreate(
            ['name' => 'My Custom Template'],
            [
                'description' => 'A blank canvas for your own custom HTML/CSS.',
                'preview_image_path' => 'https://placehold.co/600x400?text=Custom+Template', // Placeholder
                'is_active' => true,
            ]
        );

        TemplatePage::firstOrCreate(
            ['template_id' => $custom->id, 'slug' => 'index'],
            [
                'type' => 'index',
                'name' => 'Home',
                'html' => '<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
                    <h1 class="text-4xl font-bold">Insert Your HTML Here</h1>
                    <p class="mt-4">Edit database/seeders/TemplateSeeder.php to customize this content.</p>
                </div>',
            ]
        );
        
        TemplatePage::firstOrCreate(
            ['template_id' => $custom->id, 'slug' => 'checkout'],
            [
                'type' => 'checkout',
                'name' => 'Checkout',
                'html' => '<div class="min-h-screen flex items-center justify-center"><h1>Custom Checkout Page</h1></div>',
            ]
        );
    }
}
