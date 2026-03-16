<!-- resources/js/Pages/Dashboard/Client.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <!-- Background изображение -->
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#14b8a6] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <!-- Шапка с сайдбаром и основным контентом -->
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <!-- Логотип -->
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#14b8a6] lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M61.8548 14.6253..."/> <!-- Ваш логотип -->
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
                                    <p class="font-medium">{{ clientData?.client_name || 'Клиент' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">ID: {{ clientData?.client_id || '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <!-- Шапка -->
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <!-- Приветствие -->
                                <h1 class="text-3xl font-semibold text-black dark:text-white">
                                    Здравствуйте, {{ clientData?.client_name || 'Клиент' }}!
                                </h1>
                                
                                <!-- Правая часть шапки -->
                                <div class="flex items-center gap-4">
                                    <!-- Профиль с выпадающим меню -->
                                    <div class="relative" ref="profileMenu">
                                        <button @click="toggleProfileMenu" class="flex items-center gap-3 cursor-pointer">
                                            <span class="text-black dark:text-white font-medium">{{ clientData?.client_name || 'Клиент' }}</span>
                                            <div class="flex size-10 items-center justify-center rounded-full bg-[#14b8a6]/10">
                                                <svg class="size-5 text-[#14b8a6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                            </div>
                                        </button>
                                        
                                        <!-- Выпадающее меню -->
                                        <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50">
                                            <Link href="/client/profile" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#14b8a6]/10">Профиль</Link>
                                            <Link href="/client/settings" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#14b8a6]/10">Настройки</Link>
                                            <hr class="my-1 border-gray-200 dark:border-zinc-700" />
                                            <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Выйти</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <!-- Контент главной страницы -->
                        <div v-if="currentRoute === '/dashboard/client'">
                            <!-- Статистика -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Всего посещений</p>
                                            <p class="text-2xl font-semibold text-black dark:text-white">{{ stats.totalVisits || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-blue-100 rounded-full">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Предстоящих записей</p>
                                            <p class="text-2xl font-semibold text-green-600">{{ stats.upcomingAppointments || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-green-100 rounded-full">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Отзывов оставлено</p>
                                            <p class="text-2xl font-semibold text-[#14b8a6]">{{ stats.totalReviews || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-[#14b8a6]/10 rounded-full">
                                            <svg class="w-6 h-6 text-[#14b8a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Ближайшая запись -->
                            <div class="mb-8">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">БЛИЖАЙШАЯ ЗАПИСЬ</h2>
                                <div v-if="nearestAppointment" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="grid gap-4">
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Дата:</span>
                                            <span>{{ formatDate(nearestAppointment.date) }}</span>
                                        </div>
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Время:</span>
                                            <span>{{ formatTime(nearestAppointment.date) }}</span>
                                        </div>
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Услуга:</span>
                                            <span>{{ getServiceNames(nearestAppointment) }}</span>
                                        </div>
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Врач:</span>
                                            <span>{{ nearestAppointment.employee?.employee_name || 'Не указан' }}</span>
                                        </div>
                                        <div class="flex gap-4 mt-4">
                                            <button @click="cancelAppointment(nearestAppointment.appointment_id)" 
                                                    class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                                Отменить
                                            </button>
                                            <button @click="rescheduleAppointment(nearestAppointment.appointment_id)" 
                                                    class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                                                Перенести
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="bg-white dark:bg-zinc-900 rounded-lg p-6 text-center text-gray-500">
                                    У вас нет предстоящих записей
                                </div>
                            </div>

                            <!-- Популярные услуги -->
                            <div>
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ПОПУЛЯРНЫЕ УСЛУГИ</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div v-for="service in popularServices" :key="service.service_id" 
                                         class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                        <h3 class="font-semibold text-lg mb-2 text-black dark:text-white">{{ service.service_name }}</h3>
                                        <p class="text-sm text-gray-500 mb-2">{{ service.service_category }}</p>
                                        <p class="text-[#14b8a6] font-medium mb-3">{{ service.default_price }} ₽</p>
                                        <button @click="bookService(service.service_id)" 
                                                class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                                            Записаться
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Услуги -->
                        <div v-else-if="currentRoute === '/client/services'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">УСЛУГИ</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="service in services" :key="service.service_id" 
                                     class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition">
                                    <h3 class="font-semibold text-black dark:text-white">{{ service.service_name }}</h3>
                                    <p class="text-sm text-gray-500">{{ service.service_category }}</p>
                                    <p class="text-[#14b8a6] font-medium mt-2">{{ service.default_price }} ₽</p>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Мои записи -->
                        <div v-else-if="currentRoute === '/client/appointments'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МОИ ЗАПИСИ</h2>
                            
                            <div v-for="appointment in appointments" :key="appointment.appointment_id" 
                                 class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 mb-4 last:mb-0">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(appointment.date) }} в {{ formatTime(appointment.date) }}
                                        </span>
                                        <h3 class="font-semibold text-black dark:text-white">
                                            {{ getServiceNames(appointment) }}
                                        </h3>
                                        <p class="text-black dark:text-white/70">Врач: {{ appointment.employee?.employee_name || 'Не указан' }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs rounded-full" :class="getStatusClass(appointment.status)">
                                        {{ getStatusText(appointment.status) }}
                                    </span>
                                </div>
                                <div v-if="appointment.status === 0 || appointment.status === 1" class="flex gap-3 mt-4">
                                    <button @click="cancelAppointment(appointment.appointment_id)" 
                                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                        Отменить
                                    </button>
                                    <button @click="rescheduleAppointment(appointment.appointment_id)" 
                                            class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                                        Перенести
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="appointments.length === 0" class="text-center py-8 text-gray-500">
                                У вас нет записей
                            </div>
                        </div>

                        <!-- Страница История -->
                        <div v-else-if="currentRoute === '/client/history'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ИСТОРИЯ ПОСЕЩЕНИЙ</h2>
                            
                            <div v-for="appointment in appointmentHistory" :key="appointment.appointment_id" 
                                 class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 mb-4 last:mb-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-sm text-gray-500">{{ formatDate(appointment.date) }}</span>
                                        <h3 class="font-semibold text-black dark:text-white">{{ getServiceNames(appointment) }}</h3>
                                        <p class="text-sm text-gray-600">Врач: {{ appointment.employee?.employee_name || 'Не указан' }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(appointment.status)">
                                        {{ getStatusText(appointment.status) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div v-if="appointmentHistory.length === 0" class="text-center py-8 text-gray-500">
                                История посещений пуста
                            </div>
                        </div>

                        <!-- Страница Медкарта -->
                        <div v-else-if="currentRoute === '/client/medical-records'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МЕДИЦИНСКАЯ КАРТА</h2>
                            
                            <div v-for="record in medicalRecords" :key="record.record_id" 
                                 class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 mb-4 last:mb-0">
                                <p class="text-sm text-gray-500 mb-2">{{ formatDate(record.visit_date) }}</p>
                                <p class="text-sm text-gray-600 mb-1"><span class="font-medium">Врач:</span> {{ record.employee?.employee_name || 'Не указан' }}</p>
                                <p v-if="record.anamnesis" class="text-sm text-gray-600 mb-1"><span class="font-medium">Анамнез:</span> {{ record.anamnesis }}</p>
                                <p v-if="record.diagnosis" class="text-sm text-gray-600 mb-1"><span class="font-medium">Диагноз:</span> {{ record.diagnosis }}</p>
                                <p v-if="record.contraindications" class="text-sm text-gray-600"><span class="font-medium">Противопоказания:</span> {{ record.contraindications }}</p>
                            </div>
                            
                            <div v-if="medicalRecords.length === 0" class="text-center py-8 text-gray-500">
                                Медицинская карта пуста
                            </div>
                        </div>

                        <!-- Страница Профиль -->
                        <div v-else-if="currentRoute === '/client/profile'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ПРОФИЛЬ</h2>
                            
                            <div class="grid gap-6 max-w-md">
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Имя</label>
                                    <input type="text" v-model="profileForm.client_name" 
                                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Email</label>
                                    <input type="email" v-model="profileForm.email" 
                                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Телефон</label>
                                    <input type="tel" v-model="profileForm.phone" 
                                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Дата рождения</label>
                                    <input type="date" v-model="profileForm.birth_date" 
                                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <button @click="updateProfile" class="px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </main>
                </div>

                <!-- Подвал -->
                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    client: {
        type: Object,
        default: () => ({})
    },
    appointments: {
        type: Array,
        default: () => []
    },
    services: {
        type: Array,
        default: () => []
    },
    medicalRecords: {
        type: Array,
        default: () => []
    },
    feedback: {
        type: Array,
        default: () => []
    }
});

// Текущий маршрут
const currentRoute = computed(() => {
    return usePage().url;
});

// Данные клиента
const clientData = ref(props.client);

// Состояние меню
const showProfileMenu = ref(false);
const profileMenu = ref(null);

// Статистика
const stats = ref({
    totalVisits: 0,
    upcomingAppointments: 0,
    totalReviews: 0
});

// Ближайшая запись
const nearestAppointment = computed(() => {
    if (!props.appointments || props.appointments.length === 0) return null;
    
    const now = new Date();
    const futureAppointments = props.appointments
        .filter(a => a.status === 0 || a.status === 1)
        .filter(a => new Date(a.date) > now)
        .sort((a, b) => new Date(a.date) - new Date(b.date));
    
    return futureAppointments[0] || null;
});

// Популярные услуги
const popularServices = computed(() => {
    return props.services.slice(0, 3);
});

// История посещений (завершенные)
const appointmentHistory = computed(() => {
    return props.appointments.filter(a => a.status === 2);
});

// Форма профиля
const profileForm = ref({
    client_name: clientData.value?.client_name || '',
    email: clientData.value?.email || '',
    phone: clientData.value?.phone || '',
    birth_date: clientData.value?.birth_date || ''
});

// Методы
const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const logout = () => {
    router.post('/logout');
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const getServiceNames = (appointment) => {
    if (!appointment || !appointment.provided_services || appointment.provided_services.length === 0) {
        return 'Услуга не указана';
    }
    return appointment.provided_services.map(ps => ps.service?.service_name).join(', ');
};

const getStatusClass = (status) => {
    switch(status) {
        case 0: return 'bg-blue-100 text-blue-800';
        case 1: return 'bg-green-100 text-green-800';
        case 2: return 'bg-gray-100 text-gray-800';
        case 3: return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch(status) {
        case 0: return 'Запланирован';
        case 1: return 'Подтвержден';
        case 2: return 'Завершен';
        case 3: return 'Отменен';
        default: return 'Неизвестно';
    }
};

const cancelAppointment = async (id) => {
    if (!confirm('Вы уверены, что хотите отменить запись?')) return;
    
    try {
        await axios.put(`/api/client/appointments/${id}/cancel`);
        // Обновить данные
        router.reload();
    } catch (error) {
        console.error('Error canceling appointment:', error);
    }
};

const rescheduleAppointment = (id) => {
    router.get(`/client/services?reschedule=${id}`);
};

const bookService = (serviceId) => {
    router.get(`/client/services?service=${serviceId}`);
};

const updateProfile = async () => {
    try {
        await axios.put('/api/client/profile', profileForm.value);
        alert('Профиль обновлен');
        router.reload();
    } catch (error) {
        console.error('Error updating profile:', error);
    }
};

// Закрытие меню при клике вне его
const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    
    // Подсчет статистики
    stats.value.totalVisits = props.appointments?.length || 0;
    stats.value.upcomingAppointments = props.appointments?.filter(a => a.status === 0 || a.status === 1).length || 0;
    stats.value.totalReviews = props.feedback?.length || 0;
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>