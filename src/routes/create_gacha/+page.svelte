<script lang="ts">
    import { onMount } from 'svelte';
    import { goto } from '$app/navigation';
    import GachaSidebar from '../../components/GachaSidebar.svelte';
    import WaveBackground from '$lib/components/WaveBackground.svelte';

    let title = '';
    let description = '';
    let character_name = '';
    let imageFile: File | null = null;
    let errorMessage = '';
    let successMessage = '';
    let user = null;
    let selectedGachaId: number | null = null;
    let allGachas = [];

    onMount(async () => {
        const userStr = localStorage.getItem('user');
        if (!userStr) {
            goto('/');
            return;
        }
        user = JSON.parse(userStr);
        if (user.role !== 1) {
            goto('/gacha');
        }
        await loadAllGachas();
    });

    async function loadAllGachas() {
        try {
            const response = await fetch('http://localhost/Gacha_Project/api/get.php');
            const data = await response.json();
            if (Array.isArray(data)) {
                allGachas = data;
            }
        } catch (error) {
            console.error('Failed to load gachas:', error);
        }
    }

    function handleGachaSelect(gachaId: number) {
        selectedGachaId = gachaId;
    }

    async function handleSubmit() {
        try {
            if (!imageFile) {
                errorMessage = 'Please select an image';
                return;
            }

            // First upload the image
            const formData = new FormData();
            formData.append('image', imageFile);

            const uploadResponse = await fetch('http://localhost/Gacha_Project/api/upload.php', {
                method: 'POST',
                body: formData
            });

            const uploadResult = await uploadResponse.json();

            if (!uploadResult.success) {
                throw new Error(uploadResult.error);
            }

            // Then create the gacha post
            const response = await fetch('http://localhost/Gacha_Project/api/post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: user.user_id,
                    title,
                    description,
                    character_name,
                    image_path: uploadResult.image_path
                })
            });

            const result = await response.json();
            
            if (result.success) {
                successMessage = 'Gacha created successfully!';
                selectedGachaId = result.gacha_id;
                title = '';
                description = '';
                character_name = '';
                imageFile = null;
                errorMessage = '';
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            errorMessage = error.message || 'Failed to create gacha';
            successMessage = '';
        }
    }

    function handleLogout() {
        localStorage.removeItem('user');
        goto('/');
    }
</script>

<WaveBackground />
<main class="min-h-screen flex">
    <!-- Left Sidebar -->
    <div class="w-72 bg-white/80 backdrop-blur-sm p-4 flex flex-col h-screen sticky top-0">
        <h2 class="text-xl font-bold mb-4">All Gachas</h2>
        <div class="flex-1 overflow-y-auto space-y-3">
            {#each allGachas as gacha}
                <div 
                    class="bg-white border rounded-lg p-3 cursor-pointer hover:border-blue-500 transition-colors {selectedGachaId === gacha.post_id ? 'border-2 border-blue-500' : 'border-gray-200'}"
                    on:click={() => handleGachaSelect(gacha.post_id)}
                >
                    <img
                        src={`http://localhost/Gacha_Project/${gacha.image_path}`}
                        alt={gacha.character_name}
                        class="w-full h-32 object-cover rounded-lg mb-2"
                    />
                    <h3 class="font-semibold truncate">{gacha.title}</h3>
                    <p class="text-sm text-gray-600 truncate">{gacha.character_name}</p>
                </div>
            {/each}
        </div>
        <button 
            on:click={handleLogout}
            class="mt-4 w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition-colors"
        >
            Log Out
        </button>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-sm rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold">Create New Gacha</h1>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Image Preview -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="aspect-video mb-4 rounded-lg overflow-hidden bg-gray-200">
                        {#if imageFile}
                            <img 
                                src={URL.createObjectURL(imageFile)} 
                                alt="Preview" 
                                class="w-full h-full object-cover"
                            />
                        {:else}
                            <div class="w-full h-full flex items-center justify-center text-gray-500">
                                Image Preview
                            </div>
                        {/if}
                    </div>

                    <input
                        type="file"
                        accept="image/*"
                        on:change={(e) => imageFile = e.target.files[0]}
                        class="hidden"
                        id="imageInput"
                        required
                    />
                    <label 
                        for="imageInput"
                        class="block w-full bg-blue-500 text-white text-center py-2 rounded-lg cursor-pointer hover:bg-blue-600 transition-colors"
                    >
                        Choose Image
                    </label>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block font-semibold mb-2">Title</label>
                        <input
                            type="text"
                            bind:value={title}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="Enter title"
                            required
                        />
                    </div>
                    <div>
                        <label class="block font-semibold mb-2">Character Name</label>
                        <input
                            type="text"
                            bind:value={character_name}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                            placeholder="Enter character name"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-2">Description</label>
                    <textarea
                        bind:value={description}
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                        rows="4"
                        placeholder="Enter description"
                        required
                    ></textarea>
                </div>

                <!-- Messages -->
                {#if errorMessage}
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {errorMessage}
                    </div>
                {/if}

                {#if successMessage}
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        {successMessage}
                    </div>
                {/if}

                <!-- Action Buttons -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="space-x-3">
                        <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Draw 1
                        </button>
                        <button class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            Draw 10
                        </button>
                    </div>
                    <button
                        on:click={handleSubmit}
                        class="px-8 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Create Gacha
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    {#if user}
        <div class="w-[500px] max-w-[30%] bg-white/80 backdrop-blur-sm p-4 flex flex-col h-screen sticky top-0">
            <GachaSidebar {user} {selectedGachaId} onGachaSelect={handleGachaSelect} />
        </div>
    {/if}
</main> 