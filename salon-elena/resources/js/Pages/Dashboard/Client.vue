<!-- ClientDashboard.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <!-- Background изображение (как в Welcome.vue) -->
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <!-- Шапка с сайдбаром и основным контентом -->
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар (Левое меню) -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <!-- Логотип в сайдбаре -->
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#14b8a6] lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M61.8548 14.6253..."/> <!-- Ваш логотип -->
                                </svg>
                            </div>
                            
                            <!-- Навигация сайдбара -->
                            <nav class="space-y-1">
                                <Link 
                                    href="/client" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
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
                                    <span>Услуги (Запись)</span>
                                </Link>
                                
                                <Link 
                                    href="/client/appointments" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/appointments' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">📅</span>
                                    <span>Моя запись</span>
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
                                    href="/client/profile" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/client/profile' ? 'bg-[#14b8a6] text-white' : 'text-black dark:text-white/70 hover:bg-[#14b8a6]/10']"
                                >
                                    <span class="text-xl">👤</span>
                                    <span>Профиль</span>
                                </Link>
                            </nav>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <!-- Шапка -->
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <!-- Приветствие -->
                                <h1 class="text-3xl font-semibold text-black dark:text-white">
                                    Здравствуйте, Анна!
                                </h1>
                                
                                <!-- Правая часть шапки -->
                                <div class="flex items-center gap-4">
                                    <!-- Поиск (опционально) -->
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            placeholder="Поиск..." 
                                            class="pl-10 pr-4 py-2 bg-white dark:bg-zinc-900 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 focus:outline-none focus:ring-2 focus:ring-[#14b8a6]"
                                        />
                                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Профиль с выпадающим меню -->
                                    <div class="relative" ref="profileMenu">
                                        <button @click="toggleProfileMenu" class="flex items-center gap-3 cursor-pointer">
                                            <span class="text-black dark:text-white font-medium">Анна Иванова</span>
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

                        <!-- Контент в зависимости от текущего маршрута -->
                        <div v-if="currentRoute === '/client'">
                            <!-- Баннер/Слайдер -->
                            <div class="mb-8">
                                <div class="bg-gradient-to-r from-[#14b8a6] to-[#0d9488] rounded-lg p-8 text-white">
                                    <h2 class="text-2xl font-bold mb-2">Специальное предложение!</h2>
                                    <p class="mb-4">Скидка 20% на комплексный уход за лицом</p>
                                    <button class="px-6 py-2 bg-white text-[#14b8a6] rounded-md hover:bg-gray-100 transition font-medium">Записаться</button>
                                </div>
                            </div>

                            <!-- Ближайшая запись -->
                            <div class="mb-8">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">БЛИЖАЙШАЯ ЗАПИСЬ</h2>
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="grid gap-4">
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Дата:</span>
                                            <span>15 марта 2026, 15:00</span>
                                        </div>
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Услуга:</span>
                                            <span>Чистка лица</span>
                                        </div>
                                        <div class="flex items-center gap-8 text-black dark:text-white/70">
                                            <span class="font-medium min-w-24">Врач:</span>
                                            <span>Иванова М.С.</span>
                                        </div>
                                        <div class="flex gap-4 mt-4">
                                            <button class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                                                Отменить
                                            </button>
                                            <button class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                                                Перенести
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Лента уведомлений -->
                            <div class="mb-8">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">УВЕДОМЛЕНИЯ</h2>
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3 text-black dark:text-white/70">
                                            <span class="w-2 h-2 bg-[#14b8a6] rounded-full"></span>
                                            <span>Напоминание: завтра запись к Смирновой в 15:00</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-black dark:text-white/70">
                                            <span class="w-2 h-2 bg-[#14b8a6] rounded-full"></span>
                                            <span>Спасибо за визит! Оставьте отзыв</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Популярные услуги -->
                            <div>
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ПОПУЛЯРНЫЕ УСЛУГИ</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                        <h3 class="font-semibold text-lg mb-2 text-black dark:text-white">Чистка лица</h3>
                                        <p class="text-[#14b8a6] font-medium mb-3">2500 ₽</p>
                                        <button class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">Записаться</button>
                                    </div>
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                        <h3 class="font-semibold text-lg mb-2 text-black dark:text-white">Пилинг</h3>
                                        <p class="text-[#14b8a6] font-medium mb-3">1800 ₽</p>
                                        <button class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">Записаться</button>
                                    </div>
                                    <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                        <h3 class="font-semibold text-lg mb-2 text-black dark:text-white">Массаж лица</h3>
                                        <p class="text-[#14b8a6] font-medium mb-3">2000 ₽</p>
                                        <button class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">Записаться</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Услуги (Запись) -->
                        <div v-else-if="currentRoute === '/client/services'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ЗАПИСЬ НА УСЛУГУ</h2>
                            
                            <!-- Фильтры -->
                            <div class="flex gap-4 mb-6">
                                <select class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                                    <option>Все услуги</option>
                                    <option>Чистка лица</option>
                                    <option>Пилинг</option>
                                </select>
                                <select class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                                    <option>Все врачи</option>
                                    <option>Иванова М.С.</option>
                                    <option>Петрова А.В.</option>
                                </select>
                            </div>

                            <!-- Календарь -->
                            <div class="mb-6">
                                <div class="grid grid-cols-7 gap-2">
                                    <div v-for="day in ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']" 
                                         :key="day" 
                                         class="text-center font-medium text-black dark:text-white/70 py-2">
                                        {{ day }}
                                    </div>
                                    <div v-for="day in 35" :key="day" 
                                         class="aspect-square border border-gray-200 dark:border-zinc-700 rounded-lg p-2 hover:border-[#14b8a6] cursor-pointer transition">
                                        <span class="text-black dark:text-white">{{ day }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Сетка времени -->
                            <div>
                                <h3 class="font-semibold text-black dark:text-white mb-3">Доступное время</h3>
                                <div class="grid grid-cols-4 gap-3">
                                    <div class="px-4 py-2 border border-gray-200 dark:border-zinc-700 rounded-md text-center cursor-pointer hover:bg-[#14b8a6] hover:text-white transition">09:00</div>
                                    <div class="px-4 py-2 border border-gray-200 dark:border-zinc-700 rounded-md text-center cursor-pointer hover:bg-[#14b8a6] hover:text-white transition">10:00</div>
                                    <div class="px-4 py-2 border border-gray-200 dark:border-zinc-700 rounded-md text-center cursor-not-allowed bg-gray-100 dark:bg-zinc-800 text-gray-400">11:00</div>
                                    <div class="px-4 py-2 border border-gray-200 dark:border-zinc-700 rounded-md text-center cursor-pointer hover:bg-[#14b8a6] hover:text-white transition">12:00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Моя запись -->
                        <div v-else-if="currentRoute === '/client/appointments'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МОЯ ЗАПИСЬ</h2>
                            
                            <div class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">15 марта 2026, 15:00</span>
                                        <h3 class="font-semibold text-black dark:text-white">Чистка лица</h3>
                                        <p class="text-black dark:text-white/70">Врач: Иванова М.С.</p>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Подтверждено</span>
                                </div>
                                <div class="flex gap-3 mt-4">
                                    <button class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">Отменить</button>
                                    <button class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">Перенести</button>
                                </div>
                            </div>
                        </div>

                        <!-- Страница История -->
                        <div v-else-if="currentRoute === '/client/history'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ИСТОРИЯ ПОСЕЩЕНИЙ</h2>
                            
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Врач</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Стоимость</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(visit, index) in historyVisits" :key="index" class="border-b border-gray-200 dark:border-zinc-700">
                                        <td class="py-3 text-black dark:text-white/70">{{ visit.date }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ visit.service }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ visit.doctor }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ visit.price }}</td>
                                        <td class="py-3">
                                            <button v-if="visit.canReview" 
                                                    class="px-3 py-1 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition text-sm">
                                                Оставить отзыв
                                            </button>
                                            <span v-else class="text-gray-400 text-sm">Отзыв оставлен</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Страница Профиль -->
                        <div v-else-if="currentRoute === '/client/profile'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ПРОФИЛЬ</h2>
                            
                            <div class="grid gap-6 max-w-md">
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Имя</label>
                                    <input type="text" value="Анна Иванова" class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Email</label>
                                    <input type="email" value="anna@example.com" class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-1">Телефон</label>
                                    <input type="tel" value="+7 (999) 123-45-67" class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                                </div>
                                <button class="px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">Сохранить</button>
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
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

// Текущий маршрут
const currentRoute = computed(() => {
    return usePage().url;
});

// Состояние выпадающего меню профиля
const showProfileMenu = ref(false);
const profileMenu = ref(null);

// История посещений для страницы
const historyVisits = ref([
    { date: '10.03.2026', service: 'Чистка лица', doctor: 'Иванова М.С.', price: '2500 ₽', canReview: true },
    { date: '25.02.2026', service: 'Пилинг', doctor: 'Петрова А.В.', price: '1800 ₽', canReview: false },
    { date: '15.02.2026', service: 'Массаж лица', doctor: 'Смирнова Е.Н.', price: '2000 ₽', canReview: true },
]);

// Методы
const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const logout = () => {
    // Здесь будет логика выхода
    console.log('Выход из системы');
};

// Закрытие меню при клике вне его
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

<style scoped>
/* Дополнительные стили при необходимости */
</style>