<script>
    import { onMount } from 'svelte';
    import { user } from '../stores/auth.js';

    let gachaHistory = [];

    onMount(async () => {
        await fetchGachaHistory();
    });

    async function fetchGachaHistory() {
        try {
            const response = await fetch(`http://localhost/api/get_gacha_history.php?user_id=${$user.id}`);
            const data = await response.json();
            if (data.success) {
                gachaHistory = data.history;
            }
        } catch (error) {
            console.error('Error fetching history:', error);
        }
    }

    async function setProfileCharacter(gachaId) {
        try {
            const response = await fetch('http://localhost/api/update_profile_character.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: $user.id,
                    gacha_id: gachaId
                })
            });
            const data = await response.json();
            if (data.success) {
                alert('Profile character updated!');
            }
        } catch (error) {
            console.error('Error updating profile:', error);
        }
    }
</script>

<div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-4">
    <div class="profile">
        <h1>Profile</h1>
        <div class="user-info">
            <h2>{$user.username}</h2>
        </div>

        <h2>Gacha Pull History</h2>
        <div class="gacha-history">
            {#each gachaHistory as pull}
                <div class="pull-card">
                    <img src={pull.image_url} alt={pull.character_name} />
                    <h3>{pull.character_name}</h3>
                    <p>Pulled at: {new Date(pull.pulled_at).toLocaleString()}</p>
                    <button on:click={() => setProfileCharacter(pull.gacha_id)}>
                        Set as Profile Character
                    </button>
                </div>
            {/each}
        </div>
    </div>
</div>

<style>
    .gacha-history {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }

    .pull-card {
        border: 1px solid #ccc;
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }

    .pull-card img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
    }

    button {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background: #45a049;
    }
</style> 