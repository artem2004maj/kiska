<!-- resources/js/Pages/Accountant/Payments.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ОПЛАТЫ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ОПЛАТЫ УСЛУГ</h2>
            
            <!-- Фильтры -->
            <div class="flex flex-wrap gap-4 mb-6">
                <select v-model="filters.status" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                    <option value="all">Все оплаты</option>
                    <option value="pending">Ожидают оплаты</option>
                    <option value="paid">Оплачено</option>
                </select>
                <input type="date" v-model="filters.date_from" placeholder="От" 
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="date" v-model="filters.date_to" placeholder="До"
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="text" v-model="filters.search" placeholder="Поиск по клиенту..."
                       class="flex-1 px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <button @click="resetFilters" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Сбросить
                </button>
            </div>

            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Всего к оплате</p>
                    <p class="text-2xl font-semibold text-red-600">{{ formatPrice(totalPending) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Всего оплачено</p>
                    <p class="text-2xl font-semibold text-green-600">{{ formatPrice(totalPaid) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Общая сумма</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ formatPrice(totalAll) }}</p>
                </div>
            </div>

            <!-- Таблица оплат -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Клиент</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сумма</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in filteredPayments" :key="payment.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ payment.formatted_date }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ payment.client_name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ payment.service_name }}</td>
                            <td class="py-3 text-black dark:text-white/70 font-medium">{{ formatPrice(payment.total_price) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full" 
                                      :class="payment.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ payment.status === 'paid' ? 'Оплачено' : 'Ожидает' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <button v-if="payment.status === 'pending'"
                                        @click="acceptPayment(payment.id)"
                                        class="px-3 py-1 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition text-sm">
                                    Принять оплату
                                </button>
                                <span v-else class="text-xs text-gray-400">
                                    Чек: {{ payment.contract_number }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ filteredPayments.length }} из {{ payments.length }} записей
                </div>
                <div class="flex gap-2">
                    <button v-if="currentPage > 1" @click="currentPage--" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100">
                        Назад
                    </button>
                    <span class="px-3 py-1">Страница {{ currentPage }} из {{ totalPages }}</span>
                    <button v-if="currentPage < totalPages" @click="currentPage++" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100">
                        Вперед
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно успешной оплаты -->
        <Teleport to="body">
            <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSuccessModal = false"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6 text-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Оплата принята</h3>
                        <p class="text-gray-600 mb-4">Чек № {{ lastContractNumber }}</p>
                        <button @click="showSuccessModal = false" class="px-4 py-2 bg-[#3b82f6] text-white rounded-md">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    payments: Array,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const filters = ref({
    status: 'all',
    date_from: '',
    date_to: '',
    search: ''
});

const currentPage = ref(1);
const itemsPerPage = 10;
const showSuccessModal = ref(false);
const lastContractNumber = ref('');

const filteredPayments = computed(() => {
    let filtered = [...props.payments];
    
    if (filters.value.status !== 'all') {
        filtered = filtered.filter(p => p.status === filters.value.status);
    }
    
    if (filters.value.date_from) {
        filtered = filtered.filter(p => p.date >= filters.value.date_from);
    }
    
    if (filters.value.date_to) {
        filtered = filtered.filter(p => p.date <= filters.value.date_to);
    }
    
    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        filtered = filtered.filter(p => p.client_name.toLowerCase().includes(search));
    }
    
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filtered.slice(start, end);
});

const totalPages = computed(() => {
    let filtered = [...props.payments];
    if (filters.value.status !== 'all') {
        filtered = filtered.filter(p => p.status === filters.value.status);
    }
    return Math.ceil(filtered.length / itemsPerPage);
});

const totalPending = computed(() => {
    return props.payments
        .filter(p => p.status === 'pending')
        .reduce((sum, p) => sum + p.total_price, 0);
});

const totalPaid = computed(() => {
    return props.payments
        .filter(p => p.status === 'paid')
        .reduce((sum, p) => sum + p.total_price, 0);
});

const totalAll = computed(() => {
    return props.payments.reduce((sum, p) => sum + p.total_price, 0);
});

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const resetFilters = () => {
    filters.value = {
        status: 'all',
        date_from: '',
        date_to: '',
        search: ''
    };
    currentPage.value = 1;
};

const acceptPayment = async (id) => {
    if (!confirm('Подтвердить оплату?')) return;
    
    try {
        const response = await axios.post(`/api/accountant/payments/${id}/accept`);
        lastContractNumber.value = response.data.contract_number;
        showSuccessModal.value = true;
        router.reload();
    } catch (error) {
        alert('Ошибка при принятии оплаты');
    }
};
</script>