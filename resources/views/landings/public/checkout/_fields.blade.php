{{-- Shared Form Fields Partial --}}
<div class="grid grid-cols-6 gap-6">
    @forelse($checkoutFields as $field)
        <div class="col-span-6 {{ in_array($field->field_name, ['first_name', 'last_name']) ? 'sm:col-span-3' : '' }}">
            <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $field->label ?: ucwords(str_replace('_', ' ', $field->field_name)) }}
                @if($field->is_required) <span class="text-red-500">*</span> @endif
            </label>
            <input type="text" name="{{ $field->field_name }}" id="{{ $field->field_name }}" 
                {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
        </div>
    @empty
        <div class="col-span-6">
            <p class="text-sm text-gray-500">No fields configured.</p>
        </div>
    @endforelse
</div>
