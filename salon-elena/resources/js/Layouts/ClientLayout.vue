<!-- resources/js/Layouts/ClientLayout.vue -->
<template>
    <div class="min-h-screen bg-gray-50">
        <div class="relative max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-3 lg:py-6">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Сайдбар (десктопный) -->
                <aside class="hidden lg:block lg:w-64 shrink-0">
                    <div class="bg-white rounded-xl shadow-sm sticky top-6 overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex justify-center mb-4">
                                <svg class="h-10 w-auto text-[#14b8a6]" viewBox="0 0 62 65" fill="none">
                                    <path d="M61.8548 14.6253..." fill="currentColor"/>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="font-medium text-gray-800">{{ client?.client_name }}</p>
                                <p class="text-xs text-gray-500">Клиент</p>
                            </div>
                        </div>
                        
                        <nav class="p-3 space-y-1">
                            <Link href="/dashboard/client" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/dashboard/client') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">🏠</span>
                                <span>Главная</span>
                            </Link>
                            
                            <Link href="/client/services" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/client/services') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">💆‍♀️</span>
                                <span>Услуги</span>
                            </Link>
                            
                            <Link href="/client/appointments" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/client/appointments') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">📅</span>
                                <span>Мои записи</span>
                            </Link>
                            
                            <Link href="/client/history" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/client/history') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">📜</span>
                                <span>История</span>
                            </Link>
                            
                            <Link href="/client/medical-records" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/client/medical-records') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">🏥</span>
                                <span>Медкарта</span>
                            </Link>
                            
                            <Link href="/client/profile" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/client/profile') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">👤</span>
                                <span>Профиль</span>
                            </Link>
                        </nav>
                    </div>
                </aside>

                <!-- Мобильная шапка -->
                <div class="lg:hidden flex items-center justify-between mb-2 bg-white p-3 rounded-lg shadow-sm">
                    <button @click="mobileMenuOpen = true" class="p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-sm">{{ client?.client_name?.split(' ')[0] || 'Клиент' }}</span>
                        <div class="relative" ref="mobileProfileMenu">
                            <button @click="mobileProfileMenuOpen = !mobileProfileMenuOpen" 
                                    class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center overflow-hidden">
                                <img v-if="client?.photo_url" 
                                     :src="client.photo_url" 
                                     :alt="client.client_name"
                                     class="w-full h-full object-cover">
                                <span v-else class="text-xs font-medium text-[#14b8a6]">
                                    {{ getInitials(client?.client_name) }}
                                </span>
                            </button>
                            
                            <div v-if="mobileProfileMenuOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-1 z-50 text-sm border border-gray-100">
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

                <!-- Мобильное боковое меню -->
                <Transition name="slide">
                    <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40" @click="mobileMenuOpen = false">
                        <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
                        <div class="absolute left-0 top-0 h-full w-64 bg-white shadow-xl" @click.stop>
                            <div class="p-4 border-b border-gray-100">
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
                    <!-- Десктопная шапка -->
                    <header class="hidden lg:flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-semibold text-gray-800 truncate">
                            Здравствуйте, {{ client?.client_name?.split(' ')[0] || 'Клиент' }}!
                        </h1>
                        
                        <div class="relative" ref="profileMenu">
                            <button @click="showProfileMenu = !showProfileMenu" class="flex items-center gap-2 text-sm">
                                <span class="hidden xl:inline text-gray-700">{{ client?.client_name || 'Клиент' }}</span>
                                <div class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center overflow-hidden">
                                    <img v-if="client?.photo_url" 
                                         :src="client.photo_url" 
                                         :alt="client.client_name"
                                         class="w-full h-full object-cover">
                                    <span v-else class="text-xs font-medium text-[#14b8a6]">
                                        {{ getInitials(client?.client_name) }}
                                    </span>
                                </div>
                            </button>
                            
                            <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-1 z-50 text-sm border border-gray-100">
                                <Link href="/client/profile" class="block px-3 py-2 hover:bg-gray-50">Профиль</Link>
                                <Link href="/" class="block px-3 py-2 hover:bg-gray-50">На главную</Link>
                                <hr class="my-1">
                                <button @click="logout" class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
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
    return currentRoute.value === path 
        ? 'bg-[#14b8a6] text-white' 
        : 'text-gray-700 hover:bg-[#14b8a6]/10';
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