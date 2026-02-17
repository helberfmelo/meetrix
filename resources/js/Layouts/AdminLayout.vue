<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200">
            <div class="p-6">
                <div class="text-2xl font-bold text-blue-600">Meetrix</div>
            </div>
            <nav class="mt-6">
                <router-link to="/dashboard" class="block px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                    Dashboard
                </router-link>
                <router-link to="/pages" class="block px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                    Pages
                </router-link>
                <router-link to="/bookings" class="block px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                    Bookings
                </router-link>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <header class="flex justify-end mb-8">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">User</span>
                    <button class="text-sm text-red-600 hover:text-red-800">Logout</button>
                </div>
            </header>
            <router-view></router-view>
        </main>
    </div>
</template>
