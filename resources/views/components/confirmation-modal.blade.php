<div id="confirmation-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="window.closeConfirmationModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-bottom bg-white dark:bg-[#161B22] rounded-xl text-left overflow-hidden shadow-dropdown border border-gray-100 dark:border-white/[0.06] transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-50 dark:bg-red-500/10">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white" id="confirmation-modal-title">
                            Confirm Action
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="confirmation-modal-message">
                            Are you sure you want to proceed?
                        </p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-white/[0.06] flex items-center justify-end gap-3">
                <button type="button" onclick="window.closeConfirmationModal()"
                        class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/8 transition-all">
                    Cancel
                </button>
                <button type="button" id="confirmation-modal-confirm-btn"
                        class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/30 transition-all shadow-sm">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.confirmationModalCallbacks = { onConfirm: null };

    window.confirmAction = function(message, target) {
        document.getElementById('confirmation-modal-message').innerText = message;
        document.getElementById('confirmation-modal').classList.remove('hidden');

        const confirmBtn = document.getElementById('confirmation-modal-confirm-btn');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

        newConfirmBtn.addEventListener('click', function() {
            if (target instanceof HTMLFormElement) {
                target.submit();
            } else if (typeof target === 'function') {
                target();
            }
            window.closeConfirmationModal();
        });

        return false;
    }

    window.closeConfirmationModal = function() {
        document.getElementById('confirmation-modal').classList.add('hidden');
    }
</script>
