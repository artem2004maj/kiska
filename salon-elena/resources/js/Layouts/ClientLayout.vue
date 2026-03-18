<!-- resources/js/Layouts/ClientLayout.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px] pointer-events-none hidden lg:block"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#14b8a6] selection:text-white">
            <div class="relative w-full max-w-7xl px-3 sm:px-4 lg:px-6 mx-auto">
                <div class="flex flex-col lg:flex-row gap-3 lg:gap-6 py-3 lg:py-6">
                    <!-- Мобильная шапка (НОВОЕ) -->
                    <div class="lg:hidden flex items-center justify-between mb-2 bg-white p-3 rounded-lg shadow-sm">
                        <button @click="mobileMenuOpen = true" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ client?.client_name?.split(' ')[0] || 'Клиент' }}</span>
                            <div class="relative" ref="mobileProfileMenu">
                                <button @click="mobileProfileMenuOpen = !mobileProfileMenuOpen" class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center">
                                    <span class="text-xs font-medium text-[#14b8a6]">{{ getInitials(client?.client_name) }}</span>
                                </button>
                                
                                <div v-if="mobileProfileMenuOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/client/profile" class="block px-3 py-2 hover:bg-gray-100">Профиль</Link>
                                    <Link href="/" class="block px-3 py-2 hover:bg-gray-100">На главную</Link>
                                    <hr class="my-1" />
                                    <button @click="logout" class="block w-full text-left px-3 py-2 text-red-600 hover:bg-red-50">
                                        Выйти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Сайдбар (десктопный) - адаптирован -->
                    <aside class="hidden lg:block lg:w-56 xl:w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#14b8a6] lg:h-16" viewBox="0 0 62 65" fill="none">
                                    <path d="M61.8548 14.6253..."/> 
                                </svg>
                            </div>
                            
                            <nav class="space-y-1">
                                <Link href="/dashboard/client" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/dashboard/client')">
                                    <span class="text-lg lg:text-xl">🏠</span>
                                    <span>Главная</span>
                                </Link>
                                
                                <Link href="/client/services" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/client/services')">
                                    <span class="text-lg lg:text-xl">💆‍♀️</span>
                                    <span>Услуги</span>
                                </Link>
                                
                                <Link href="/client/appointments" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/client/appointments')">
                                    <span class="text-lg lg:text-xl">📅</span>
                                    <span>Мои записи</span>
                                </Link>
                                
                                <Link href="/client/history" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/client/history')">
                                    <span class="text-lg lg:text-xl">📜</span>
                                    <span>История</span>
                                </Link>
                                
                                <Link href="/client/medical-records" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/client/medical-records')">
                                    <span class="text-lg lg:text-xl">🏥</span>
                                    <span>Медкарта</span>
                                </Link>
                                
                                <Link href="/client/profile" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/client/profile')">
                                    <span class="text-lg lg:text-xl">👤</span>
                                    <span>Профиль</span>
                                </Link>
                            </nav>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                                <div class="text-sm text-black dark:text-white/70">
                                    <p class="font-medium truncate">{{ client?.client_name || 'Клиент' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">ID: {{ client?.client_id || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Мобильное боковое меню (НОВОЕ) -->
                    <Transition name="slide">
                        <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40" @click="mobileMenuOpen = false">
                            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
                            <div class="absolute left-0 top-0 h-full w-64 bg-white shadow-xl" @click.stop>
                                <div class="p-4 border-b">
                                    <p class="font-medium">{{ client?.client_name || 'Клиент' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">ID: {{ client?.client_id || '—' }}</p>
                                </div>
                                <nav class="p-3 space-y-1">
                                    <Link href="/dashboard/client" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/dashboard/client')">
                                        <span class="text-lg">🏠</span> Главная
                                    </Link>
                                    <Link href="/client/services" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/client/services')">
                                        <span class="text-lg">💆‍♀️</span> Услуги
                                    </Link>
                                    <Link href="/client/appointments" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/client/appointments')">
                                        <span class="text-lg">📅</span> Записи
                                    </Link>
                                    <Link href="/client/history" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/client/history')">
                                        <span class="text-lg">📜</span> История
                                    </Link>
                                    <Link href="/client/medical-records" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/client/medical-records')">
                                        <span class="text-lg">🏥</span> Медкарта
                                    </Link>
                                    <Link href="/client/profile" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/client/profile')">
                                        <span class="text-lg">👤</span> Профиль
                                    </Link>
                                    <hr class="my-2">
                                    <button @click="logout" class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm text-red-600">
                                        <span class="text-lg">🚪</span> Выйти
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </Transition>

                    <!-- Основной контент -->
                    <main class="flex-1 min-w-0">
                        <!-- Десктопная шапка - адаптирована -->
                        <header class="hidden lg:flex items-center justify-between mb-4">
                            <h1 class="text-xl lg:text-2xl xl:text-3xl font-semibold text-black dark:text-white truncate">
                                Здравствуйте, {{ client?.client_name?.split(' ')[0] || 'Клиент' }}!
                            </h1>
                            
                            <div class="relative" ref="profileMenu">
                                <button @click="showProfileMenu = !showProfileMenu" class="flex items-center gap-2 text-sm">
                                    <span class="hidden xl:inline">{{ client?.client_name || 'Клиент' }}</span>
                                    <div class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center">
                                        <span class="text-xs font-medium text-[#14b8a6]">{{ getInitials(client?.client_name) }}</span>
                                    </div>
                                </button>
                                
                                <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-40 bg-white dark:bg-zinc-900 rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/client/profile" class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-800">Профиль</Link>
                                    <Link href="/" class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-800">На главную</Link>
                                    <hr class="my-1 border-gray-200 dark:border-zinc-700" />
                                    <button @click="logout" class="block w-full text-left px-3 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        Выйти
                                    </button>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    client: Object
});

const currentRoute = computed(() => usePage().url);
const showProfileMenu = ref(false);
const profileMenu = ref(null);
const mobileProfileMenu = ref(null);
const mobileMenuOpen = ref(false);
const mobileProfileMenuOpen = ref(false);

const isActive = (path) => {
    return currentRoute.value === path ? 'bg-[#14b8a6] text-white' : 'text-gray-700 dark:text-white/70 hover:bg-[#14b8a6]/10';
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const logout = () => {
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
    if (mobileProfileMenu.value && !mobileProfileMenu.value.contains(event.target)) {
        mobileProfileMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
.slide-enter-to, .slide-leave-from { transform: translateX(0); }
</style>