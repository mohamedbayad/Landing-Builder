<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ $automation->exists ? 'Edit Automation' : 'Create Automation' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')
            <form action="{{ $action }}" method="POST" class="space-y-6">
                @csrf
                @if($method !== 'POST')
                    @method($method)
                @endif

                @if($errors->any())
                    <div class="rounded-lg bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-4 py-3 text-sm border border-red-100 dark:border-red-900/50">
                        <ul class="list-disc ml-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6 space-y-8">
                    <div class="pb-6 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">General</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Define the automation identity, trigger source, and operating status.</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Name</label>
                                <input type="text" name="name" value="{{ old('name', $automation->name) }}" required
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                    @foreach(['draft','active','paused'] as $status)
                                        <option value="{{ $status }}" @selected(old('status', $automation->status) === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Trigger</label>
                                <select name="trigger_type" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                    @foreach(['form_submitted','checkout_completed','lead_created'] as $trigger)
                                        <option value="{{ $trigger }}" @selected(old('trigger_type', $automation->trigger_type) === $trigger)>{{ str_replace('_', ' ', $trigger) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                                <input type="text" name="timezone" value="{{ old('timezone', $automation->timezone ?: config('app.timezone')) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="pb-6 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Trigger Config (Optional)</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use filters to scope this automation to specific landing, endpoint, or product contexts.</p>
                        @php
                            $config = old('trigger_config', $automation->trigger_config ?? []);
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Landing</label>
                                <select name="trigger_config[landing_id]" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                    <option value="">Any Landing</option>
                                    @foreach($landings as $landing)
                                        <option value="{{ $landing->id }}" @selected((string)($config['landing_id'] ?? '') === (string)$landing->id)>{{ $landing->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Form Endpoint</label>
                                <select name="trigger_config[form_endpoint_id]" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                    <option value="">Any Endpoint</option>
                                    @foreach($formEndpoints as $endpoint)
                                        <option value="{{ $endpoint->id }}" @selected((string)($config['form_endpoint_id'] ?? '') === (string)$endpoint->id)>{{ $endpoint->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Product</label>
                                <select name="trigger_config[product_id]" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                    <option value="">Any Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @selected((string)($config['product_id'] ?? '') === (string)$product->id)>{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sequence Steps</h3>
                            <button type="button"
                                    id="add-step-btn"
                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-100 dark:bg-white/[0.06] text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-white/[0.12]">
                                Add Step
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mix `send_email` and `wait` steps to build your follow-up sequence.</p>
                        @php
                            $steps = old('steps', $automation->exists ? $automation->steps->toArray() : [['step_type' => 'send_email', 'template_id' => null]]);
                        @endphp
                        <div id="steps-container" class="space-y-3 mt-3">
                            @foreach($steps as $index => $step)
                                <div class="step-item rounded-xl border border-gray-200 dark:border-white/[0.06] p-4 bg-gray-50 dark:bg-white/[0.02]">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Step Type</label>
                                            <select name="steps[{{ $index }}][step_type]"
                                                    class="step-type w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                                <option value="send_email" @selected(($step['step_type'] ?? 'send_email') === 'send_email')>Send Email</option>
                                                <option value="wait" @selected(($step['step_type'] ?? '') === 'wait')>Wait</option>
                                            </select>
                                        </div>
                                        <div class="template-col {{ ($step['step_type'] ?? 'send_email') === 'send_email' ? '' : 'hidden' }}">
                                            <label class="block text-xs text-gray-500 mb-1">Template</label>
                                            <select name="steps[{{ $index }}][template_id]"
                                                    class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                                <option value="">Select template</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}" @selected((string)($step['template_id'] ?? '') === (string)$template->id)>{{ $template->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="delay-value-col {{ ($step['step_type'] ?? 'send_email') === 'wait' ? '' : 'hidden' }}">
                                            <label class="block text-xs text-gray-500 mb-1">Delay Value</label>
                                            <input type="number"
                                                   min="0"
                                                   name="steps[{{ $index }}][delay_value]"
                                                   value="{{ $step['delay_value'] ?? '' }}"
                                                   class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                        </div>
                                        <div class="delay-unit-col {{ ($step['step_type'] ?? 'send_email') === 'wait' ? '' : 'hidden' }}">
                                            <label class="block text-xs text-gray-500 mb-1">Delay Unit</label>
                                            <select name="steps[{{ $index }}][delay_unit]" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                                                @foreach(['minutes','hours','days'] as $unit)
                                                    <option value="{{ $unit }}" @selected(($step['delay_unit'] ?? 'minutes') === $unit)>{{ ucfirst($unit) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-right">
                                        <button type="button" class="remove-step text-xs font-semibold px-2.5 py-1 rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('email-automation.automations.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 dark:border-white/[0.06] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 shadow-sm transition-colors">
                        Save Automation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <template id="step-template">
        <div class="step-item rounded-xl border border-gray-200 dark:border-white/[0.06] p-4 bg-gray-50 dark:bg-white/[0.02]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Step Type</label>
                    <select data-name="step_type" class="step-type w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                        <option value="send_email">Send Email</option>
                        <option value="wait">Wait</option>
                    </select>
                </div>
                <div class="template-col">
                    <label class="block text-xs text-gray-500 mb-1">Template</label>
                    <select data-name="template_id" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                        <option value="">Select template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="delay-value-col hidden">
                    <label class="block text-xs text-gray-500 mb-1">Delay Value</label>
                    <input type="number" min="0" data-name="delay_value" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                </div>
                <div class="delay-unit-col hidden">
                    <label class="block text-xs text-gray-500 mb-1">Delay Unit</label>
                    <select data-name="delay_unit" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white">
                        <option value="minutes">Minutes</option>
                        <option value="hours">Hours</option>
                        <option value="days">Days</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 text-right">
                <button type="button" class="remove-step text-xs font-semibold px-2.5 py-1 rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">Remove</button>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('steps-container');
            const template = document.getElementById('step-template');
            const addBtn = document.getElementById('add-step-btn');

            const applyStepMode = (item) => {
                const type = item.querySelector('.step-type').value;
                item.querySelector('.template-col').classList.toggle('hidden', type !== 'send_email');
                item.querySelector('.delay-value-col').classList.toggle('hidden', type !== 'wait');
                item.querySelector('.delay-unit-col').classList.toggle('hidden', type !== 'wait');
            };

            const reindex = () => {
                Array.from(container.querySelectorAll('.step-item')).forEach((item, index) => {
                    item.querySelectorAll('[name], [data-name]').forEach((field) => {
                        const base = field.getAttribute('data-name');
                        if (base) {
                            field.setAttribute('name', `steps[${index}][${base}]`);
                        } else {
                            const current = field.getAttribute('name');
                            if (!current) return;
                            const key = current.substring(current.lastIndexOf('[') + 1, current.length - 1);
                            field.setAttribute('name', `steps[${index}][${key}]`);
                        }
                    });
                });
            };

            addBtn.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                container.appendChild(fragment);
                reindex();
            });

            container.addEventListener('change', (event) => {
                if (event.target.classList.contains('step-type')) {
                    applyStepMode(event.target.closest('.step-item'));
                }
            });

            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('remove-step')) {
                    event.target.closest('.step-item').remove();
                    reindex();
                }
            });

            Array.from(container.querySelectorAll('.step-item')).forEach((item) => applyStepMode(item));
            reindex();
        });
    </script>
</x-app-layout>
