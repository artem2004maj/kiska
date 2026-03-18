<!-- resources/js/Layouts/DoctorLayout.vue -->
<template>
    <div class="bg-gray-50 min-h-screen">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
             src="https://laravel.com/assets/img/welcome/background.svg" />

        <div class="relative flex min-h-screen flex-col">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow sticky top-10">
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#2A7F6E]" viewBox="0 0 62 65">
                                    <path d="M61.8548 14.6253..."/> 
                                </svg>
                            </div>
                            
                            <nav class="space-y-1">
                                <Link href="/dashboard/doctor" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                      :class="isActive('/dashboard/doctor')">
                                    <span class="text-xl">📅</span>
                                    <span>Главная (График)</span>
                                </Link>
                                
                                <Link href="/doctor/patients" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                      :class="isActive('/doctor/patients')">
                                    <span class="text-xl">👥</span>
                                    <span>Мои пациенты</span>
                                </Link>
                                
                                <Link href="/doctor/services" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                      :class="isActive('/doctor/services')">
                                    <span class="text-xl">💼</span>
                                    <span>Мои услуги</span>
                                </Link>
                                
                                <Link href="/doctor/profile" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                      :class="isActive('/doctor/profile')">
                                    <span class="text-xl">👤</span>
                                    <span>Профиль</span>
                                </Link>
                            </nav>
                            
                            <div class="mt-6 pt-6 border-t">
                                <p class="font-medium">{{ doctor?.employee_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ doctor?.role }}</p>
                            </div>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <h1 class="text-3xl font-semibold">
                                    Здравствуйте, {{ doctor?.employee_name }}!
                                </h1>
                                
                                <div class="relative" ref="profileMenu">
                                    <button @click="toggleMenu" class="flex items-center gap-3">
                                        <span>{{ doctor?.employee_name }}</span>
                                        <div class="size-10 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center">
                                            <svg class="size-5 text-[#2A7F6E]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </div>
                                    </button>
                                    
                                    <div v-if="showMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-1 z-50">
                                        <Link href="/doctor/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">Профиль</Link>
                                        <hr class="my-1" />
                                        <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                            Выйти
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    doctor: Object
});

const showMenu = ref(false);
const profileMenu = ref(null);

const isActive = (path) => {
    return usePage().url === path ? 'bg-[#2A7F6E] text-white' : 'text-gray-700 hover:bg-[#2A7F6E]/10';
};

const toggleMenu = () => {
    showMenu.value = !showMenu.value;
};

const logout = () => {
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>