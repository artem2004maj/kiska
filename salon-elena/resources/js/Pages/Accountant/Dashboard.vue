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
        <!-- Быстрые действия -->
        <div class="mb-8">
            <div class="flex gap-4">
                <button @click="openPayments" class="flex items-center gap-2 px-6 py-3 bg-[#3b82f6] text-white rounded-lg hover:bg-[#3b82f6]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Принять оплаты
                </button>
                <button @click="openWarehouse" class="flex items-center gap-2 px-6 py-3 bg-[#22c55e] text-white rounded-lg hover:bg-[#22c55e]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-4.5M20 7v4.5M20 7l-6 6m-2-2l-4 4" />
                    </svg>
                    Заказать материалы
                </button>
                <button @click="openSalary" class="flex items-center gap-2 px-6 py-3 bg-[#f59e0b] text-white rounded-lg hover:bg-[#f59e0b]/90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Рассчитать ЗП
                </button>
            </div>
        </div>

        <!-- Карточки статистики -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Неоплаченных услуг</p>
                        <p class="text-2xl font-semibold text-red-600">{{ stats.unpaid_count }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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

        <!-- Неоплаченные услуги -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-black dark:text-white">НЕОПЛАЧЕННЫЕ УСЛУГИ</h2>
                <Link href="/accountant/payments" class="text-[#3b82f6] hover:underline">
                    Все оплаты →
                </Link>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div v-if="unpaidServices.length > 0" class="space-y-4">
                    <div v-for="service in unpaidServices" :key="service.id" 
                         class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-zinc-700 last:border-0">
                        <div>
                            <p class="font-medium text-black dark:text-white">{{ service.client_name }}</p>
                            <p class="text-sm text-gray-500">{{ service.service_name }}</p>
                            <p class="text-xs text-gray-400">{{ service.formatted_date }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-red-600">{{ formatPrice(service.total_price) }}</p>
                            <button @click="acceptPayment(service.id)" 
                                    class="mt-2 px-3 py-1 bg-[#22c55e] text-white text-sm rounded-md hover:bg-[#22c55e]/90">
                                Принять оплату
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    Нет неоплаченных услуг
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
                        </div>
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Критично</span>
                    </div>
                </div>
                <div v-if="criticalMaterials.length === 0" class="col-span-3 text-center py-8 text-gray-500">
                    Все материалы в достаточном количестве
                </div>
            </div>
        </div>

        <!-- Последние оплаты -->
        <div>
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ПОСЛЕДНИЕ ОПЛАТЫ</h2>
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div v-if="recentPayments.length > 0" class="space-y-4">
                    <div v-for="payment in recentPayments" :key="payment.id" 
                         class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-zinc-700 last:border-0">
                        <div>
                            <p class="font-medium text-black dark:text-white">{{ payment.client_name }}</p>
                            <p class="text-xs text-gray-400">Чек: {{ payment.contract_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">{{ formatPrice(payment.amount) }}</p>
                            <p class="text-xs text-gray-400">{{ payment.formatted_date }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    Нет проведенных оплат
                </div>
            </div>
        </div>
    </AccountantLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    stats: Object,
    unpaidServices: Array,
    criticalMaterials: Array,
    recentPayments: Array
});

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const acceptPayment = async (id) => {
    if (!confirm('Подтвердить оплату?')) return;
    
    try {
        await axios.post(`/api/accountant/payments/${id}/accept`);
        router.reload();
    } catch (error) {
        alert('Ошибка при принятии оплаты');
    }
};

const openPayments = () => {
    router.get('/accountant/payments');
};

const openWarehouse = () => {
    router.get('/accountant/warehouse');
};

const openSalary = () => {
    router.get('/accountant/salary');
};
</script>