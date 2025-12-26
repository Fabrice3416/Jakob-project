/**
 * Payment Method Modal
 * Handles adding payment methods
 */

function showAddPaymentMethodModal() {
    const modalHTML = `
        <div id="paymentMethodModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) closePaymentMethodModal()">
            <div class="bg-surface-dark rounded-3xl w-full max-w-md border border-white/10">
                <!-- Header -->
                <div class="bg-surface-dark border-b border-white/10 p-6 flex items-center justify-between rounded-t-3xl">
                    <h2 class="text-xl font-bold text-white">Add Payment Method</h2>
                    <button onclick="closePaymentMethodModal()" class="size-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form id="paymentMethodForm" class="p-6 space-y-4">
                    <!-- Payment Type Selection -->
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-white/80">Select Payment Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="moncash" checked class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-red-500 peer-checked:bg-red-500/10 transition-all text-center">
                                    <div class="size-12 rounded-xl bg-red-500/20 flex items-center justify-center mx-auto mb-2">
                                        <span class="material-symbols-outlined text-red-400">phone_iphone</span>
                                    </div>
                                    <p class="text-white font-bold text-sm">MonCash</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="natcash" class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition-all text-center">
                                    <div class="size-12 rounded-xl bg-blue-500/20 flex items-center justify-center mx-auto mb-2">
                                        <span class="material-symbols-outlined text-blue-400">account_balance_wallet</span>
                                    </div>
                                    <p class="text-white font-bold text-sm">NatCash</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="bank_transfer" class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-green-500 peer-checked:bg-green-500/10 transition-all text-center">
                                    <div class="size-12 rounded-xl bg-green-500/20 flex items-center justify-center mx-auto mb-2">
                                        <span class="material-symbols-outlined text-green-400">account_balance</span>
                                    </div>
                                    <p class="text-white font-bold text-sm">Bank</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="credit_card" class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 transition-all text-center">
                                    <div class="size-12 rounded-xl bg-purple-500/20 flex items-center justify-center mx-auto mb-2">
                                        <span class="material-symbols-outlined text-purple-400">credit_card</span>
                                    </div>
                                    <p class="text-white font-bold text-sm">Card</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Account Number -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Account Number *</label>
                        <input type="text" name="account_number" required placeholder="e.g., 12345678" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        <p id="accountHint" class="text-xs text-white/50">Enter your MonCash number</p>
                    </div>

                    <!-- Account Name -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Account Name (Optional)</label>
                        <input type="text" name="account_name" placeholder="Full name on account" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <!-- Provider (optional) -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Provider (Optional)</label>
                        <input type="text" name="provider" placeholder="e.g., Digicel, BNC" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <!-- Make Default -->
                    <label class="flex items-center gap-3 cursor-pointer p-4 rounded-xl bg-background-dark border border-white/10">
                        <input type="checkbox" name="is_default" class="size-5 rounded border-white/10 bg-surface-dark text-primary focus:ring-primary/20"/>
                        <div>
                            <p class="text-white font-semibold text-sm">Set as default payment method</p>
                            <p class="text-white/50 text-xs">Use this method for all future payments</p>
                        </div>
                    </label>

                    <!-- Message Box -->
                    <div id="paymentMethodMessage" class="hidden p-4 rounded-xl"></div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closePaymentMethodModal()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold py-3 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" id="btnAddPaymentMethod" class="flex-1 bg-primary hover:bg-primary-dark active:scale-95 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                            Add Method
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Update hint text based on selected type
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const accountHint = document.getElementById('accountHint');

    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const hints = {
                'moncash': 'Enter your MonCash phone number',
                'natcash': 'Enter your NatCash phone number',
                'bank_transfer': 'Enter your bank account number',
                'credit_card': 'Enter your card number'
            };
            accountHint.textContent = hints[this.value] || 'Enter your account number';
        });
    });

    // Form submission
    document.getElementById('paymentMethodForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btnAddPaymentMethod');
        const msgBox = document.getElementById('paymentMethodMessage');
        const originalText = btn.textContent;

        btn.disabled = true;
        btn.textContent = 'Adding...';

        const formData = new FormData(e.target);
        const data = {
            type: formData.get('type'),
            account_number: formData.get('account_number'),
            account_name: formData.get('account_name') || null,
            provider: formData.get('provider') || null,
            is_default: formData.get('is_default') === 'on'
        };

        try {
            const response = await fetch('/api/add-payment-method.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                msgBox.className = 'p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-200';
                msgBox.textContent = 'Payment method added successfully!';
                msgBox.classList.remove('hidden');

                setTimeout(() => {
                    closePaymentMethodModal();
                    window.location.reload(); // Reload to show new payment method
                }, 1500);
            } else {
                msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
                msgBox.textContent = result.message || 'Failed to add payment method';
                msgBox.classList.remove('hidden');

                btn.disabled = false;
                btn.textContent = originalText;
            }
        } catch (error) {
            msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
            msgBox.textContent = 'An error occurred. Please try again.';
            msgBox.classList.remove('hidden');

            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
}

function closePaymentMethodModal() {
    const modal = document.getElementById('paymentMethodModal');
    if (modal) {
        modal.remove();
    }
}

// Make functions globally available
window.showAddPaymentMethodModal = showAddPaymentMethodModal;
window.closePaymentMethodModal = closePaymentMethodModal;
