<script lang="ts">
    import { onMount } from 'svelte';
    
    export let user: any = null;
    export let selectedGachaId: number | null = null;
    export let onGachaSelect: (gachaId: number) => void;

    interface Reward {
        title: string;
        character_name: string;
        description: string;
        image_path: string;
    }

    let rewards: Reward[] = [];
    let newReward = {
        title: '',
        character_name: '',
        description: '',
        imageFile: null as File | null
    };
    let errorMessage = '';
    let successMessage = '';

    $: if (selectedGachaId) {
        loadRewards();
    }

    async function loadRewards() {
        if (!selectedGachaId) return;
        
        try {
            const response = await fetch(`http://localhost/Gacha_Project/api/get_rewards.php?gacha_id=${selectedGachaId}`);
            const data = await response.json();
            if (Array.isArray(data)) {
                rewards = data;
            }
        } catch (error) {
            console.error('Failed to load rewards:', error);
        }
    }

    async function handleAddReward() {
        if (!selectedGachaId) return;
        
        try {
            if (!newReward.imageFile) {
                errorMessage = 'Please select an image';
                return;
            }

            // Upload image first
            const formData = new FormData();
            formData.append('image', newReward.imageFile);

            const uploadResponse = await fetch('http://localhost/Gacha_Project/api/upload.php', {
                method: 'POST',
                body: formData
            });

            const uploadResult = await uploadResponse.json();
            if (!uploadResult.success) {
                throw new Error(uploadResult.error);
            }

            // Add reward
            const response = await fetch('http://localhost/Gacha_Project/api/add_reward.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    gacha_id: selectedGachaId,
                    title: newReward.title,
                    character_name: newReward.character_name,
                    description: newReward.description,
                    image_path: uploadResult.image_path
                })
            });

            const result = await response.json();
            if (result.success) {
                successMessage = 'Reward added successfully!';
                newReward = {
                    title: '',
                    character_name: '',
                    description: '',
                    imageFile: null
                };
                await loadRewards();
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            errorMessage = error.message || 'Failed to add reward';
        }
    }
</script>

<div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-4">
    {#if selectedGachaId}
        <h2 class="text-xl font-bold mb-4">Manage Rewards</h2>

        <!-- Add reward form -->
        <form on:submit|preventDefault={handleAddReward} class="space-y-4 mb-8">
            <div>
                <label class="block text-gray-700">Title</label>
                <input
                    type="text"
                    bind:value={newReward.title}
                    class="w-full px-3 py-2 border rounded-lg"
                    required
                />
            </div>

            <div>
                <label class="block text-gray-700">Character Name</label>
                <input
                    type="text"
                    bind:value={newReward.character_name}
                    class="w-full px-3 py-2 border rounded-lg"
                    required
                />
            </div>

            <div>
                <label class="block text-gray-700">Description</label>
                <textarea
                    bind:value={newReward.description}
                    class="w-full px-3 py-2 border rounded-lg"
                    rows="3"
                    required
                ></textarea>
            </div>

            <div>
                <label class="block text-gray-700">Image</label>
                <input
                    type="file"
                    accept="image/*"
                    on:change={(e) => newReward.imageFile = e.target.files?.[0] || null}
                    class="w-full"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600"
            >
                Add Reward
            </button>
        </form>

        <!-- Current rewards list -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Current Rewards</h3>
            {#each rewards as reward}
                <div class="border rounded-lg p-4">
                    <img
                        src={`http://localhost/Gacha_Project/${reward.image_path}`}
                        alt={reward.character_name}
                        class="w-full h-32 object-cover rounded mb-2"
                    />
                    <h4 class="font-bold">{reward.title}</h4>
                    <p class="text-sm text-gray-600">{reward.character_name}</p>
                    <p class="text-sm mt-1">{reward.description}</p>
                </div>
            {/each}
        </div>
    {:else}
        <p class="text-gray-500 text-center">Select a gacha to manage rewards</p>
    {/if}
</div> 