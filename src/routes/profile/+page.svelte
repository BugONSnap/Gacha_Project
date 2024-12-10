<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';

    interface GachaHistory {
        history_id: number;
        gacha_id: number;
        pulled_at: string;
        gacha_title: string;
        reward_title: string;
        reward_image: string;
        character_name: string;
    }

    interface UserProfile {
        username: string;
        role: number;
        character_name: string | null;
        image_path: string | null;
        total_pulls: number;
    }

    let user = null;
    let profile: UserProfile | null = null;
    let gachaHistory: GachaHistory[] = [];
    let errorMessage = '';

    onMount(async () => {
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            goto('/');
            return;
        }
        user = JSON.parse(userStr);
        await Promise.all([
            loadProfile(),
            loadGachaHistory()
        ]);
    });

    async function loadProfile() {
        try {
            const response = await fetch(`http://localhost/Gacha_Project/api/get_profile.php?user_id=${user.user_id}`);
            const data = await response.json();
            if (data.success) {
                profile = data.profile;
            }
        } catch (error) {
            errorMessage = 'Failed to load profile';
        }
    }

    async function loadGachaHistory() {
        try {
            const response = await fetch(`http://localhost/Gacha_Project/api/get_user_history.php?user_id=${user.user_id}`);
            const data = await response.json();
            if (Array.isArray(data)) {
                gachaHistory = data;
            }
        } catch (error) {
            errorMessage = 'Failed to load gacha history';
        }
    }

    function handleLogout() {
        localStorage.removeItem('user');
        goto('/');
    }
</script>

<main class="min-h-screen flex p-4">
    <div class="w-full bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6">
        <div class="min-h-screen bg-gray-100">
            <!-- Header -->
            <nav class="bg-white shadow mb-8">
                <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">User Profile</h1>
                    <div class="flex gap-4">
                        <button
                            on:click={() => goto('/gacha')}
                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                        >
                            Back to Gacha
                        </button>
                        <button
                            on:click={handleLogout}
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </nav>

            <div class="container mx-auto px-4">
                {#if errorMessage}
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {errorMessage}
                    </div>
                {/if}

                <!-- Profile Section -->
                {#if profile}
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                        <div class="flex items-center mb-6">
                            <img 
                                src={profile.image_path ? `http://localhost/Gacha_Project/${profile.image_path}` : '/assets/profile.png'}
                                alt="Profile"
                                class="w-24 h-24 rounded-full mr-6"
                            />
                            <div>
                                <h2 class="text-2xl font-bold">{profile.username}</h2>
             
                                <p class="text-gray-600">Role: {profile.role === 1 ? 'Admin' : 'User'}</p>
                            </div>
                        </div>
                    </div>
                {/if}

                <!-- Gacha History -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-6">Gacha Pull History</h2>
                    {#if gachaHistory.length > 0}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {#each gachaHistory as history}
                                <div class="border rounded-lg p-4">
                                    <img
                                        src={`http://localhost/Gacha_Project/${history.reward_image}`}
                                        alt={history.character_name}
                                        class="w-full h-40 object-cover rounded-lg mb-4"
                                    />
                                    <div class="space-y-1">
                                        <p class="font-bold">{history.gacha_title}</p>
                                        <p class="text-sm text-gray-600">Reward: {history.reward_title}</p>
                                        <p class="text-sm text-gray-600">Character: {history.character_name}</p>
                                        <p class="text-xs text-gray-500">Pulled at: {new Date(history.pulled_at).toLocaleString()}</p>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {:else}
                        <p class="text-center text-gray-500">No gacha pulls yet</p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</main>