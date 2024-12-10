<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';
    import WaveBackground from '$lib/components/WaveBackground.svelte';

    interface Gacha {
        post_id: number;
        title: string;
        character_name: string;
        description: string;
        image_path: string;
        username: string;
    }

    interface Reward {
        title: string;
        character_name: string;
        description: string;
        image_path: string;
    }

    interface User {
        user_id: number;
        username: string;
        image_path: string;
    }

    interface PullResult {
        reward: Reward;
        rarity: 'common' | 'rare' | 'super_rare';
    }

    let gachas: Gacha[] = [];
    let selectedGacha: Gacha | null = null;
    let rewards: Reward[] = [];
    let user: User | null = null;
    let errorMessage = '';
    let currentRewardIndex = 0;
    const rewardsPerPage = 3;
    let isPulling = false;
    let pullResults: PullResult[] = [];
    let showPullResults = false;

    onMount(async () => {
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            goto('/');
            return;
        }
        user = JSON.parse(userStr);
        await loadGachas();
    });

    async function loadGachas() {
        try {
            const response = await fetch('http://localhost/Gacha_Project/api/get.php');
            const data = await response.json();
            if (Array.isArray(data)) {
                gachas = data;
            }
        } catch (error) {
            errorMessage = 'Failed to load gachas';
        }
    }

    async function loadRewards(gachaId: number) {
        try {
            const response = await fetch(`http://localhost/Gacha_Project/api/get_rewards.php?gacha_id=${gachaId}`);
            const data = await response.json();
            if (Array.isArray(data)) {
                rewards = data;
            }
        } catch (error) {
            console.error('Failed to load rewards:', error);
        }
    }

    async function handleGachaSelect(gacha: Gacha) {
        selectedGacha = gacha;
        await loadRewards(gacha.post_id);
    }

    function handleLogout() {
        localStorage.removeItem('user');
        goto('/');
    }

    function nextRewards() {
        if (rewards.length > currentRewardIndex + rewardsPerPage) {
            currentRewardIndex += rewardsPerPage;
        }
    }

    function previousRewards() {
        if (currentRewardIndex >= rewardsPerPage) {
            currentRewardIndex -= rewardsPerPage;
        }
    }

    $: visibleRewards = rewards.slice(currentRewardIndex, currentRewardIndex + rewardsPerPage);

    async function handlePull(count: number) {
        if (!selectedGacha || !user || isPulling) return;

        try {
            isPulling = true;
            const response = await fetch('http://localhost/Gacha_Project/api/pull_gacha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: user.user_id,
                    gacha_id: selectedGacha.post_id,
                    pull_count: count
                })
            });

            const result = await response.json();
            
            if (result.success) {
                pullResults = result.pulls;
                showPullResults = true;
                // Reload rewards to show updated list
                await loadRewards(selectedGacha.post_id);
            } else {
                errorMessage = result.error || 'Pull failed';
            }
        } catch (error) {
            errorMessage = 'Failed to perform pull';
        } finally {
            isPulling = false;
        }
    }

    function closePullResults() {
        showPullResults = false;
        pullResults = [];
    }
</script>

