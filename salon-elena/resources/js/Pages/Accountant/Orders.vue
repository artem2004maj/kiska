<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'УПРАВЛЕНИЕ ЗАКАЗАМИ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-black dark:text-white">ЗАКАЗЫ ПОСТАВЩИКАМ</h2>
            </div>

            <!-- Фильтрация по статусу -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.value"
                    @click="selectedStatus = tab.value"
                    class="px-4 py-2 rounded-md transition"
                    :class="selectedStatus === tab.value 
                        ? 'bg-[#3b82f6] text-white' 
                        : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700'"
                >
                    {{ tab.label }}
                    <span v-if="tab.count > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">
                        {{ tab.count }}
                    </span>
                </button>
            </div>

            <!-- Список заказов -->
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3b82f6]"></div>
                <p class="mt-2 text-gray-500">Загрузка...</p>
            </div>

            <div v-else-if="filteredOrders.length === 0" class="text-center py-8 text-gray-500">
                Нет заказов в этом статусе
            </div>

            <div v-else class="space-y-4">
                <div 
                    v-for="order in filteredOrders" 
                    :key="order.id"
                    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition"
                >
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <!-- Основная информация -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-black dark:text-white">
                                    Заказ №{{ order.number }}
                                </h3>
                                <span 
                                    class="px-2 py-1 text-xs rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': order.status === 0,
                                        'bg-blue-100 text-blue-800': order.status === 1,
                                        'bg-green-100 text-green-800': order.status === 2,
                                        'bg-red-100 text-red-800': order.status === 3
                                    }"
                                >
                                    {{ order.status_text }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Поставщик:</span>
                                    <span class="font-medium text-black dark:text-white">{{ order.supplier_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Сумма заказа:</span>
                                    <span class="font-medium text-[#3b82f6]">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата создания:</span>
                                    <span>{{ formatDateTime(order.created_at) }}</span>
                                </div>
                                <div v-if="order.status === 1 || order.status === 2" class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата подтверждения:</span>
                                    <span>{{ order.confirmed_at ? formatDateTime(order.confirmed_at) : '—' }}</span>
                                </div>
                                <div v-if="order.status === 2" class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата получения:</span>
                                    <span>{{ order.received_at ? formatDateTime(order.received_at) : '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="flex gap-2">
                            <button 
                                @click="showOrderDetails(order)"
                                class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-zinc-700 transition"
                            >
                                Детали заказа
                            </button>
                            
                            <!-- Кнопка "Документ" для завершенных заказов -->
                            <button 
                                v-if="order.status === 2"
                                @click="showSupplyDocument(order)"
                                class="px-3 py-1.5 text-sm bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition"
                            >
                                Документ
                            </button>
                            
                            <!-- ИЗМЕНЕНО: Убрана кнопка "Подтвердить" - теперь только "Отменить" для неподтвержденных заказов -->
                            <button 
                                v-if="order.status === 0"
                                @click="cancelOrder(order)"
                                class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                            >
                                Отменить
                            </button>
                            
                            <!-- Кнопка "Принять на склад" остается для заказов в пути -->
                            <button 
                                v-if="order.status === 1"
                                @click="receiveOrder(order)"
                                class="px-3 py-1.5 text-sm bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition"
                            >
                                Принять на склад
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно деталей заказа -->
        <Teleport to="body">
            <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeDetailsModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Детали заказа №{{ selectedOrder?.number }}</h3>
                            <button @click="closeDetailsModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedOrder" class="p-6">
                            <!-- Основная информация -->
                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-500">Поставщик</p>
                                    <p class="font-medium">{{ selectedOrder.supplier_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Статус</p>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full mt-1"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': selectedOrder.status === 0,
                                            'bg-blue-100 text-blue-800': selectedOrder.status === 1,
                                            'bg-green-100 text-green-800': selectedOrder.status === 2,
                                            'bg-red-100 text-red-800': selectedOrder.status === 3
                                        }">
                                        {{ selectedOrder.status_text }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Дата создания</p>
                                    <p>{{ formatDateTime(selectedOrder.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Итого закупка</p>
                                    <p class="text-lg font-bold text-[#3b82f6]">{{ formatPrice(selectedOrder.total_amount) }}</p>
                                </div>
                            </div>
                            
                            <!-- Таблица материалов -->
                            <h4 class="font-semibold mb-3">Состав заказа</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                                            <th class="text-left py-2">Материал</th>
                                            <th class="text-left py-2">Количество</th>
                                            <th class="text-left py-2">Ед. изм.</th>
                                            <th class="text-right py-2">Цена за ед.</th>
                                            <th class="text-right py-2">Сумма</th>
                                         </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in selectedOrder.items" :key="idx"
                                            class="border-b border-gray-200 dark:border-zinc-700">
                                            <td class="py-2">{{ item.material_name }}</td>
                                            <td class="py-2">{{ item.quantity }}</td>
                                            <td class="py-2">{{ item.unit || 'шт' }}</td>
                                            <td class="py-2 text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="py-2 text-right">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-bold">
                                            <td colspan="4" class="py-3 text-right">Итого:</td>
                                            <td class="py-3 text-right text-[#3b82f6]">{{ formatPrice(selectedOrder.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t flex justify-end">
                            <button @click="closeDetailsModal" 
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
                    <svg v-if="notification.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ notification.message }}</span>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
    <SupplyReceiptModal 
        :show="showSupplyModal" 
        :receiptData="selectedSupplyOrder"
        @close="showSupplyModal = false"
    />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import SupplyReceiptModal from '@/Components/SupplyReceiptModal.vue';

const props = defineProps({
    accountant: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const loading = ref(true);
const orders = ref([]);
const selectedStatus = ref('all'); // Изменено на 'all' для отображения всех заказов по умолчанию
const showDetailsModal = ref(false);
const selectedOrder = ref(null);
const showSupplyModal = ref(false);
const selectedSupplyOrder = ref(null);
const loadingDocument = ref(false);

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

const tabs = computed(() => {
    const counts = {
        0: orders.value.filter(o => o.status === 0).length,
        1: orders.value.filter(o => o.status === 1).length,
        2: orders.value.filter(o => o.status === 2).length,
        3: orders.value.filter(o => o.status === 3).length,
        all: orders.value.length
    };
    
    return [
        { value: 0, label: 'Не подтверждены', count: counts[0] },
        { value: 1, label: 'В пути', count: counts[1] },
        { value: 2, label: 'Завершены', count: counts[2] },
        { value: 3, label: 'Отменены', count: counts[3] },
        { value: 'all', label: 'Все', count: counts.all }
    ];
});

const filteredOrders = computed(() => {
    if (selectedStatus.value === 'all') {
        return orders.value;
    }
    return orders.value.filter(o => o.status === selectedStatus.value);
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

const formatDateTime = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const loadOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/orders');
        orders.value = response.data;
    } catch (error) {
        console.error('Error loading orders:', error);
        showNotification('error', 'Ошибка загрузки заказов');
    } finally {
        loading.value = false;
    }
};

const showOrderDetails = (order) => {
    selectedOrder.value = order;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedOrder.value = null;
};

const showSupplyDocument = async (order) => {
    loadingDocument.value = true;
    try {
        const response = await axios.get(`/api/accountant/orders/${order.id}/document`);
        selectedSupplyOrder.value = response.data;
        showSupplyModal.value = true;
    } catch (error) {
        console.error('Error loading document data:', error);
        alert('Ошибка при загрузке данных документа');
    } finally {
        loadingDocument.value = false;
    }
};

// ИЗМЕНЕНО: Убрана функция confirmOrder - теперь подтверждает только директор
// Оставлена только функция cancelOrder для отмены заказов

const cancelOrder = async (order) => {
    if (!confirm(`Отменить заказ №${order.number}?`)) return;
    
    try {
        await axios.put(`/api/accountant/orders/${order.id}/status`, { status: 3 });
        showNotification('success', `Заказ №${order.number} отменен`);
        await loadOrders();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при отмене заказа');
    }
};

const receiveOrder = async (order) => {
    if (!confirm(`Подтвердить получение заказа №${order.number}? Материалы будут добавлены на склад.`)) return;
    
    try {
        await axios.put(`/api/accountant/orders/${order.id}/status`, { status: 2 });
        showNotification('success', `Заказ №${order.number} получен, материалы добавлены на склад`);
        await loadOrders();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при получении заказа');
    }
};

onMounted(() => {
    loadOrders();
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