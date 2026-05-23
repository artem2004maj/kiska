<!-- resources/js/Layouts/DoctorLayout.vue -->
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
                                <p class="font-medium text-gray-800">{{ doctor?.employee_name }}</p>
                                <p class="text-xs text-gray-500">Врач-косметолог</p>
                            </div>
                        </div>
                        
                        <nav class="p-3 space-y-1">
                            <Link href="/dashboard/doctor" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/dashboard/doctor') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">📅</span>
                                <span>Главная</span>
                            </Link>
                            
                            <Link href="/doctor/patients" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/doctor/patients') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">👥</span>
                                <span>Мои пациенты</span>
                            </Link>
                            
                            <Link href="/doctor/services" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/doctor/services') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
                                <span class="text-lg">💼</span>
                                <span>Мои услуги</span>
                            </Link>
                            
                            <Link href="/doctor/profile" 
                                  class="flex items-center gap-3 px-3 py-2 rounded-lg transition"
                                  :class="isActive('/doctor/profile') ? 'bg-[#14b8a6] text-white' : 'text-gray-700 hover:bg-[#14b8a6]/10'">
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
                        <span class="font-medium text-sm">{{ doctor?.employee_name?.split(' ')[0] || 'Врач' }}</span>
                        <div class="relative" ref="mobileProfileMenu">
                            <button @click="mobileProfileMenuOpen = !mobileProfileMenuOpen" 
                                    class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center overflow-hidden">
                                <img v-if="doctor?.photo_url" 
                                     :src="doctor.photo_url" 
                                     :alt="doctor.employee_name"
                                     class="w-full h-full object-cover">
                                <span v-else class="text-xs font-medium text-[#14b8a6]">
                                    {{ getInitials(doctor?.employee_name) }}
                                </span>
                            </button>
                            
                            <div v-if="mobileProfileMenuOpen" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-1 z-50 text-sm border border-gray-100">
                                <Link href="/doctor/profile" class="block px-3 py-2 hover:bg-gray-100">Профиль</Link>
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
                    <!-- Десктопная шапка -->
                    <header class="hidden lg:flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-semibold text-gray-800 truncate">
                            Здравствуйте, {{ doctor?.employee_name?.split(' ')[0] }}!
                        </h1>
                        
                        <div class="relative" ref="profileMenu">
                            <button @click="showMenu = !showMenu" class="flex items-center gap-2 text-sm">
                                <span class="hidden xl:inline text-gray-700">{{ doctor?.employee_name }}</span>
                                <div class="w-8 h-8 rounded-full bg-[#14b8a6]/10 flex items-center justify-center overflow-hidden">
                                    <img v-if="doctor?.photo_url" 
                                         :src="doctor.photo_url" 
                                         :alt="doctor.employee_name"
                                         class="w-full h-full object-cover">
                                    <span v-else class="text-xs font-medium text-[#14b8a6]">
                                        {{ getInitials(doctor?.employee_name) }}
                                    </span>
                                </div>
                            </button>
                            
                            <div v-if="showMenu" class="absolute right-0 mt-2 w-40 bg-white rounded-lg shadow-lg py-1 z-50 text-sm border border-gray-100">
                                <Link href="/doctor/profile" class="block px-3 py-2 hover:bg-gray-50">Профиль</Link>
                                <hr class="my-1">
                                <button @click="logout" class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Выйти
                                </button>
                            </div>
                        </div>
                    </header>

                    <!-- Поиск для мобильных -->
                    <div class="lg:hidden mb-3">
                        <input type="text" v-model="searchQuery" @keyup.enter="searchPatients"
                               placeholder="Поиск пациента..." 
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                    </div>

                    <slot />
                </main>
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
const searchQuery = ref('');

const isActive = (path) => {
    return usePage().url === path 
        ? 'bg-[#14b8a6] text-white' 
        : 'text-gray-700 hover:bg-[#14b8a6]/10';
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