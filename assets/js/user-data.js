/**
 * JaKòb - User Data Loader
 * Loads real user data from APIs and updates the UI
 */

// API base URL
const API_BASE = '/api';

// Global user data
let currentUser = null;

/**
 * Check if user is authenticated
 */
async function checkAuth() {
    try {
        const response = await fetch(`${API_BASE}/me.php`);
        const data = await response.json();

        if (!data.success) {
            // Not authenticated, redirect to login
            window.location.href = '/pages/auth/login.html';
            return null;
        }

        currentUser = data.data;
        return currentUser;
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = '/pages/auth/login.html';
        return null;
    }
}

/**
 * Get user profile data
 */
async function getUserProfile() {
    try {
        const response = await fetch(`${API_BASE}/get-profile.php`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        return data.user;
    } catch (error) {
        console.error('Failed to load profile:', error);
        return null;
    }
}

/**
 * Get campaigns
 */
async function getCampaigns(options = {}) {
    try {
        const params = new URLSearchParams({
            status: options.status || 'active',
            limit: options.limit || 10,
            offset: options.offset || 0
        });

        if (options.category) params.append('category', options.category);
        if (options.influencer_id) params.append('influencer_id', options.influencer_id);

        const response = await fetch(`${API_BASE}/get-campaigns.php?${params}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        return data;
    } catch (error) {
        console.error('Failed to load campaigns:', error);
        return { success: false, campaigns: [] };
    }
}

/**
 * Get wallet data
 */
async function getWallet() {
    try {
        const response = await fetch(`${API_BASE}/get-wallet.php`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        return data;
    } catch (error) {
        console.error('Failed to load wallet:', error);
        return null;
    }
}

/**
 * Get notifications
 */
async function getNotifications(unreadOnly = false) {
    try {
        const params = new URLSearchParams({
            unread_only: unreadOnly
        });

        const response = await fetch(`${API_BASE}/get-notifications.php?${params}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        return data;
    } catch (error) {
        console.error('Failed to load notifications:', error);
        return { notifications: [], unread_count: 0 };
    }
}

/**
 * Update profile UI elements
 */
function updateProfileUI(user) {
    // Update user name
    const nameElements = document.querySelectorAll('[data-user-name]');
    nameElements.forEach(el => {
        if (user.user_type === 'donor') {
            el.textContent = `${user.first_name} ${user.last_name}`;
        } else {
            el.textContent = user.display_name;
        }
    });

    // Update email
    const emailElements = document.querySelectorAll('[data-user-email]');
    emailElements.forEach(el => {
        el.textContent = user.email;
    });

    // Update phone
    const phoneElements = document.querySelectorAll('[data-user-phone]');
    phoneElements.forEach(el => {
        el.textContent = user.phone;
    });

    // Update avatar
    const avatarElements = document.querySelectorAll('[data-user-avatar]');
    avatarElements.forEach(el => {
        if (user.avatar_url) {
            el.src = user.avatar_url;
        }
    });

    // Update bio
    const bioElements = document.querySelectorAll('[data-user-bio]');
    bioElements.forEach(el => {
        el.textContent = user.bio || 'No bio yet';
    });

    // Update location
    const locationElements = document.querySelectorAll('[data-user-location]');
    locationElements.forEach(el => {
        el.textContent = user.location || 'Not specified';
    });

    // For donors
    if (user.user_type === 'donor') {
        const donatedElements = document.querySelectorAll('[data-donor-total]');
        donatedElements.forEach(el => {
            el.textContent = formatCurrency(user.total_donated);
        });

        const countElements = document.querySelectorAll('[data-donor-count]');
        countElements.forEach(el => {
            el.textContent = user.donation_count || 0;
        });
    }

    // For influencers
    if (user.user_type === 'influencer') {
        const usernameElements = document.querySelectorAll('[data-influencer-username]');
        usernameElements.forEach(el => {
            el.textContent = '@' + user.username;
        });

        const raisedElements = document.querySelectorAll('[data-influencer-raised]');
        raisedElements.forEach(el => {
            el.textContent = formatCurrency(user.total_raised);
        });

        const followersElements = document.querySelectorAll('[data-influencer-followers]');
        followersElements.forEach(el => {
            el.textContent = formatNumber(user.total_followers);
        });

        const campaignsElements = document.querySelectorAll('[data-influencer-campaigns]');
        campaignsElements.forEach(el => {
            el.textContent = user.total_campaigns || 0;
        });
    }
}

/**
 * Update wallet UI
 */
function updateWalletUI(walletData) {
    // Update balance
    const balanceElements = document.querySelectorAll('[data-wallet-balance]');
    balanceElements.forEach(el => {
        el.textContent = formatCurrency(walletData.wallet.total_balance);
    });

    // Update payment methods list
    const methodsContainer = document.querySelector('[data-payment-methods]');
    if (methodsContainer && walletData.wallet.payment_methods) {
        methodsContainer.innerHTML = walletData.wallet.payment_methods.map(method => `
            <div class="payment-method-item">
                <span class="method-type">${method.type}</span>
                <span class="method-balance">${formatCurrency(method.balance)}</span>
            </div>
        `).join('');
    }

    // Update transactions list
    const transactionsContainer = document.querySelector('[data-transactions-list]');
    if (transactionsContainer && walletData.transactions) {
        if (walletData.transactions.length === 0) {
            transactionsContainer.innerHTML = '<p class="text-white/60">No transactions yet</p>';
        } else {
            transactionsContainer.innerHTML = walletData.transactions.map(tx => `
                <div class="transaction-item">
                    <div class="transaction-info">
                        <span class="transaction-type">${tx.type}</span>
                        <span class="transaction-date">${formatDate(tx.created_at)}</span>
                    </div>
                    <span class="transaction-amount ${tx.type === 'withdrawal' ? 'negative' : 'positive'}">
                        ${tx.type === 'withdrawal' ? '-' : '+'}${formatCurrency(tx.amount)}
                    </span>
                </div>
            `).join('');
        }
    }
}

/**
 * Update campaigns UI
 */
function updateCampaignsUI(campaignsData, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!campaignsData.campaigns || campaignsData.campaigns.length === 0) {
        container.innerHTML = '<p class="text-white/60 text-center py-10">No campaigns found</p>';
        return;
    }

    container.innerHTML = campaignsData.campaigns.map(campaign => `
        <div class="campaign-card bg-surface-dark rounded-2xl overflow-hidden hover:transform hover:scale-105 transition-all">
            <div class="campaign-image h-48 bg-gradient-to-br from-primary/20 to-accent/20"></div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-2">${escapeHtml(campaign.title)}</h3>
                <p class="text-white/60 mb-4 line-clamp-2">${escapeHtml(campaign.description)}</p>
                <div class="progress-bar bg-white/10 rounded-full h-2 mb-2">
                    <div class="progress-fill bg-primary h-full rounded-full" style="width: ${campaign.progress_percentage}%"></div>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-white/80">${formatCurrency(campaign.raised_amount)} raised</span>
                    <span class="text-white/60">${campaign.progress_percentage}%</span>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Update notifications badge
 */
async function updateNotificationsBadge() {
    const data = await getNotifications(true);
    const badges = document.querySelectorAll('[data-notifications-badge]');

    badges.forEach(badge => {
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}

/**
 * Utility: Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-HT', {
        style: 'currency',
        currency: 'HTG',
        minimumFractionDigits: 0
    }).format(amount || 0);
}

/**
 * Utility: Format number
 */
function formatNumber(num) {
    return new Intl.NumberFormat('fr-HT').format(num || 0);
}

/**
 * Utility: Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-HT', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
}

/**
 * Utility: Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Initialize user data on page load
 */
async function initUserData() {
    try {
        // Check authentication
        const user = await checkAuth();
        if (!user) return;

        // Load and update profile
        const profile = await getUserProfile();
        if (profile) {
            updateProfileUI(profile);
        }

        // Update notifications badge
        await updateNotificationsBadge();

        return profile;
    } catch (error) {
        console.error('Failed to initialize user data:', error);
    }
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUserData);
} else {
    initUserData();
}