<WaveBackground />
<div class="flex relative">
    <!-- Left Sidebar -->
    <div class="w-64 bg-white/80 backdrop-blur-sm min-h-screen p-4 flex flex-col">
        <!-- Profile Section -->
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold mb-4">Profile</h2>
            <img 
                src={user?.image_path ? `http://localhost/Gacha_Project/${user.image_path}` : '/assets/profile.png'}
                alt="Profile"
                class="w-24 h-24 rounded-full mx-auto mb-2"
            />
            <p class="font-bold">{user?.username}</p>
            <button
                on:click={() => goto('/profile')}
                class="mt-2 text-blue-500 hover:underline"
            >
                View Profile
            </button>
        </div>

        <!-- Available Gachas Button -->
        <div class="bg-cyan-400 text-white py-2 px-4 rounded-lg mb-4 text-center">
            Available Gachas
        </div>

        <!-- Gacha List -->
        <div class="flex-1 space-y-2 overflow-y-auto">
            {#each gachas as gacha}
                <div 
                    class="bg-white rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors {selectedGacha?.post_id === gacha.post_id ? 'border-2 border-blue-500' : ''}"
                    on:click={() => handleGachaSelect(gacha)}
                >
                    <img
                        src={`http://localhost/Gacha_Project/${gacha.image_path}`}
                        alt={gacha.character_name}
                        class="w-full h-32 object-cover rounded mb-2"
                    />
                    <p class="font-bold truncate">{gacha.title}</p>
                </div>
            {/each}
        </div>

        <!-- Logout Button -->
        <button 
            on:click={handleLogout}
            class="mt-4 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600"
        >
            Log Out
        </button>
    </div>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        {#if selectedGacha}
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-sm rounded-lg shadow-lg p-6">
                <!-- Main Gacha Display -->
                <div class="bg-red-300 aspect-video mb-6 rounded-lg overflow-hidden">
                    <img
                        src={`http://localhost/Gacha_Project/${selectedGacha.image_path}`}
                        alt={selectedGacha.character_name}
                        class="w-full h-full object-cover"
                    />
                </div>

                <!-- Gacha Info -->
                <div class="grid grid-cols-2 gap-8 mb-8">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">TITLE</h2>
                        <p class="text-gray-700">{selectedGacha.title}</p>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Character Name</h2>
                        <p class="text-gray-700">{selectedGacha.character_name}</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-2">Description</h2>
                    <p class="text-gray-700">{selectedGacha.description}</p>
                </div>

                <!-- Rewards Carousel -->
                <div class="bg-red-200 p-4 rounded-lg mb-6">
                    <div class="flex items-center justify-between">
                        <button 
                            class="text-2xl px-4 py-2 bg-gray-700 text-white rounded-lg {currentRewardIndex === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-600'}"
                            on:click={previousRewards}
                            disabled={currentRewardIndex === 0}
                        >
                            ←
                        </button>
                        <div class="flex-1 grid grid-cols-3 gap-4 px-4">
                            {#each visibleRewards as reward}
                                <div class="bg-white p-4 rounded-lg">
                                    <h3 class="text-center font-bold mb-2">POSSIBLE REWARDS</h3>
                                    <img
                                        src={`http://localhost/Gacha_Project/${reward.image_path}`}
                                        alt={reward.character_name}
                                        class="w-full h-32 object-cover rounded"
                                    />
                                </div>
                            {/each}
                            {#if visibleRewards.length < 3}
                                {#each Array(3 - visibleRewards.length) as _}
                                    <div class="bg-white p-4 rounded-lg">
                                        <h3 class="text-center font-bold mb-2">POSSIBLE REWARDS</h3>
                                        <div class="w-full h-32 bg-gray-200 rounded"></div>
                                    </div>
                                {/each}
                            {/if}
                        </div>
                        <button 
                            class="text-2xl px-4 py-2 bg-gray-700 text-white rounded-lg {currentRewardIndex + rewardsPerPage >= rewards.length ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-600'}"
                            on:click={nextRewards}
                            disabled={currentRewardIndex + rewardsPerPage >= rewards.length}
                        >
                            →
                        </button>
                    </div>
                </div>

                <!-- Draw Buttons -->
                <div class="flex justify-end space-x-4">
                    <button 
                        class="bg-gray-500 text-white px-8 py-2 rounded-lg hover:bg-gray-600 disabled:opacity-50"
                        on:click={() => handlePull(1)}
                        disabled={isPulling}
                    >
                        {isPulling ? 'Drawing...' : 'Draw 1'}
                    </button>
                    <button 
                        class="bg-gray-500 text-white px-8 py-2 rounded-lg hover:bg-gray-600 disabled:opacity-50"
                        on:click={() => handlePull(10)}
                        disabled={isPulling}
                    >
                        {isPulling ? 'Drawing...' : 'Draw 10'}
                    </button>
                </div>
            </div>
        {:else}
            <div class="text-center text-gray-500 mt-20 bg-white/80 backdrop-blur-sm p-6 rounded-lg">
                Select a gacha from the sidebar to view details
            </div>
        {/if}
    </main>
</div>

<!-- Add pull results modal -->
{#if showPullResults}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4">Pull Results</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                {#each pullResults as result}
                    <div class="border rounded-lg p-4 {result.rarity === 'super_rare' ? 'bg-yellow-100' : result.rarity === 'rare' ? 'bg-blue-100' : 'bg-gray-100'}">
                        <img
                            src={`http://localhost/Gacha_Project/${result.reward.image_path}`}
                            alt={result.reward.character_name}
                            class="w-full h-32 object-cover rounded mb-2"
                        />
                        <p class="font-bold truncate">{result.reward.title}</p>
                        <p class="text-sm text-gray-600">{result.reward.character_name}</p>
                        <p class="text-xs text-gray-500 capitalize">{result.rarity}</p>
                    </div>
                {/each}
            </div>
            <button
                class="mt-6 w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600"
                on:click={closePullResults}
            >
                Close
            </button>
        </div>
    </div>
{/if}