<!-- resources/js/Pages/Admin/Orders.vue -->
<template>
    <AdminLayout 
        :admin="admin"
        :pageTitle="'ПОСТАВКИ'"
        :criticalCount="criticalCount"
    >
        <div class="space-y-6">
            <!-- Таблица заказов -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">ЗАКАЗЫ ПОСТАВЩИКАМ</h2>
                        <div class="flex gap-2">
                            <button @click="refreshOrders" 
                                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                🔄 Обновить
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Фильтр по статусу -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button v-for="tab in tabs" :key="tab.value"
                                @click="selectedStatus = tab.value"
                                class="px-4 py-2 rounded-lg text-sm transition"
                                :class="selectedStatus === tab.value 
                                    ? 'bg-blue-500 text-white' 
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                            {{ tab.label }}
                            <span class="ml-1 px-2 py-0.5 text-xs rounded-full"
                                  :class="selectedStatus === tab.value ? 'bg-white/20' : 'bg-gray-200'">
                                {{ tab.count }}
                            </span>
                        </button>
                    </div>

                    <!-- Загрузка -->
                    <div v-if="loading" class="text-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                        <p class="mt-2 text-gray-500">Загрузка заказов...</p>
                    </div>

                    <!-- Нет заказов -->
                    <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p>Нет заказов в этом статусе</p>
                    </div>

                    <!-- Список заказов -->
                    <div v-else class="space-y-4">
                        <div v-for="order in filteredOrders" :key="order.id" 
                             class="border rounded-lg overflow-hidden hover:shadow-md transition">
                            <!-- Заголовок заказа -->
                            <div class="p-4 bg-gray-50 border-b flex flex-wrap justify-between items-center gap-3">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-semibold text-gray-800">
                                        Заказ №{{ order.number }}
                                    </h3>
                                    <span class="px-2 py-1 text-xs rounded-full" :class="statusClass(order.status)">
                                        {{ order.status_text }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-blue-600">{{ formatPrice(order.total_amount) }}</p>
                                    <p class="text-xs text-gray-500">Общая сумма</p>
                                </div>
                            </div>
                            
                            <!-- Информация о заказе -->
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Поставщик</p>
                                        <p class="font-medium text-gray-800">{{ order.supplier_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Дата создания</p>
                                        <p class="text-gray-800">{{ formatDateTime(order.created_at) }}</p>
                                    </div>
                                    <div v-if="order.confirmed_at">
                                        <p class="text-sm text-gray-500">Дата подтверждения</p>
                                        <p class="text-gray-800">{{ formatDateTime(order.confirmed_at) }}</p>
                                    </div>
                                    <div v-if="order.received_at">
                                        <p class="text-sm text-gray-500">Дата получения</p>
                                        <p class="text-green-600">{{ formatDateTime(order.received_at) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Состав заказа -->
                                <div class="mt-3 pt-3 border-t">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Состав заказа:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="(item, idx) in order.items" :key="idx"
                                              class="px-2 py-1 bg-gray-100 rounded-md text-sm">
                                            {{ item.material_name }}: {{ item.quantity }} {{ item.unit }}
                                            ({{ formatPrice(item.price) }}/{{ item.unit }})
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Кнопки действий -->
                                <div class="mt-4 pt-3 border-t flex gap-3">
                                    <button @click="showDetails(order)" 
                                            class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                                        📋 Детали
                                    </button>
                                    <button v-if="order.status === 1" 
                                            @click="receiveOrder(order)" 
                                            class="px-3 py-1.5 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                        📦 Принять на склад
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно деталей заказа -->
        <Teleport to="body">
            <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
                    <div class="relative bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Детали заказа №{{ selectedOrder?.number }}</h3>
                            <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
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
                                    <span class="inline-block px-2 py-1 text-xs rounded-full" :class="statusClass(selectedOrder.status)">
                                        {{ selectedOrder.status_text }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Дата создания</p>
                                    <p>{{ formatDateTime(selectedOrder.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Итого закупка</p>
                                    <p class="text-lg font-bold text-blue-600">{{ formatPrice(selectedOrder.total_amount) }}</p>
                                </div>
                                <div v-if="selectedOrder.confirmed_at">
                                    <p class="text-sm text-gray-500">Дата подтверждения</p>
                                    <p>{{ formatDateTime(selectedOrder.confirmed_at) }}</p>
                                </div>
                                <div v-if="selectedOrder.received_at">
                                    <p class="text-sm text-gray-500">Дата получения</p>
                                    <p>{{ formatDateTime(selectedOrder.received_at) }}</p>
                                </div>
                            </div>
                            
                            <!-- Таблица материалов -->
                            <h4 class="font-semibold mb-3">Состав заказа</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left px-4 py-2">№</th>
                                            <th class="text-left px-4 py-2">Материал</th>
                                            <th class="text-right px-4 py-2">Количество</th>
                                            <th class="text-right px-4 py-2">Ед. изм.</th>
                                            <th class="text-right px-4 py-2">Цена</th>
                                            <th class="text-right px-4 py-2">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in selectedOrder.items" :key="idx" class="border-t">
                                            <td class="px-4 py-2">{{ idx + 1 }}</td>
                                            <td class="px-4 py-2">{{ item.material_name }}</td>
                                            <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                            <td class="px-4 py-2 text-right">{{ item.unit }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 font-bold">
                                        <tr class="border-t">
                                            <td colspan="5" class="px-4 py-2 text-right">Итого:</td>
                                            <td class="px-4 py-2 text-right text-blue-600">{{ formatPrice(selectedOrder.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white px-6 py-4 border-t flex justify-end">
                            <button @click="showDetailsModal = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
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
                <div class="px-4 py-3 rounded-lg shadow-lg text-white">
                    {{ notification.message }}
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    admin: Object,
    criticalCount: Number
});

const orders = ref([]);
const loading = ref(false);
const selectedStatus = ref('all');
const showDetailsModal = ref(false);
const selectedOrder = ref(null);

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
        { value: 'all', label: 'Все заказы', count: counts.all },
        { value: 0, label: '⏳ Ожидают', count: counts[0] },
        { value: 1, label: '🚚 В пути', count: counts[1] },
        { value: 2, label: '✅ Получены', count: counts[2] },
        { value: 3, label: '❌ Отменены', count: counts[3] }
    ];
});

const filteredOrders = computed(() => {
    if (selectedStatus.value === 'all') return orders.value;
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

const statusClass = (status) => {
    return {
        0: 'bg-yellow-100 text-yellow-700',
        1: 'bg-blue-100 text-blue-700',
        2: 'bg-green-100 text-green-700',
        3: 'bg-red-100 text-red-700'
    }[status] || 'bg-gray-100 text-gray-700';
};

const loadOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/admin/orders');
        orders.value = response.data;
    } catch (error) {
        console.error('Error loading orders:', error);
        showNotification('error', 'Ошибка загрузки заказов');
    } finally {
        loading.value = false;
    }
};

const refreshOrders = () => {
    loadOrders();
};

const receiveOrder = async (order) => {
    if (!confirm(`Подтвердить получение заказа №${order.number}? Материалы будут добавлены на склад.`)) return;
    
    try {
        await axios.post(`/api/admin/orders/${order.id}/receive`);
        showNotification('success', `Заказ №${order.number} принят на склад`);
        await loadOrders();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при приемке заказа');
    }
};

const showDetails = (order) => {
    selectedOrder.value = order;
    showDetailsModal.value = true;
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