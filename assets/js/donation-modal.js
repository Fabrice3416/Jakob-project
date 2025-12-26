/**
 * Donation Modal
 * Handles donation process with payment methods
 */

function showDonationModal(campaignId, campaignTitle, influencerName) {
    const modalHTML = `
        <div id="donationModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) closeDonationModal()">
            <div class="bg-surface-dark rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto border border-white/10">
                <!-- Header -->
                <div class="sticky top-0 bg-surface-dark border-b border-white/10 p-6 z-10">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white">Make a Donation</h2>
                        <button onclick="closeDonationModal()" class="size-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="space-y-1">
                        <p class="text-white/60 text-sm">Supporting</p>
                        <p class="text-white font-bold">${escapeHtml(campaignTitle)}</p>
                        <p class="text-white/60 text-sm">by ${escapeHtml(influencerName)}</p>
                    </div>
                </div>

                <!-- Form -->
                <form id="donationForm" class="p-6 space-y-6">
                    <!-- Amount Selection -->
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-white/80">Select Amount (HTG)</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" onclick="selectAmount(100)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">100</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                            <button type="button" onclick="selectAmount(250)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">250</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                            <button type="button" onclick="selectAmount(500)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">500</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                            <button type="button" onclick="selectAmount(1000)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">1,000</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                            <button type="button" onclick="selectAmount(2500)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">2,500</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                            <button type="button" onclick="selectAmount(5000)" class="amount-btn p-4 rounded-xl bg-background-dark border-2 border-white/10 hover:border-primary/50 transition-all text-center">
                                <p class="text-white font-bold text-lg">5,000</p>
                                <p class="text-white/50 text-xs">HTG</p>
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60">HTG</span>
                            <input type="number" id="customAmount" name="amount" min="1" step="0.01" placeholder="Or enter custom amount" class="w-full bg-background-dark border border-white/10 rounded-xl pl-16 pr-4 py-3.5 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="space-y-3">
                        <label class="text-sm font-semibold text-white/80">Payment Method</label>
                        <div class="space-y-2">
                            <label class="cursor-pointer block">
                                <input type="radio" name="payment_method" value="moncash" checked class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-primary peer-checked:bg-primary/5 transition-all flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-red-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-red-400">phone_iphone</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white font-bold text-sm">MonCash</p>
                                        <p class="text-white/50 text-xs">Digicel mobile payment</p>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer block">
                                <input type="radio" name="payment_method" value="natcash" class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-primary peer-checked:bg-primary/5 transition-all flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-blue-400">account_balance_wallet</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white font-bold text-sm">NatCash</p>
                                        <p class="text-white/50 text-xs">Natcom mobile payment</p>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer block">
                                <input type="radio" name="payment_method" value="card" class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-primary peer-checked:bg-primary/5 transition-all flex items-center gap-3">
                                    <div class="size-12 rounded-xl bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-purple-400">credit_card</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white font-bold text-sm">Credit Card</p>
                                        <p class="text-white/50 text-xs">Visa, Mastercard, etc.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Optional Message -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Message (Optional)</label>
                        <textarea name="message" rows="3" placeholder="Leave a message of support..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20 resize-none"></textarea>
                    </div>

                    <!-- Anonymous Option -->
                    <label class="flex items-center gap-3 cursor-pointer p-4 rounded-xl bg-background-dark border border-white/10">
                        <input type="checkbox" name="is_anonymous" class="size-5 rounded border-white/10 bg-surface-dark text-primary focus:ring-primary/20"/>
                        <div>
                            <p class="text-white font-semibold text-sm">Make this donation anonymous</p>
                            <p class="text-white/50 text-xs">Your name won't be shown publicly</p>
                        </div>
                    </label>

                    <!-- Message Box -->
                    <div id="donationMessage" class="hidden p-4 rounded-xl"></div>

                    <!-- Total & Submit -->
                    <div class="space-y-3 pt-4">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-primary/10 border border-primary/30">
                            <span class="text-white font-semibold">Total Amount</span>
                            <span id="totalAmount" class="text-white text-2xl font-black">0 HTG</span>
                        </div>
                        <button type="submit" id="btnDonate" class="w-full bg-primary hover:bg-primary-dark active:scale-95 text-white font-bold py-4 rounded-full shadow-lg transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">favorite</span>
                            Send Love & Support
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Store campaign ID
    document.getElementById('donationForm').dataset.campaignId = campaignId;

    // Amount input listener
    const amountInput = document.getElementById('customAmount');
    amountInput.addEventListener('input', function() {
        updateTotal(this.value);
    });

    // Form submission
    document.getElementById('donationForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btnDonate');
        const msgBox = document.getElementById('donationMessage');
        const originalHTML = btn.innerHTML;

        const amount = parseFloat(document.getElementById('customAmount').value);

        if (!amount || amount <= 0) {
            msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
            msgBox.textContent = 'Please select or enter a valid amount';
            msgBox.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Processing...';

        const formData = new FormData(e.target);
        const data = {
            campaign_id: parseInt(campaignId),
            amount: amount,
            payment_method: formData.get('payment_method'),
            message: formData.get('message') || null,
            is_anonymous: formData.get('is_anonymous') === 'on'
        };

        try {
            const response = await fetch('/api/create-donation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                msgBox.className = 'p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-200';
                msgBox.innerHTML = `
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-green-400">check_circle</span>
                        <div>
                            <p class="font-bold mb-1">Donation Successful! 🎉</p>
                            <p class="text-sm">Thank you for supporting this campaign!</p>
                        </div>
                    </div>
                `;
                msgBox.classList.remove('hidden');

                setTimeout(() => {
                    closeDonationModal();
                    // Build URL with donation data
                    const params = new URLSearchParams({
                        donation_id: result.donation?.id || '',
                        amount: amount,
                        recipient: influencerName,
                        campaign: campaignTitle,
                        method: formData.get('payment_method'),
                        ref: result.transaction_ref || ''
                    });
                    window.location.href = `/pages/main/payment-success.html?${params.toString()}`;
                }, 2000);
            } else {
                msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
                msgBox.textContent = result.message || 'Donation failed';
                msgBox.classList.remove('hidden');

                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        } catch (error) {
            msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
            msgBox.textContent = 'An error occurred. Please try again.';
            msgBox.classList.remove('hidden');

            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    });
}

function selectAmount(amount) {
    // Remove active state from all buttons
    document.querySelectorAll('.amount-btn').forEach(btn => {
        btn.classList.remove('border-primary', 'bg-primary/10');
        btn.classList.add('border-white/10');
    });

    // Add active state to clicked button
    event.currentTarget.classList.remove('border-white/10');
    event.currentTarget.classList.add('border-primary', 'bg-primary/10');

    // Update input
    document.getElementById('customAmount').value = amount;
    updateTotal(amount);
}

function updateTotal(amount) {
    const total = parseFloat(amount) || 0;
    document.getElementById('totalAmount').textContent = formatCurrency(total);
}

function closeDonationModal() {
    const modal = document.getElementById('donationModal');
    if (modal) {
        modal.remove();
    }
}

// Make functions globally available
window.showDonationModal = showDonationModal;
window.closeDonationModal = closeDonationModal;
window.selectAmount = selectAmount;
