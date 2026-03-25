<!-- resources/js/Pages/Director/Analytics.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'АНАЛИТИКА'"
    >
        <div class="space-y-6">
            <!-- Фильтры -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium mb-1">Период</label>
                        <select v-model="filters.period" 
                                class="px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                            <option value="all">Все время</option>
                            <option value="year">Год</option>
                            <option value="month">Месяц</option>
                        </select>
                    </div>
                    
                    <div v-if="filters.period === 'year' || filters.period === 'month'">
                        <label class="block text-sm font-medium mb-1">Год</label>
                        <select v-model="filters.year" 
                                class="px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                            <option v-for="year in availableYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>
                    
                    <div v-if="filters.period === 'month'">
                        <label class="block text-sm font-medium mb-1">Месяц</label>
                        <select v-model="filters.month" 
                                class="px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                            <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                {{ month.name }}
                            </option>
                        </select>
                    </div>
                    
                    <button @click="loadAnalytics" 
                            class="px-6 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 transition">
                        Применить
                    </button>
                </div>
            </div>

            <!-- Карточки KPI -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <p class="text-sm text-gray-500">Средний чек</p>
                    <p class="text-2xl font-bold text-[#8b5cf6]">{{ formatPrice(averageCheck) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ appointmentsCount }} записей</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <p class="text-sm text-gray-500">Общий доход</p>
                    <p class="text-2xl font-bold text-green-600">{{ formatPrice(totalIncome) }}</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <p class="text-sm text-gray-500">Общий расход</p>
                    <p class="text-2xl font-bold text-red-600">{{ formatPrice(totalExpense) }}</p>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <p class="text-sm text-gray-500">Прибыль</p>
                    <p class="text-2xl font-bold" :class="totalProfit >= 0 ? 'text-blue-600' : 'text-red-600'">
                        {{ formatPrice(totalProfit) }}
                    </p>
                </div>
            </div>

            <!-- Рейтинг салона -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Рейтинг -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4">Рейтинг салона</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-5xl font-bold text-yellow-500">{{ averageRating }}</p>
                            <p class="text-sm text-gray-500">из 5</p>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-1 mb-2">
                                <span v-for="i in 5" :key="i" class="text-2xl">
                                    {{ i <= Math.round(averageRating) ? '⭐' : '☆' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ totalReviews }} отзывов</p>
                        </div>
                    </div>
                    
                    <!-- Распределение оценок -->
                    <div class="space-y-2">
                        <div v-for="rating in [5,4,3,2,1]" :key="rating" class="flex items-center gap-2">
                            <span class="w-12 text-sm">{{ rating }} ★</span>
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400 rounded-full" 
                                     :style="{ width: getRatingPercent(rating) + '%' }"></div>
                            </div>
                            <span class="w-12 text-sm text-right">{{ ratingDistribution[rating] || 0 }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Самая популярная услуга -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                    <h3 class="text-lg font-semibold mb-4">🏆 Самая популярная услуга</h3>
                    <div class="text-center py-4">
                        <p class="text-2xl font-bold text-[#8b5cf6]">{{ mostPopularService.name }}</p>
                        <p class="text-gray-500 mt-2">Выполнено {{ mostPopularService.count }} раз</p>
                    </div>
                </div>
            </div>

            <!-- График доходов и расходов -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-lg font-semibold mb-4">Динамика доходов и расходов</h3>
                <div class="h-80">
                    <canvas ref="chartCanvas"></canvas>
                </div>
            </div>

            <!-- Эффективность сотрудников -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-lg font-semibold mb-4">👥 Эффективность сотрудников</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left py-3">Сотрудник</th>
                                <th class="text-right py-3">Выручка</th>
                                <th class="text-right py-3">Кол-во приемов</th>
                                <th class="text-right py-3">Кол-во услуг</th>
                                <th class="text-right py-3">Средний чек</th>
                             </tr>
                        </thead>
                        <tbody>
                            <tr v-for="employee in employeeEfficiency" :key="employee.employee_id" 
                                class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                <td class="py-3 font-medium">{{ employee.employee_name }}</td>
                                <td class="py-3 text-right text-green-600">{{ formatPrice(employee.total_revenue) }}</td>
                                <td class="py-3 text-right">{{ employee.appointments_count }}</td>
                                <td class="py-3 text-right">{{ employee.services_count }}</td>
                                <td class="py-3 text-right">{{ formatPrice(employee.total_revenue / (employee.appointments_count || 1)) }}</td>
                             </tr>
                        </tbody>
                        <tfoot v-if="employeeEfficiency.length === 0">
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    Нет данных за выбранный период
                                </td>
                             </tr>
                        </tfoot>
                     </table>
                </div>
            </div>
        </div>

        <!-- Уведомления -->
        <Teleport to="body">
            <div v-if="notification.show" 
                 class="fixed bottom-4 right-4 z-50 animate-slide-up"
                 :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                <div class="px-4 py-3 rounded-lg shadow-lg text-white">
                    {{ notification.message }}
                </div>
            </div>
        </Teleport>
    </DirectorLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    director: Object
});

