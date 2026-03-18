<!-- resources/js/Layouts/ClientLayout.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#14b8a6] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <!-- Логотип -->
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#14b8a6] lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M61.8548 14.6253..."/> 
                                </svg>
                            </div>
                            
                            <!-- Навигация -->
                            <nav class="space-y-1">
                                <Link 
                                    href="/dashboard/client" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/dashboard/client' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">🏠</span>
                                    <span>Главная</span>
                                </Link>
                                
                                <Link 
                                    href="/client/services" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/services' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">💆‍♀️</span>
                                    <span>Услуги</span>
                                </Link>
                                
                                <Link 
                                    href="/client/appointments" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/appointments' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">📅</span>
                                    <span>Мои записи</span>
                                </Link>
                                
                                <Link 
                                    href="/client/history" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/history' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">📜</span>
                                    <span>История</span>
                                </Link>
                                
                                <Link 
                                    href="/client/medical-records" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/medical-records' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">🏥</span>
                                    <span>Медкарта</span>
                                </Link>
                                
                                <Link 
                                    href="/client/profile" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/profile' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">👤</span>
                                    <span>Профиль</span>
                                </Link>
                            </nav>
                            
                            <!-- Информация о клиенте -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                                <div class="text-sm text-black dark:text-white/70">
                                    <p class="font-medium">{{ client?.client_name || 'Клиент' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">ID: {{ client?.client_id || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <!-- Шапка -->
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <h1 class="text-3xl font-semibold text-black dark:text-white">
                                    Здравствуйте, {{ client?.client_name || 'Клиент' }}!
                                </h1>
                                
                                <div class="relative" ref="profileMenu">
                                    <button @click="toggleProfileMenu" class="flex items-center gap-3 cursor-pointer">
                                        <span class="text-black dark:text-white font-medium">{{ client?.client_name || 'Клиент' }}</span>
                                        <div class="flex size-10 items-center justify-center rounded-full bg-[#14b8a6]/10">
                                            <svg class="size-5 text-[#14b8a6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </div>
                                    </button>
                                    
                                    <!-- Выпадающее меню -->
                                    <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50">
                                        <Link href="/client/profile" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#14b8a6]/10">Профиль</Link>
                                        <Link href="/" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#14b8a6]/10">На главную</Link>
                                        <hr class="my-1 border-gray-200 dark:border-zinc-700" />
                                        <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Выйти</button>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <!-- Слот для контента -->
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    client: Object
});

const currentRoute = computed(() => usePage().url);
const showProfileMenu = ref(false);
const profileMenu = ref(null);

const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const logout = () => {
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>