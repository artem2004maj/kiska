<!-- resources/js/Layouts/DoctorLayout.vue -->
<template>
    <div class="bg-gray-50 min-h-screen">
        <img id="background" class="absolute -left-20 top-0 max-w-[877px] pointer-events-none hidden lg:block"
             src="https://laravel.com/assets/img/welcome/background.svg" />

        <div class="relative flex min-h-screen flex-col">
            <div class="relative w-full px-3 sm:px-4 lg:px-6 mx-auto max-w-7xl">
                <div class="flex flex-col lg:flex-row gap-3 lg:gap-6 py-3 lg:py-6">
                    <!-- Мобильная шапка (НОВОЕ) -->
                    <div class="lg:hidden flex items-center justify-between mb-2 bg-white p-3 rounded-lg shadow-sm">
                        <button @click="mobileMenuOpen = true" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ doctor?.employee_name?.split(' ')[0] || 'Врач' }}</span>
                            <div class="relative" ref="mobileProfileMenu">
                                <button @click="mobileProfileMenuOpen = !mobileProfileMenuOpen" class="w-8 h-8 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center">
                                    <span class="text-xs font-medium text-[#2A7F6E]">{{ getInitials(doctor?.employee_name) }}</span>
                                </button>
                                
                                <div v-if="mobileProfileMenuOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/doctor/profile" class="block px-3 py-2 hover:bg-gray-100">Профиль</Link>
                                    <hr class="my-1" />
                                    <button @click="logout" class="block w-full text-left px-3 py-2 text-red-600 hover:bg-red-50">
                                        Выйти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Сайдбар (десктопный) - без изменений -->
                    <aside class="hidden lg:block lg:w-56 xl:w-64 shrink-0">
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
                                <p class="font-medium truncate">{{ doctor?.employee_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ doctor?.role }}</p>
                            </div>
                        </div>
                    </aside>

                    <!-- Мобильное боковое меню (НОВОЕ) -->
                    <Transition name="slide">
                        <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40" @click="mobileMenuOpen = false">
                            <div class="absolute inset-0 bg-black bg-opacity-50" @click="mobileMenuOpen = false"></div>
                            <div class="absolute left-0 top-0 h-full w-64 bg-white shadow-xl" @click.stop>
                                <div class="p-4 border-b">
                                    <p class="font-medium">{{ doctor?.employee_name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ doctor?.role }}</p>
                                </div>
                                <nav class="p-3 space-y-1">
                                    <Link href="/dashboard/doctor" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/dashboard/doctor')">
                                        <span class="text-lg">📅</span> Главная
                                    </Link>
                                    <Link href="/doctor/patients" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/doctor/patients')">
                                        <span class="text-lg">👥</span> Пациенты
                                    </Link>
                                    <Link href="/doctor/services" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/doctor/services')">
                                        <span class="text-lg">💼</span> Услуги
                                    </Link>
                                    <Link href="/doctor/profile" @click="mobileMenuOpen = false"
                                          class="flex items-center gap-3 px-3 py-2.5 rounded-md w-full text-sm"
                                          :class="isActive('/doctor/profile')">
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
                            <h1 class="text-xl lg:text-2xl xl:text-3xl font-semibold truncate">
                                Здравствуйте, {{ doctor?.employee_name?.split(' ')[0] }}!
                            </h1>
                            
                            <div class="relative" ref="profileMenu">
                                <button @click="showMenu = !showMenu" class="flex items-center gap-2 text-sm">
                                    <span class="hidden xl:inline">{{ doctor?.employee_name }}</span>
                                    <div class="w-8 h-8 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center">
                                        <span class="text-xs font-medium text-[#2A7F6E]">{{ getInitials(doctor?.employee_name) }}</span>
                                    </div>
                                </button>
                                
                                <div v-if="showMenu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-xl py-1 z-50 text-sm">
                                    <Link href="/doctor/profile" class="block px-3 py-2 hover:bg-gray-100">Профиль</Link>
                                    <hr class="my-1" />
                                    <button @click="logout" class="block w-full text-left px-3 py-2 text-red-600 hover:bg-red-50">
                                        Выйти
                                    </button>
                                </div>
                            </div>
                        </header>

                        <!-- Поиск для мобильных (НОВОЕ) -->
                        <div class="lg:hidden mb-3">
                            <input type="text" v-model="searchQuery" @keyup.enter="searchPatients"
                                   placeholder="Поиск пациента..." 
                                   class="w-full px-3 py-2 text-sm border rounded-lg">
                        </div>

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
const mobileProfileMenu = ref(null);
const mobileMenuOpen = ref(false);
const mobileProfileMenuOpen = ref(false);
const searchQuery = ref(''); // Для мобильного поиска

const isActive = (path) => {
    return usePage().url === path ? 'bg-[#2A7F6E] text-white' : 'text-gray-700 hover:bg-[#2A7F6E]/10';
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const logout = () => router.post('/logout');

const searchPatients = async () => {
    if (!searchQuery.value.trim()) return;
    router.get(`/doctor/patients?search=${searchQuery.value}`);
};

const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) showMenu.value = false;
    if (mobileProfileMenu.value && !mobileProfileMenu.value.contains(event.target)) mobileProfileMenuOpen.value = false;
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
.slide-enter-to, .slide-leave-from { transform: translateX(0); }
</style>