// Состояния
const loading = ref(false);
const filters = ref({
    period: 'all',
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1
});

// Данные
const availableYears = ref([]);
const availableMonths = ref([]);
const averageCheck = ref(0);
const appointmentsCount = ref(0);
const totalIncome = ref(0);
const totalExpense = ref(0);
const totalProfit = ref(0);
const mostPopularService = ref({ name: '—', count: 0 });
const employeeEfficiency = ref([]);
const financialData = ref([]);
const averageRating = ref(0);
const totalReviews = ref(0);
const ratingDistribution = ref({ 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 });

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

let chart = null;
const chartCanvas = ref(null);

const showNotification = (type, message) => {
    notification.value = { show: true, type, message };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const getRatingPercent = (rating) => {
    const total = Object.values(ratingDistribution.value).reduce((a, b) => a + b, 0);
    if (total === 0) return 0;
    return (ratingDistribution.value[rating] / total) * 100;
};

const loadFilters = async () => {
    try {
        const response = await axios.get('/api/director/analytics/filters');
        availableYears.value = response.data.years;
        availableMonths.value = response.data.months;
    } catch (error) {
        console.error('Error loading filters:', error);
    }
};

const loadAnalytics = async () => {
    loading.value = true;
    try {
        const params = {
            period: filters.value.period,
            year: filters.value.year,
        };
        if (filters.value.period === 'month') {
            params.month = filters.value.month;
        }
        
        const response = await axios.get('/api/director/analytics/data', { params });
        
        averageCheck.value = response.data.average_check;
        appointmentsCount.value = response.data.appointments_count || 0;
        totalIncome.value = response.data.total_income;
        totalExpense.value = response.data.total_expense;
        totalProfit.value = response.data.total_profit;
        mostPopularService.value = response.data.most_popular_service;
        employeeEfficiency.value = response.data.employee_efficiency;
        financialData.value = response.data.financial_data;
        averageRating.value = response.data.average_rating;
        totalReviews.value = response.data.total_reviews;
        ratingDistribution.value = response.data.rating_distribution;
        
        // Обновляем график
        updateChart();
        
    } catch (error) {
        console.error('Error loading analytics:', error);
        showNotification('error', 'Ошибка загрузки данных');
    } finally {
        loading.value = false;
    }
};

const updateChart = () => {
    if (!chartCanvas.value) return;
    
    const ctx = chartCanvas.value.getContext('2d');
    
    if (chart) {
        chart.destroy();
    }
    
    const labels = financialData.value.map(d => d.month_name);
    const incomeData = financialData.value.map(d => d.income);
    const expenseData = financialData.value.map(d => d.expense);
    
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Доходы',
                    data: incomeData,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Расходы',
                    data: expenseData,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + formatPrice(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return formatPrice(value);
                        }
                    }
                }
            }
        }
    });
};

// Следим за изменением периода
watch(() => filters.value.period, () => {
    loadAnalytics();
});

onMounted(() => {
    loadFilters();
    loadAnalytics();
});
</script>

<style scoped>
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>