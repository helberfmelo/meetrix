<template>
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Stat Card -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-gray-500 text-sm font-medium">Total Bookings</h3>
                <p class="text-2xl font-bold text-gray-800 mt-2">12</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-gray-500 text-sm font-medium">Active Pages</h3>
                <p class="text-2xl font-bold text-gray-800 mt-2">3</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h3 class="text-gray-500 text-sm font-medium">Upcoming</h3>
                <p class="text-2xl font-bold text-gray-800 mt-2">5</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Bookings</h2>
            <div class="text-gray-500 text-sm">No bookings yet.</div>
        </div>
    </div>
</template>
