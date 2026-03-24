<!-- resources/js/Pages/Accountant/Dashboard.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ГЛАВНАЯ'"
        :unpaidCount="stats.unpaid_count"
        :criticalCount="stats.critical_count"
        :todayRevenue="stats.today_revenue"
        :pendingPayments="stats.pending_payments"
    >
        <!-- Карточки финансовой статистики -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Общий доход компании</p>
                        <p class="text-2xl font-semibold text-green-600">{{ formatPrice(financialStats.total_income) }}</p>
                        <p class="text-xs text-gray-400 mt-1">За последние 30 дней: {{ formatPrice(financialStats.recent_income) }}</p>
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
                        <p class="text-sm text-gray-500">Общий расход компании</p>
                        <p class="text-2xl font-semibold text-red-600">{{ formatPrice(financialStats.total_expense) }}</p>
                        <p class="text-xs text-gray-400 mt-1">За последние 30 дней: {{ formatPrice(financialStats.recent_expense) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Прибыль компании</p>
                        <p class="text-2xl font-semibold" :class="financialStats.profit >= 0 ? 'text-blue-600' : 'text-red-600'">
                            {{ formatPrice(financialStats.profit) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Доход - Расход</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительные карточки статистики -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Неоплаченных услуг</p>
                        <p class="text-2xl font-semibold text-red-600">{{ stats.unpaid_count }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Критические материалы</p>
                        <p class="text-2xl font-semibold text-yellow-600">{{ stats.critical_count }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Выручка сегодня</p>
                        <p class="text-2xl font-semibold text-green-600">{{ formatPrice(stats.today_revenue) }}</p>
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
                        <p class="text-sm text-gray-500">Ожидает оплаты</p>
                        <p class="text-2xl font-semibold text-blue-600">{{ formatPrice(stats.pending_payments) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-4">
                <Link href="/accountant/incomes" class="flex items-center gap-2 px-6 py-3 bg-[#3b82f6] text-white rounded-lg hover:bg-[#3b82f6]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Доходы
                </Link>
                <Link href="/accountant/expenses" class="flex items-center gap-2 px-6 py-3 bg-[#ef4444] text-white rounded-lg hover:bg-[#ef4444]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                    Расходы
                </Link>
                <Link href="/accountant/warehouse" class="flex items-center gap-2 px-6 py-3 bg-[#22c55e] text-white rounded-lg hover:bg-[#22c55e]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-4.5M20 7v4.5M20 7l-6 6m-2-2l-4 4" />
                    </svg>
                    Заказать материалы
                </Link>
                <Link href="/accountant/salary" class="flex items-center gap-2 px-6 py-3 bg-[#f59e0b] text-white rounded-lg hover:bg-[#f59e0b]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Рассчитать ЗП
                </Link>
            </div>
        </div>

        <!-- Блок последних операций с фильтрацией -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-black dark:text-white">ПОСЛЕДНИЕ ОПЕРАЦИИ</h2>
                <div class="flex gap-2">
                    <button 
                        @click="activeFilter = 'all'"
                        class="px-4 py-2 rounded-md transition text-sm"
                        :class="activeFilter === 'all' 
                            ? 'bg-[#3b82f6] text-white' 
                            : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300'">
                        Все
                    </button>
                    <button 
                        @click="activeFilter = 'income'"
                        class="px-4 py-2 rounded-md transition text-sm"
                        :class="activeFilter === 'income' 
                            ? 'bg-[#3b82f6] text-white' 
                            : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300'">
                        Доходы
                    </button>
                    <button 
                        @click="activeFilter = 'expense'"
                        class="px-4 py-2 rounded-md transition text-sm"
                        :class="activeFilter === 'expense' 
                            ? 'bg-[#3b82f6] text-white' 
                            : 'bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300'">
                        Расходы
                    </button>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3b82f6]"></div>
                    <p class="mt-2 text-gray-500">Загрузка...</p>
                </div>
                
                <div v-else-if="filteredOperations.length === 0" class="text-center py-8 text-gray-500">
                    Нет операций за выбранный период
                </div>
                
                <div v-else class="space-y-4">
                    <div v-for="operation in filteredOperations" :key="operation.id" 
                         class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-zinc-700 last:border-0 hover:bg-gray-50 dark:hover:bg-zinc-800 transition rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                 :class="operation.type === 'income' ? 'bg-green-100' : 'bg-red-100'">
                                <span class="text-xl">{{ operation.type_icon }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-black dark:text-white">{{ operation.description }}</p>
                                <p class="text-xs text-gray-400">{{ operation.formatted_date }}</p>
                                <p v-if="operation.reference_number" class="text-xs text-gray-500 mt-1">
                                    {{ operation.type === 'income' ? 'Чек: ' : '№: ' }}{{ operation.reference_number }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold" :class="operation.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                {{ operation.type === 'income' ? '+' : '-' }} {{ formatPrice(operation.amount) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">{{ operation.type_text }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Критические материалы -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-black dark:text-white">КРИТИЧЕСКИЕ МАТЕРИАЛЫ</h2>
                <Link href="/accountant/warehouse" class="text-[#3b82f6] hover:underline">
                    Весь склад →
                </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="material in criticalMaterials" :key="material.id" 
                     class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow border-l-4 border-red-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-black dark:text-white">{{ material.name }}</h3>
                            <p class="text-sm text-gray-500">Остаток: {{ material.current_balance }} {{ material.unit }}</p>
                            <p class="text-xs text-gray-400">Мин. остаток: {{ material.min_stock }}</p>
                        </div>
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Критично</span>
                    </div>
                </div>
                <div v-if="criticalMaterials.length === 0" class="col-span-3 text-center py-8 text-gray-500">
                    Все материалы в достаточном количестве
                </div>
            </div>
        </div>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    stats: Object,
    financialStats: Object,
    criticalMaterials: Array
});

const operations = ref([]);
const loading = ref(false);
const activeFilter = ref('all');

const filteredOperations = computed(() => {
    if (activeFilter.value === 'all') return operations.value;
    return operations.value.filter(op => op.type === activeFilter.value);
});

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const loadOperations = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/operations', {
            params: { type: activeFilter.value, limit: 20 }
        });
        operations.value = response.data;
    } catch (error) {
        console.error('Error loading operations:', error);
    } finally {
        loading.value = false;
    }
};

// Следим за изменением фильтра
watch(activeFilter, () => {
    loadOperations();
});

onMounted(() => {
    loadOperations();
});
</script>