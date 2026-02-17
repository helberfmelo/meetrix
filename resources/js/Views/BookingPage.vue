<template>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden flex flex-col md:flex-row h-full md:h-[600px]">
             <!-- Left Panel: Page Info -->
             <div class="w-full md:w-1/3 bg-gray-50 p-8 border-r border-gray-100">
                <div class="mb-6">
                    <img class="h-16 w-16 rounded-full mb-4" src="https://ui-avatars.com/api/?name=Meetrix+User" alt="User Avatar">
                    <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">Meetrix User</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-2">Consultation Call</h1>
                </div>
                
                <div class="space-y-4 text-gray-600">
                    <div class="flex items-center">
                        <span class="mr-2">⏱️</span> 30 min
                    </div>
                    <div class="flex items-center">
                        <span class="mr-2">📹</span> Google Meet
                    </div>
                </div>
                
                <p class="mt-8 text-gray-500 text-sm">
                    Book a time to discuss how we can help your business grow.
                </p>
             </div>

             <!-- Right Panel: Calendar & Slots -->
             <div class="w-full md:w-2/3 p-8 overflow-y-auto">
                <h2 class="text-lg font-semibold text-gray-800 mb-6">Select a Date & Time</h2>
                
                <!-- Mock Calendar Grid -->
                <div class="grid grid-cols-7 gap-1 mb-6 text-center text-sm">
                    <div class="text-gray-400">Sun</div>
                    <div class="text-gray-400">Mon</div>
                    <div class="text-gray-400">Tue</div>
                    <div class="text-gray-400">Wed</div>
                    <div class="text-gray-400">Thu</div>
                    <div class="text-gray-400">Fri</div>
                    <div class="text-gray-400">Sat</div>
                    
                    <!-- Days -->
                    <div v-for="n in 31" :key="n" class="p-2 hover:bg-blue-50 cursor-pointer rounded">
                        {{ n }}
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-md font-medium text-gray-700 mb-4">Available Slots</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <button class="border border-blue-600 text-blue-600 py-2 rounded hover:bg-blue-600 hover:text-white transition">09:00 AM</button>
                        <button class="border border-blue-600 text-blue-600 py-2 rounded hover:bg-blue-600 hover:text-white transition">09:30 AM</button>
                        <button class="border border-blue-600 text-blue-600 py-2 rounded hover:bg-blue-600 hover:text-white transition">10:00 AM</button>
                    </div>
                </div>
             </div>
        </div>
    </div>
</template>
