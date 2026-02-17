<template>
    <div class="min-h-screen bg-white">
        <!-- Minimal Header for Public Pages -->
        <header class="border-b border-gray-100 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <div class="text-2xl font-bold text-blue-600">Meetrix</div>
                <nav>
                    <router-link to="/login" class="text-gray-600 hover:text-gray-900 px-3">Login</router-link>
                </nav>
            </div>
        </header>

        <main>
            <router-view></router-view>
        </main>

        <footer class="bg-gray-50 border-t border-gray-100 py-8 mt-12">
            <div class="max-w-7xl mx-auto px-4 text-center text-gray-500">
                &copy; {{ new Date().getFullYear() }} Meetrix. All rights reserved.
            </div>
        </footer>
    </div>
</template>
