/**
 * Profile Edit Modal
 * Handles profile editing functionality
 */

// Create and show profile edit modal
function showProfileEditModal() {
    const user = window.currentUser; // Set by user-data.js
    if (!user) {
        alert('User data not loaded');
        return;
    }

    const isDonor = user.user_type === 'donor';

    const modalHTML = `
        <div id="profileEditModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) closeProfileEditModal()">
            <div class="bg-surface-dark rounded-3xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-white/10">
                <!-- Header -->
                <div class="sticky top-0 bg-surface-dark border-b border-white/10 p-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-white">Edit Profile</h2>
                    <button onclick="closeProfileEditModal()" class="size-10 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-white/60 hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Form -->
                <form id="profileEditForm" class="p-6 space-y-4">
                    ${isDonor ? `
                        <!-- Donor Fields -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-white/80">First Name</label>
                                <input type="text" name="first_name" value="${escapeHtml(user.first_name || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-white/80">Last Name</label>
                                <input type="text" name="last_name" value="${escapeHtml(user.last_name || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                            </div>
                        </div>
                    ` : `
                        <!-- Influencer Fields -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Display Name</label>
                            <input type="text" name="display_name" value="${escapeHtml(user.display_name || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Username</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/60">@</span>
                                <input type="text" name="username" value="${escapeHtml(user.username || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl pl-8 pr-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-white/80">Category</label>
                            <select name="category" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <option value="art" ${user.category === 'art' ? 'selected' : ''}>Art</option>
                                <option value="music" ${user.category === 'music' ? 'selected' : ''}>Music</option>
                                <option value="education" ${user.category === 'education' ? 'selected' : ''}>Education</option>
                                <option value="youth" ${user.category === 'youth' ? 'selected' : ''}>Youth</option>
                                <option value="heritage" ${user.category === 'heritage' ? 'selected' : ''}>Heritage</option>
                            </select>
                        </div>
                    `}

                    <!-- Common Fields -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Email</label>
                        <input type="email" name="email" value="${escapeHtml(user.email || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Phone</label>
                        <input type="tel" name="phone" value="${escapeHtml(user.phone || '')}" required class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Location</label>
                        <input type="text" name="location" value="${escapeHtml(user.location || '')}" placeholder="Port-au-Prince, Haiti" class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Bio</label>
                        <textarea name="bio" rows="4" placeholder="Tell us about yourself..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20 resize-none">${escapeHtml(user.bio || '')}</textarea>
                    </div>

                    <!-- Avatar URL (temporary until we have upload) -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-white/80">Avatar URL</label>
                        <input type="url" name="avatar_url" value="${escapeHtml(user.avatar_url || '')}" placeholder="https://..." class="w-full bg-background-dark border border-white/10 rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:border-primary focus:ring-2 focus:ring-primary/20"/>
                        <p class="text-xs text-white/50">Paste a link to your profile picture</p>
                    </div>

                    <!-- Error/Success Message -->
                    <div id="profileEditMessage" class="hidden p-4 rounded-xl"></div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeProfileEditModal()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white font-semibold py-3 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" id="btnSaveProfile" class="flex-1 bg-primary hover:bg-primary-dark active:scale-95 text-white font-bold py-3 rounded-xl shadow-lg transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Form submission handler
    document.getElementById('profileEditForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.getElementById('btnSaveProfile');
        const msgBox = document.getElementById('profileEditMessage');
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
            const response = await fetch('/api/update-profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                msgBox.className = 'p-4 rounded-xl bg-green-500/20 border border-green-500/30 text-green-200';
                msgBox.textContent = 'Profile updated successfully!';
                msgBox.classList.remove('hidden');

                // Reload profile data
                setTimeout(async () => {
                    const profile = await getUserProfile();
                    if (profile) {
                        updateProfileUI(profile);
                        window.currentUser = profile;
                    }
                    closeProfileEditModal();
                }, 1500);
            } else {
                msgBox.className = 'p-4 rounded-xl bg-red-500/20 border border-red-500/30 text-red-200';
                msgBox.textContent = result.message || 'Failed to update profile';
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

// Close profile edit modal
function closeProfileEditModal() {
    const modal = document.getElementById('profileEditModal');
    if (modal) {
        modal.remove();
    }
}

// Make functions globally available
window.showProfileEditModal = showProfileEditModal;
window.closeProfileEditModal = closeProfileEditModal;
