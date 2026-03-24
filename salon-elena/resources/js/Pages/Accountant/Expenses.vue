<!-- resources/js/Pages/Accountant/Expenses.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'РАСХОДЫ КОМПАНИИ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <!-- Фильтры -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">
                        Тип расхода
                    </label>
                    <select v-model="filters.type" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                        <option value="all">Все расходы</option>
                        <option value="salary">Зарплата</option>
                        <option value="supplier_order">Заказы поставщикам</option>
                    </select>
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">
                        Период с
                    </label>
                    <input type="date" v-model="filters.date_from" 
                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">
                        Период по
                    </label>
                    <input type="date" v-model="filters.date_to" 
                           class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                </div>
                
                <div class="flex gap-2">
                    <button @click="loadExpenses" 
                            class="px-6 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition">
                        Применить
                    </button>
                    <button @click="resetFilters" 
                            class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                        Сбросить
                    </button>
                </div>
            </div>
        </div>

        <!-- Общая сумма расходов -->
        <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg p-6 shadow mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">Расходы за выбранный период</p>
            <p class="text-3xl font-bold text-red-600 dark:text-red-400">{{ formatPrice(totalAmount) }}</p>
        </div>

        <!-- Таблица расходов -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3b82f6]"></div>
                <p class="mt-2 text-gray-500">Загрузка...</p>
            </div>

            <div v-else-if="expenses.length === 0" class="text-center py-8 text-gray-500">
                Нет расходов за выбранный период
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Тип</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Описание</th>
                            <th class="text-right py-3 text-black dark:text-white font-medium">Сумма</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="expense in expenses" :key="expense.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ expense.formatted_date }}</td>
                            <td class="py-3">
                                <span class="flex items-center gap-2">
                                    <span class="text-lg">{{ expense.type_icon }}</span>
                                    <span>{{ expense.type_text }}</span>
                                </span>
                            </td>
                            <td class="py-3 text-black dark:text-white/70">{{ expense.description }}</td>
                            <td class="py-3 text-right font-medium text-red-600">{{ formatPrice(expense.amount) }}</td>
                            <td class="py-3">
                                <div class="flex gap-2">
                                    <button @click="viewDetails(expense)" 
                                            class="px-3 py-1 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition text-sm">
                                        Детали
                                    </button>
                                    <button @click="viewDocument(expense)" 
                                            class="px-3 py-1 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition text-sm">
                                        Документ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-zinc-700 font-bold">
                            <td colspan="3" class="py-3 text-right">Итого:</td>
                            <td class="py-3 text-right text-red-600">{{ formatPrice(totalAmount) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Модальное окно деталей -->
        <Teleport to="body">
            <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-black dark:text-white">
                                Детали расхода
                            </h3>
                            <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="selectedExpense" class="p-6">
                            <!-- Детали зарплаты -->
                            <div v-if="selectedExpense.type === 'salary'">
                                <div class="text-center mb-6">
                                    <h4 class="font-bold text-xl">РАСЧЕТНЫЙ ЛИСТОК</h4>
                                    <p class="text-gray-600">{{ selectedExpense.data.employee_name }}</p>
                                    <p class="text-sm text-gray-500">{{ selectedExpense.data.month_name }} {{ selectedExpense.data.year }}</p>
                                </div>
                                
                                <div class="border rounded-lg overflow-hidden mb-6">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Вид начисления</th>
                                                <th class="px-4 py-2 text-right">Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="border-t">
                                                <td class="px-4 py-2">Оплата по часам</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(selectedExpense.data.calculation_details.base_salary) }}</td>
                                            </tr>
                                            <tr class="border-t font-medium">
                                                <td class="px-4 py-2">Итого начислено</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(selectedExpense.data.calculation_details.total_accrued || selectedExpense.data.total_amount) }}</td>
                                            </tr>
                                            <tr class="border-t">
                                                <td class="px-4 py-2">НДФЛ (13%)</td>
                                                <td class="px-4 py-2 text-right text-red-600">{{ formatPrice(selectedExpense.data.calculation_details.ndfl) }}</td>
                                            </tr>
                                            <tr class="border-t font-bold">
                                                <td class="px-4 py-2">К выплате</td>
                                                <td class="px-4 py-2 text-right text-red-600 text-lg">{{ formatPrice(selectedExpense.data.total_amount) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="text-sm text-gray-500 text-center">
                                    <p>Ставка: {{ formatPrice(selectedExpense.data.hourly_rate) }} ₽/час</p>
                                    <p>Отработано часов: {{ selectedExpense.data.hours_worked }}</p>
                                    <p>Дата выплаты: {{ selectedExpense.data.payment_date }}</p>
                                </div>
                            </div>

                            <!-- Детали заказа поставщику -->
                            <div v-else-if="selectedExpense.type === 'supplier_order'">
                                <div class="mb-6">
                                    <h4 class="font-semibold text-lg mb-3">Заказ №{{ selectedExpense.data.order_number }}</h4>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Поставщик</p>
                                            <p class="font-medium">{{ selectedExpense.data.supplier_name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Дата подтверждения</p>
                                            <p class="font-medium">{{ selectedExpense.data.confirmed_at }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="font-semibold mb-3">Состав заказа</h5>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left">Материал</th>
                                                <th class="px-4 py-2 text-right">Количество</th>
                                                <th class="px-4 py-2 text-right">Цена</th>
                                                <th class="px-4 py-2 text-right">Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, idx) in selectedExpense.data.items" :key="idx" class="border-t">
                                                <td class="px-4 py-2">{{ item.material_name }}</td>
                                                <td class="px-4 py-2 text-right">{{ item.quantity }} {{ item.unit }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-gray-50 font-bold">
                                            <tr class="border-t">
                                                <td colspan="3" class="px-4 py-2 text-right">Итого:</td>
                                                <td class="px-4 py-2 text-right text-red-600">{{ formatPrice(selectedExpense.data.total_amount) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-end">
                            <button @click="showDetailsModal = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Уведомления -->
        <Teleport to="body">
            <div v-if="notification.show" 
                 class="fixed bottom-4 right-4 z-50 animate-slide-up"
                 :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                <div class="px-4 py-3 rounded-lg shadow-lg text-white flex items-center gap-3">
                    <span>{{ notification.message }}</span>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const loading = ref(false);
const expenses = ref([]);
const totalAmount = ref(0);
const showDetailsModal = ref(false);
const selectedExpense = ref(null);

const filters = ref({
    type: 'all',
    date_from: '',
    date_to: ''
});

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

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

const loadExpenses = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/expenses', {
            params: {
                type: filters.value.type,
                date_from: filters.value.date_from,
                date_to: filters.value.date_to
            }
        });
        expenses.value = response.data.expenses;
        totalAmount.value = response.data.total_amount;
    } catch (error) {
        console.error('Error loading expenses:', error);
        showNotification('error', 'Ошибка загрузки расходов');
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => {
    filters.value = {
        type: 'all',
        date_from: '',
        date_to: ''
    };
    loadExpenses();
};

const viewDetails = async (expense) => {
    try {
        const response = await axios.get(`/api/accountant/expenses/${expense.id}/details`);
        selectedExpense.value = response.data;
        showDetailsModal.value = true;
    } catch (error) {
        console.error('Error loading details:', error);
        showNotification('error', 'Ошибка загрузки деталей');
    }
};

const viewDocument = async (expense) => {
    try {
        const response = await axios.get(`/api/accountant/expenses/${expense.id}/document`);
        // Открываем документ в новом окне или показываем модалку
        console.log('Document data:', response.data);
        showNotification('success', 'Документ загружен');
    } catch (error) {
        console.error('Error loading document:', error);
        showNotification('error', 'Ошибка загрузки документа');
    }
};

onMounted(() => {
    // Устанавливаем начальные даты: последние 30 дней
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    filters.value.date_from = startDate.toISOString().split('T')[0];
    filters.value.date_to = endDate.toISOString().split('T')[0];
    
    loadExpenses();
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