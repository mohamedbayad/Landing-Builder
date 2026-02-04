<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateThankYouPageSeeder extends Seeder
{
    public function run()
    {
        // New HTML Template with IDs for JS injection
        $html = '
<div class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Thank you for your order!</h2>
            <p class="mt-2 text-lg text-gray-600">Your order has been placed successfully.</p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Order Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Details about your purchase.</p>
                </div>
                <div>
                     <span id="crm-status" class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Completed
                    </span>
                </div>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd id="crm-fullname" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">John Doe</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email address</dt>
                        <dd id="crm-email" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">john@example.com</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Phone</dt>
                         <dd id="crm-phone" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">+1 234 567 8900</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                        <dt class="text-sm font-medium text-gray-500">Shipping Address</dt>
                        <dd id="crm-address" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            123 Main St, New York 10001, USA
                        </dd>
                    </div>

                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                        <dd id="crm-order-id" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">ORD-12345-XYZ</dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Date Placed</dt>
                         <dd id="crm-date" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">January 1, 2026, 12:00 am</dd>
                    </div>
                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                         <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                         <dd id="crm-payment" class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Credit Card</dd>
                    </div>

                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-t border-gray-200">
                        <dt class="text-sm font-medium text-gray-500 self-center">Item Purchased</dt>
                         <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="flex justify-between font-medium">
                                <span id="crm-product">Premium Plan</span>
                                <span id="crm-amount">USD 99.00</span>
                            </div>
                        </dd>
                    </div>
                     <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
                        <dt class="text-base font-bold text-gray-900 self-center">Total Paid</dt>
                        <dd class="mt-1 text-base font-bold text-gray-900 sm:mt-0 sm:col-span-2 flex justify-end" id="crm-total">
                            USD 99.00
                        </dd>
                    </div>

                </dl>
            </div>
             <div class="bg-gray-50 px-4 py-4 sm:px-6 flex justify-end space-x-3">
                 <a id="crm-invoice-btn" href="#" onclick="return false;" class="inline-flex items-center px-4 py-2 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Invoice
                 </a>
                 <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Return Home
                 </a>
            </div>
        </div>
    </div>
</div>';

        // Update all Thank You pages (simplification: updating ID 12 as per user context, or all 'thankyou' types)
        // Updating all thank you pages to ensure consistency
        DB::table('landing_pages')
            ->where('type', 'thankyou')
            ->update(['html' => $html]);
    }
}
