/**
 * Campaign Creation/Edit Modal
 * Handles campaign CRUD operations
 */

// Show campaign creation modal
function showCreateCampaignModal() {
    showCampaignModal(null);
}

// Show campaign edit modal
function showEditCampaignModal(campaignId) {
    // TODO: Fetch campaign data and pre-fill form
    showCampaignModal(campaignId);
}

// Main modal function
async function showCampaignModal(campaignId = null) {
    const isEdit = campaignId !== null;
    let campaign = null;

    if (isEdit) {
        // TODO: Fetch campaign data
        try {
            const response = await fetch(`/api/get-campaigns.php?campaign_id=${campaignId}`);
            const data = await response.json();
            if (data.success && data.campaigns && data.campaigns.length > 0) {
                campaign = data.campaigns[0];
            }
        } catch (error) {
            console.error('Failed to load campaign:', error);
        }
    }

    const modalHTML = `
        <div id="campaignModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) closeCampaignModal()">
            <div class="bg-surface-dark rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-white/10">
                <!-- Header -->
                <div class="sticky top-0 bg-surface-dark border-b border-white/10 p-6 flex items-center justify-between z-10">
                    <h2 class="text-xl font-bold text-white">${isEdit ? 'Edit Campaign' : 'Create New Campaign'}</h2>
                    <button onclick="closeCampaignModal()" class="size-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form id="campaignForm" class="p-6 space-y-4">
                    <!-- Title -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Campaign Title *</label>
                        <input type="text" name="title" value="${escapeHtml(campaign?.title || '')}" required placeholder="e.g., Support Local Art Education" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Short Description *</label>
                        <textarea name="description" required rows="2" placeholder="Brief summary of your campaign..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20 resize-none">${escapeHtml(campaign?.description || '')}</textarea>
                        <p class="text-xs text-white/50">Maximum 200 characters</p>
                    </div>

                    <!-- Full Story -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Full Story (Optional)</label>
                        <textarea name="story" rows="5" placeholder="Tell the complete story of your campaign..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20">${escapeHtml(campaign?.story || '')}</textarea>
                    </div>

                    <!-- Goal Amount & Category -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Goal Amount (HTG) *</label>
                            <input type="number" name="goal_amount" value="${campaign?.goal_amount || ''}" required min="1" step="0.01" placeholder="5000" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Category *</label>
                            <select name="category" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <option value="">Select category</option>
                                <option value="art" ${campaign?.category === 'art' ? 'selected' : ''}>Art (Atizay)</option>
                                <option value="music" ${campaign?.category === 'music' ? 'selected' : ''}>Music (Mizik)</option>
                                <option value="education" ${campaign?.category === 'education' ? 'selected' : ''}>Education (Edikasyon)</option>
                                <option value="youth" ${campaign?.category === 'youth' ? 'selected' : ''}>Youth (Jèn)</option>
                                <option value="heritage" ${campaign?.category === 'heritage' ? 'selected' : ''}>Heritage (Eritaj)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Start Date</label>
                            <input type="date" name="start_date" value="${campaign?.start_date?.split(' ')[0] || new Date().toISOString().split('T')[0]}" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">End Date *</label>
                            <input type="date" name="end_date" value="${campaign?.end_date?.split(' ')[0] || ''}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        </div>
                    </div>

                    <!-- Media URLs -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Cover Image URL (Optional)</label>
                        <input type="url" name="image_url" value="${escapeHtml(campaign?.image_url || '')}" placeholder="https://example.com/image.jpg" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        <p class="text-xs text-white/50">Paste a link to your campaign cover image</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Video URL (Optional)</label>
                        <input type="url" name="video_url" value="${escapeHtml(campaign?.video_url || '')}" placeholder="https://youtube.com/watch?v=..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        <p class="text-xs text-white/50">YouTube, Vimeo, or other video platform link</p>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Status</label>
                        <div class="flex gap-3">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="draft" ${!campaign || campaign.status === 'draft' ? 'checked' : ''} class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-yellow-500 peer-checked:bg-yellow-500/10 transition-all text-center">
                                    <span class="material-symbols-outlined text-yellow-500 text-2xl block mb-1">edit_note</span>
                                    <p class="text-white font-semibold text-sm">Draft</p>
                                    <p class="text-white/50 text-xs">Not visible to others</p>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="active" ${campaign?.status === 'active' ? 'checked' : ''} class="sr-only peer"/>
                                <div class="p-4 rounded-xl bg-background-dark border-2 border-white/10 peer-checked:border-green-500 peer-checked:bg-green-500/10 transition-all text-center">
                                    <span class="material-symbols-outlined text-green-500 text-2xl block mb-1">check_circle</span>
                                    <p class="text-white font-semibold text-sm">Active</p>
                                    <p class="text-white/50 text-xs">Live and accepting donations</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Message Box -->
                    <div id="campaignMessage" class="hidden p-4 rounded-xl"></div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeCampaignModal()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold py-3.5 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" id="btnSaveCampaign" class="flex-1 bg-primary hover:bg-primary-dark active:scale-95 text-white font-bold py-3.5 rounded-xl shadow-lg transition-all">
                            ${isEdit ? 'Update Campaign' : 'Create Campaign'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Form submission
    document.getElementById('campaignForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btnSaveCampaign');
        const msgBox = document.getElementById('campaignMessage');
        const originalText = btn.textContent;

        btn.disabled = true;
        btn.textContent = 'Saving...';

        const formData = new FormData(e.target);
        const data = {};

        for (const [key, value] of formData.entries()) {
            if (value.trim()) {
                data[key] = value.trim();
            }
        }

        try {
            const endpoint = isEdit ? `/api/update-campaign.php` : `/api/create-campaign.php`;
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(isEdit ? { ...data, campaign_id: campaignId } : data)
            });

            const result = await response.json();

            if (result.success) {
                msgBox.className = 'p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-200';
                msgBox.textContent = isEdit ? 'Campaign updated successfully!' : 'Campaign created successfully!';
                msgBox.classList.remove('hidden');

                setTimeout(() => {
                    closeCampaignModal();
                    window.location.reload(); // Reload to show new campaign
                }, 1500);
            } else {
                msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
                msgBox.textContent = result.message || 'Failed to save campaign';
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

// Close campaign modal
function closeCampaignModal() {
    const modal = document.getElementById('campaignModal');
    if (modal) {
        modal.remove();
    }
}

// Make functions globally available
window.showCreateCampaignModal = showCreateCampaignModal;
window.showEditCampaignModal = showEditCampaignModal;
window.closeCampaignModal = closeCampaignModal;
