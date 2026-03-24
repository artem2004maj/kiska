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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
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
            
            <!-- НОВАЯ КАРТОЧКА - НАЛОГИ -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Налоги (6%)</p>
                        <p class="text-2xl font-semibold text-purple-600">{{ formatPrice(currentYearTax) }}</p>
                        <p class="text-xs text-gray-400 mt-1">За {{ currentYear }} год</p>
                    </div>
                    <button @click="openTaxModal" class="p-3 bg-purple-100 rounded-full hover:bg-purple-200 transition">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                </div>
                <button @click="openTaxModal" class="mt-3 text-sm text-purple-600 hover:underline">
                    Детали →
                </button>
            </div>
        </div>

        <!-- Остальные карточки статистики -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
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

        <!-- Модальное окно налогового учета -->
        <Teleport to="body">
            <div v-if="showTaxModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showTaxModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-black dark:text-white">
                                Налоговый учет ({{ taxRate }}% от дохода)
                            </h3>
                            <button @click="showTaxModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6">
                            <!-- Выбор года - выпадающий список -->
                            <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Выберите год:</label>
                                    <select 
                                        v-model="selectedTaxYear" 
                                        class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        @change="loadYearTaxData">
                                        <option v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }} год
                                        </option>
                                    </select>
                                </div>
                                <div class="text-sm text-gray-500 bg-gray-100 dark:bg-zinc-800 px-4 py-2 rounded-lg">
                                    <span class="font-medium">Итого за {{ selectedTaxYear }} год:</span>
                                    <span class="ml-2 text-green-600">Доход: {{ formatPrice(selectedYearData?.total_income || 0) }}</span>
                                    <span class="ml-3 text-purple-600">Налог: {{ formatPrice(selectedYearData?.total_tax || 0) }}</span>
                                    <span class="ml-3 text-blue-600">Прибыль: {{ formatPrice(selectedYearData?.total_profit || 0) }}</span>
                                </div>
                            </div>

                            <!-- Загрузка данных -->
                            <div v-if="loadingTax" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                                <p class="mt-2 text-gray-500">Загрузка данных...</p>
                            </div>

                            <!-- Таблица налогов по кварталам для выбранного года -->
                            <div v-else-if="selectedYearData" class="border rounded-lg overflow-hidden">
                                <div class="bg-gray-100 dark:bg-zinc-800 px-4 py-3">
                                    <h4 class="font-semibold text-lg">{{ selectedYearData.year }} год - поквартальная разбивка</h4>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                            <tr>
                                                <th class="px-4 py-3 text-left">Квартал</th>
                                                <th class="px-4 py-3 text-left">Период</th>
                                                <th class="px-4 py-3 text-right">Доход</th>
                                                <th class="px-4 py-3 text-right">Налог ({{ taxRate }}%)</th>
                                                <th class="px-4 py-3 text-right">Прибыль после налога</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="quarter in selectedYearData.quarters" :key="quarter.quarter" 
                                                class="border-t border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800">
                                                <td class="px-4 py-3 font-medium">
                                                    {{ quarter.quarter }} квартал
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    {{ quarter.start_date }} - {{ quarter.end_date }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-medium text-green-600">
                                                    {{ formatPrice(quarter.income) }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-purple-600">
                                                    {{ formatPrice(quarter.tax) }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-blue-600">
                                                    {{ formatPrice(quarter.profit) }}
                                                </td>
                                            </tr>
                                            <tr class="border-t border-gray-300 dark:border-zinc-600 bg-gray-50 dark:bg-zinc-800 font-bold">
                                                <td colspan="2" class="px-4 py-3">Итого за год</td>
                                                <td class="px-4 py-3 text-right text-green-600">{{ formatPrice(selectedYearData.total_income) }}</td>
                                                <td class="px-4 py-3 text-right text-purple-600">{{ formatPrice(selectedYearData.total_tax) }}</td>
                                                <td class="px-4 py-3 text-right text-blue-600">{{ formatPrice(selectedYearData.total_profit) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div v-else-if="!loadingTax" class="text-center py-8 text-gray-500">
                                Нет данных по налогам за {{ selectedTaxYear }} год
                            </div>
                            
                            <!-- Дополнительная информация -->
                            <div class="mt-6 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-purple-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="text-sm text-purple-700 dark:text-purple-300">
                                        <p class="font-medium mb-1">Информация для налоговой отчетности:</p>
                                        <ul class="list-disc list-inside space-y-1">
                                            <li>Налоговая база: {{ taxRate }}% от полученного дохода</li>
                                            <li>Отчетный период: квартал (до 25 числа месяца, следующего за кварталом)</li>
                                            <li>Годовая декларация подается до 30 апреля следующего года</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-end gap-3">
                            <button @click="exportTaxToExcel" 
                                    :disabled="!selectedYearData || selectedYearData.total_income === 0"
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                📊 Экспорт в Excel
                            </button>
                            <button @click="showTaxModal = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    accountant: Object,
    stats: Object,
    financialStats: Object,
    criticalMaterials: Array
});

const operations = ref([]);
const loading = ref(false);
const activeFilter = ref('all');
const currentYear = ref(new Date().getFullYear());
const currentYearTax = ref(0);
const taxRate = ref(6);
const showTaxModal = ref(false);
const taxSummary = ref([]);
const selectedTaxYear = ref(new Date().getFullYear());
const loadingTax = ref(false);

// Генерируем список доступных лет (от 2023 до текущий год + 5 лет вперед)
const availableYears = computed(() => {
    const currentYearValue = new Date().getFullYear();
    const years = [];
    for (let year = 2023; year <= currentYearValue + 5; year++) {
        years.push(year);
    }
    return years;
});

const filteredOperations = computed(() => {
    if (activeFilter.value === 'all') return operations.value;
    return operations.value.filter(op => op.type === activeFilter.value);
});

// Получить данные для выбранного года
const selectedYearData = computed(() => {
    return taxSummary.value.find(item => item.year === selectedTaxYear.value);
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

const loadCurrentYearTax = async () => {
    try {
        const response = await axios.get('/api/accountant/tax/data', {
            params: { period: 'year', year: currentYear.value }
        });
        currentYearTax.value = response.data.tax || 0;
    } catch (error) {
        console.error('Error loading tax data:', error);
    }
};

// Загрузка налоговых данных для конкретного года
const loadYearTaxData = async () => {
    if (!selectedTaxYear.value) return;
    
    loadingTax.value = true;
    try {
        // Проверяем, есть ли уже данные для этого года в кэше
        const existingData = taxSummary.value.find(item => item.year === selectedTaxYear.value);
        
        if (!existingData) {
            // Если данных нет, загружаем их
            const response = await axios.get('/api/accountant/tax/data', {
                params: { period: 'year', year: selectedTaxYear.value }
            });
            
            // Добавляем данные в общую сводку
            const newYearData = {
                year: selectedTaxYear.value,
                quarters: [],
                total_income: response.data.income,
                total_tax: response.data.tax,
                total_profit: response.data.profit_after_tax
            };
            
            // Создаем квартальные данные
            for (let quarter = 1; quarter <= 4; quarter++) {
                const quarterResponse = await axios.get('/api/accountant/tax/data', {
                    params: { period: 'quarter', year: selectedTaxYear.value, quarter: quarter }
                });
                
                newYearData.quarters.push({
                    quarter: quarter,
                    period: `${quarter} кв.`,
                    start_date: quarterResponse.data.start_date,
                    end_date: quarterResponse.data.end_date,
                    income: quarterResponse.data.income,
                    tax: quarterResponse.data.tax,
                    profit: quarterResponse.data.profit_after_tax
                });
            }
            
            taxSummary.value.push(newYearData);
            // Сортируем по году
            taxSummary.value.sort((a, b) => b.year - a.year);
        }
    } catch (error) {
        console.error('Error loading year tax data:', error);
    } finally {
        loadingTax.value = false;
    }
};

// Загрузка полной налоговой сводки для всех годов
const loadTaxSummary = async () => {
    loadingTax.value = true;
    try {
        // Очищаем старые данные
        taxSummary.value = [];
        
        // Загружаем данные для всех доступных годов
        for (const year of availableYears.value) {
            const response = await axios.get('/api/accountant/tax/data', {
                params: { period: 'year', year: year }
            });
            
            const yearData = {
                year: year,
                quarters: [],
                total_income: response.data.income,
                total_tax: response.data.tax,
                total_profit: response.data.profit_after_tax
            };
            
            // Загружаем квартальные данные
            for (let quarter = 1; quarter <= 4; quarter++) {
                const quarterResponse = await axios.get('/api/accountant/tax/data', {
                    params: { period: 'quarter', year: year, quarter: quarter }
                });
                
                yearData.quarters.push({
                    quarter: quarter,
                    period: `${quarter} кв.`,
                    start_date: quarterResponse.data.start_date,
                    end_date: quarterResponse.data.end_date,
                    income: quarterResponse.data.income,
                    tax: quarterResponse.data.tax,
                    profit: quarterResponse.data.profit_after_tax
                });
            }
            
            taxSummary.value.push(yearData);
        }
        
        // Сортируем по году (от новых к старым)
        taxSummary.value.sort((a, b) => b.year - a.year);
        
    } catch (error) {
        console.error('Error loading tax summary:', error);
    } finally {
        loadingTax.value = false;
    }
};

const openTaxModal = async () => {
    // Устанавливаем текущий год
    selectedTaxYear.value = new Date().getFullYear();
    showTaxModal.value = true;
    await loadTaxSummary();
};

// Экспорт налоговых данных в Excel
const exportTaxToExcel = () => {
    if (!selectedYearData.value) return;
    
    const data = selectedYearData.value;
    const excelData = [
        ['ELENA Beauty Clinic'],
        ['Налоговый учет'],
        [`Налоговая ставка: ${taxRate.value}% от дохода`],
        [],
        [`ОТЧЕТ ЗА ${data.year} ГОД`],
        [],
        ['Период', 'Даты', 'Доход (₽)', `Налог ${taxRate.value}% (₽)`, 'Прибыль после налога (₽)']
    ];
    
    // Добавляем данные по кварталам
    data.quarters.forEach(quarter => {
        excelData.push([
            `${quarter.quarter} квартал`,
            `${quarter.start_date} - ${quarter.end_date}`,
            quarter.income,
            quarter.tax,
            quarter.profit
        ]);
    });
    
    // Добавляем итоговую строку
    excelData.push([
        'ИТОГО ЗА ГОД',
        '',
        data.total_income,
        data.total_tax,
        data.total_profit
    ]);
    
    excelData.push([]);
    excelData.push(['Информация для налоговой отчетности:']);
    excelData.push(['Налоговая база:', `${taxRate.value}% от полученного дохода`]);
    excelData.push(['Отчетный период:', 'квартал (до 25 числа месяца, следующего за кварталом)']);
    excelData.push(['Годовая декларация:', 'подается до 30 апреля следующего года']);
    excelData.push([]);
    excelData.push(['Дата формирования:', new Date().toLocaleDateString('ru-RU')]);
    excelData.push(['Бухгалтер:', props.accountant?.employee_name || '']);
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    
    // Настройка ширины колонок
    ws['!cols'] = [
        { wch: 12 },  // Период
        { wch: 25 },  // Даты
        { wch: 15 },  // Доход
        { wch: 15 },  // Налог
        { wch: 20 }   // Прибыль
    ];
    
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Налоговый_учет_${data.year}`);
    XLSX.writeFile(wb, `Налоговый_учет_${data.year}.xlsx`);
};

// Следим за изменением фильтра
watch(activeFilter, () => {
    loadOperations();
});

onMounted(() => {
    loadOperations();
    loadCurrentYearTax();
});
</script>