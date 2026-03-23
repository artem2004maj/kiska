<!-- resources/js/Layouts/AccountantLayout.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px] pointer-events-none hidden lg:block"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#3b82f6] selection:text-white">
            <div class="relative w-full max-w-7xl px-3 sm:px-4 lg:px-6 mx-auto">
                <div class="flex flex-col lg:flex-row gap-3 lg:gap-6 py-3 lg:py-6">
                    <!-- Мобильная шапка -->
                    <div class="lg:hidden flex items-center justify-between mb-2 bg-white p-3 rounded-lg shadow-sm">
                        <button @click="mobileMenuOpen = true" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ accountant?.employee_name?.split(' ')[0] || 'Бухгалтер' }}</span>
                            <div class="relative" ref="mobileProfileMenu">
                                <button @click="mobileProfileMenuOpen = !mobileProfileMenuOpen" 
                                        class="w-8 h-8 rounded-full bg-[#3b82f6]/10 flex items-center justify-center overflow-hidden">
                                    <span class="text-xs font-medium text-[#3b82f6]">
                                        {{ getInitials(accountant?.employee_name) }}
                                    </span>
                                </button>
                                
                                <div v-if="mobileProfileMenuOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/accountant/profile" class="block px-3 py-2 hover:bg-gray-100">Профиль</Link>
                                    <Link href="/" class="block px-3 py-2 hover:bg-gray-100">На главную</Link>
                                    <hr class="my-1" />
                                    <button @click="logout" class="block w-full text-left px-3 py-2 text-red-600 hover:bg-red-50">
                                        Выйти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Сайдбар (десктопный) -->
                    <aside class="hidden lg:block lg:w-56 xl:w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#3b82f6] lg:h-16" viewBox="0 0 62 65" fill="none">
                                    <path d="M61.8548 14.6253..."/> 
                                </svg>
                            </div>
                            
                            <nav class="space-y-1">
                                <Link href="/dashboard/accountant" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/dashboard/accountant')">
                                    <span class="text-lg lg:text-xl">📊</span>
                                    <span>Главная</span>
                                    <span v-if="unpaidCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ unpaidCount }}</span>
                                </Link>
                                
                                <!-- ИЗМЕНЕНО: payments -> incomes -->
                                <Link href="/accountant/incomes" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/accountant/incomes')">
                                    <span class="text-lg lg:text-xl">💰</span>
                                    <span>Доходы</span>
                                </Link>
                                
                                <Link href="/accountant/warehouse" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/accountant/warehouse')">
                                    <span class="text-lg lg:text-xl">📦</span>
                                    <span>Склад</span>
                                    <span v-if="criticalCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ criticalCount }}</span>
                                </Link>

                                <Link href="/accountant/orders" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                    :class="isActive('/accountant/orders')">
                                    <span class="text-lg lg:text-xl">🚚</span>
                                    <span>Поставка</span>
                                </Link>

                                <Link href="/accountant/suppliers" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                    :class="isActive('/accountant/suppliers')">
                                    <span class="text-lg lg:text-xl">🏭</span>
                                    <span>Поставщики</span>
                                </Link>
                                
                                <Link href="/accountant/salary" 
                                      class="flex items-center gap-3 px-3 py-2 rounded-md transition text-sm lg:text-base"
                                      :class="isActive('/accountant/salary')">
                                    <span class="text-lg lg:text-xl">💵</span>
                                    <span>Зарплата</span>
                                </Link>
                            </nav>
                            
                            <!-- Быстрая статистика в сайдбаре -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-black dark:text-white/70">Выручка сегодня:</span>
                                        <span class="font-medium text-green-600">{{ formatPrice(todayRevenue) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-black dark:text-white/70">К оплате:</span>
                                        <span class="font-medium text-blue-600">{{ formatPrice(pendingPayments) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Мобильное боковое меню -->
                    <Transition name="slide">
                        <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40" @click="mobileMenuOpen = false">
                            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
                            <div class="absolute left-0 top-0 h-full w-64 bg-white shadow-xl" @click.stop>
                                <div class="p-4 border-b">
                                    <p class="font-medium">{{ accountant?.employee_name || 'Бухгалтер' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ accountant?.role }}</p>
                                </div>
                                <nav class="p-3 space-y-1">
                                    <Link href="/dashboard/accountant" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/dashboard/accountant')">
                                        <span class="text-lg">📊</span> Главная
                                    </Link>
                                    <!-- ИЗМЕНЕНО: payments -> incomes -->
                                    <Link href="/accountant/incomes" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/accountant/incomes')">
                                        <span class="text-lg">💰</span> Доходы
                                    </Link>
                                    <Link href="/accountant/warehouse" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/accountant/warehouse')">
                                        <span class="text-lg">📦</span> Склад
                                    </Link>
                                    <Link href="/accountant/orders" @click="mobileMenuOpen = false"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                        :class="isActive('/accountant/orders')">
                                        <span class="text-lg">🚚</span> Поставка
                                    </Link>
                                    <Link href="/accountant/suppliers" @click="mobileMenuOpen = false"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                        :class="isActive('/accountant/suppliers')">
                                        <span class="text-lg">🏭</span> Поставщики
                                    </Link>
                                    <Link href="/accountant/salary" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/accountant/salary')">
                                        <span class="text-lg">💵</span> Зарплата
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
                        <header class="hidden lg:flex items-center justify-between mb-4">
                            <h1 class="text-xl lg:text-2xl xl:text-3xl font-semibold text-black dark:text-white truncate">
                                {{ pageTitle }}
                            </h1>
                            
                            <div class="relative" ref="profileMenu">
                                <button @click="showProfileMenu = !showProfileMenu" class="flex items-center gap-2 text-sm">
                                    <span class="hidden xl:inline">{{ accountant?.employee_name || 'Бухгалтер' }}</span>
                                    <div class="w-8 h-8 rounded-full bg-[#3b82f6]/10 flex items-center justify-center overflow-hidden">
                                        <span class="text-xs font-medium text-[#3b82f6]">
                                            {{ getInitials(accountant?.employee_name) }}
                                        </span>
                                    </div>
                                </button>
                                
                                <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-40 bg-white dark:bg-zinc-900 rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/accountant/profile" class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-800">Профиль</Link>
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
    accountant: Object,
    pageTitle: String,
    unpaidCount: {
        type: Number,
        default: 0
    },
    criticalCount: {
        type: Number,
        default: 0
    },
    todayRevenue: {
        type: Number,
        default: 0
    },
    pendingPayments: {
        type: Number,
        default: 0
    }
});

const currentRoute = computed(() => usePage().url);
const showProfileMenu = ref(false);
const profileMenu = ref(null);
const mobileProfileMenu = ref(null);
const mobileMenuOpen = ref(false);
const mobileProfileMenuOpen = ref(false);

const isActive = (path) => {
    return currentRoute.value === path ? 'bg-[#3b82f6] text-white' : 'text-gray-700 dark:text-white/70 hover:bg-[#3b82f6]/10';
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
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