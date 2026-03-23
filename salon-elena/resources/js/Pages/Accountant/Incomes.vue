<!-- resources/js/Pages/Accountant/Incomes.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ДОХОДЫ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ДОХОДЫ ОТ УСЛУГ</h2>
            
            <!-- Фильтры -->
            <div class="flex flex-wrap gap-4 mb-6">
                <input type="date" v-model="filters.date_from" placeholder="От" 
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="date" v-model="filters.date_to" placeholder="До"
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="text" v-model="filters.client_search" placeholder="Поиск по клиенту..."
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md w-64" />
                <input type="text" v-model="filters.service_search" placeholder="Поиск по услуге..."
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md w-64" />
                <button @click="resetFilters" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Сбросить
                </button>
            </div>

            <!-- Общая сумма -->
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Всего доходов за выбранный период</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatPrice(totalSum) }}</p>
            </div>

            <!-- Таблица доходов -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Клиент</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сумма</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Номер чека</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="income in paginatedIncomes" :key="income.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ income.formatted_date }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ income.client_name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ income.services_list }}</td>
                            <td class="py-3 text-black dark:text-white/70 font-medium">{{ formatPrice(income.total_price) }}</td>
                            <td class="py-3">
                                <span class="text-sm font-mono text-gray-600 dark:text-gray-400">
                                    {{ income.contract_number }}
                                </span>
                            </td>
                            <td class="py-3">
                                <button @click="viewReceipt(income.id)"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">
                                    Просмотр чека
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ paginatedIncomes.length }} из {{ incomes.length }} записей
                </div>
                <div class="flex gap-2">
                    <button v-if="currentPage > 1" @click="currentPage--" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Назад
                    </button>
                    <span class="px-3 py-1">Страница {{ currentPage }} из {{ totalPages }}</span>
                    <button v-if="currentPage < totalPages" @click="currentPage++" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Вперед
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно чека -->
        <ReceiptModal 
            :show="showReceiptModal" 
            :contractId="selectedContractId"
            @close="showReceiptModal = false"
        />
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import ReceiptModal from '@/Components/ReceiptModal.vue';

const props = defineProps({
    accountant: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const filters = ref({
    date_from: '',
    date_to: '',
    client_search: '',
    service_search: ''
});

const incomes = ref([]);
const totalSum = ref(0);
const currentPage = ref(1);
const itemsPerPage = 10;
const showReceiptModal = ref(false);
const selectedContractId = ref(null);

// Загрузка данных
const loadIncomes = async () => {
    try {
        const response = await axios.get('/api/accountant/incomes', {
            params: filters.value
        });
        incomes.value = response.data.incomes;
        totalSum.value = response.data.total_sum;
        currentPage.value = 1;
    } catch (error) {
        console.error('Ошибка загрузки доходов:', error);
        alert('Ошибка при загрузке данных');
    }
};

// Отслеживаем изменение фильтров
watch(filters, () => {
    loadIncomes();
}, { deep: true });

// Пагинация
const paginatedIncomes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return incomes.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(incomes.value.length / itemsPerPage);
});

// Форматирование цены
const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

// Сброс фильтров
const resetFilters = () => {
    filters.value = {
        date_from: '',
        date_to: '',
        client_search: '',
        service_search: ''
    };
};

// Просмотр чека
const viewReceipt = (contractId) => {
    selectedContractId.value = contractId;
    showReceiptModal.value = true;
};

// Загружаем данные при монтировании
loadIncomes();
</script>