<!-- resources/js/Pages/Director/Dashboard.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'ГЛАВНАЯ'"
    >
        <div class="space-y-6">
            <!-- Приветствие с датой -->
            <div class="bg-gradient-to-r from-[#8b5cf6] to-[#6d28d9] rounded-lg p-6 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">Добро пожаловать, {{ director?.employee_name }}!</h2>
                        <p class="text-purple-100">{{ currentDate }}</p>
                        <p class="text-purple-200 text-sm mt-1">Сегодня {{ dayOfWeek }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold">{{ currentTime }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Уведомления о новых поставках -->
            <div v-if="pendingOrders.length > 0" class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-300">Новые заказы на поставку</h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                            Требуется ваше подтверждение: {{ pendingOrders.length }} заказ(ов) ожидают рассмотрения
                        </p>
                        <Link href="/director/supply-control" 
                              class="mt-2 inline-block text-sm text-yellow-800 dark:text-yellow-300 hover:underline">
                            Перейти к поставкам →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Ключевые показатели -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Выручка (мес)</p>
                            <p class="text-2xl font-bold text-green-600">{{ formatPrice(monthlyRevenue) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ trendIcon(monthlyTrend) }} {{ monthlyTrend }}% к прошлому месяцу</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Количество записей</p>
                            <p class="text-2xl font-bold text-blue-600">{{ monthlyAppointments }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ trendIcon(appointmentsTrend) }} {{ appointmentsTrend }}% к прошлому месяцу</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Средний чек</p>
                            <p class="text-2xl font-bold text-purple-600">{{ formatPrice(averageCheck) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ trendIcon(checkTrend) }} {{ checkTrend }}% к прошлому месяцу</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Рейтинг салона</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ averageRating }}</p>
                            <p class="text-xs text-gray-400 mt-1">⭐ из 5 ({{ totalReviews }} отзывов)</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия и график -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Быстрые действия -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4">⚡ Быстрые действия</h3>
                    <div class="space-y-3">
                        <Link href="/director/services" 
                              class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition group">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center group-hover:bg-purple-200">
                                <span class="text-xl">💅</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">Управление услугами</p>
                                <p class="text-xs text-gray-500">Добавить или изменить услуги</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                        
                        <Link href="/director/staff" 
                              class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition group">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center group-hover:bg-blue-200">
                                <span class="text-xl">👥</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">Управление персоналом</p>
                                <p class="text-xs text-gray-500">Добавить сотрудников, настроить график</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                        
                        <Link href="/director/supply-control" 
                              class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition group">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center group-hover:bg-green-200">
                                <span class="text-xl">🚚</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">Контроль поставок</p>
                                <p class="text-xs text-gray-500">Подтверждение заказов, отслеживание</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </div>
                
                <!-- Мини-график выручки -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">📈 Динамика выручки</h3>
                        <Link href="/director/analytics" class="text-sm text-purple-600 hover:underline">
                            Подробнее →
                        </Link>
                    </div>
                    <div class="h-48">
                        <canvas ref="miniChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Последние отзывы -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">💬 Последние отзывы</h3>
                    <Link href="/director/analytics" class="text-sm text-purple-600 hover:underline">
                        Все отзывы →
                    </Link>
                </div>
                <div v-if="recentFeedbacks.length > 0" class="space-y-4">
                    <div v-for="feedback in recentFeedbacks" :key="feedback.id" 
                         class="p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ feedback.client_name }}</span>
                                    <div class="flex items-center">
                                        <span v-for="i in 5" :key="i" class="text-sm">
                                            {{ i <= feedback.score ? '⭐' : '☆' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ feedback.comment }}</p>
                                <p class="text-xs text-gray-400 mt-2">{{ feedback.formatted_date }}</p>
                            </div>
                            <div class="text-sm text-green-600">
                                {{ feedback.service_name }}
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    Пока нет отзывов
                </div>
            </div>
        </div>
    </DirectorLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    director: Object,
    pendingOrders: Array
});

// Время и дата
const currentDate = ref('');
const currentTime = ref('');
const dayOfWeek = ref('');
let timer = null;

// Статистика
const monthlyRevenue = ref(0);
const monthlyAppointments = ref(0);
const averageCheck = ref(0);
const averageRating = ref(0);
const totalReviews = ref(0);
const monthlyTrend = ref(0);
const appointmentsTrend = ref(0);
const checkTrend = ref(0);
const recentFeedbacks = ref([]);
const financialData = ref([]);

let miniChart = null;
const miniChartCanvas = ref(null);

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const trendIcon = (trend) => {
    if (trend > 0) return '📈';
    if (trend < 0) return '📉';
    return '➡️';
};

const updateDateTime = () => {
    const now = new Date();
    currentDate.value = now.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
    currentTime.value = now.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit'
    });
    dayOfWeek.value = now.toLocaleDateString('ru-RU', { weekday: 'long' });
};

const loadDashboardData = async () => {
    try {
        const response = await axios.get('/api/director/dashboard/stats');
        monthlyRevenue.value = response.data.monthly_revenue;
        monthlyAppointments.value = response.data.monthly_appointments;
        averageCheck.value = response.data.average_check;
        averageRating.value = response.data.average_rating;
        totalReviews.value = response.data.total_reviews;
        monthlyTrend.value = response.data.monthly_trend;
        appointmentsTrend.value = response.data.appointments_trend;
        checkTrend.value = response.data.check_trend;
        recentFeedbacks.value = response.data.recent_feedbacks;
        financialData.value = response.data.financial_data;
        
        // Обновляем мини-график
        updateMiniChart();
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
};

const updateMiniChart = () => {
    if (!miniChartCanvas.value || financialData.value.length === 0) return;
    
    const ctx = miniChartCanvas.value.getContext('2d');
    
    if (miniChart) {
        miniChart.destroy();
    }
    
    const last6Months = financialData.value.slice(-6);
    const labels = last6Months.map(d => d.month_name);
    const data = last6Months.map(d => d.income);
    
    miniChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Выручка',
                data: data,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => formatPrice(context.raw)
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: (value) => formatPrice(value)
                    }
                }
            }
        }
    });
};

onMounted(() => {
    updateDateTime();
    timer = setInterval(updateDateTime, 1000);
    loadDashboardData();
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (miniChart) miniChart.destroy();
});
</script>