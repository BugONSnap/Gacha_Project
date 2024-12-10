<script lang="ts">
    import { goto } from '$app/navigation';
    let username = '';
    let password = '';
    let errorMessage = '';

    async function handleLogin() {
        try {
            const response = await fetch('http://localhost/Gacha_Project/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();
            
            if (data.success) {
                localStorage.setItem('user', JSON.stringify(data.user));
                if (data.user.role === 1) {
                    goto('/create_gacha');
                } else {
                    goto('/gacha');
                }
            } else {
                errorMessage = data.error || 'Login failed';
            }
        } catch (error) {
            errorMessage = 'Network error occurred';
            console.error('Login error:', error);
        }
    }
</script>

<main class="min-h-screen flex items-center justify-center">
    <div class="bg-white/80 backdrop-blur-sm p-8 rounded-lg shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
        
        {#if errorMessage}
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {errorMessage}
            </div>
        {/if}

        <form on:submit|preventDefault={handleLogin} class="space-y-4">
            <div>
                <label class="block text-gray-700">Username</label>
                <input
                    type="text"
                    bind:value={username}
                    class="w-full px-3 py-2 border rounded-lg"
                    required
                />
            </div>

            <div>
                <label class="block text-gray-700">Password</label>
                <input
                    type="password"
                    bind:value={password}
                    class="w-full px-3 py-2 border rounded-lg"
                    required
                />
            </div>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600"
            >
                Login
            </button>
        </form>

        <p class="mt-4 text-center">
            Don't have an account? 
            <a href="/register" class="text-blue-500 hover:underline">Register</a>
        </p>
    </div>
</main>

<style>
    /* Add any additional styles here */
</style>