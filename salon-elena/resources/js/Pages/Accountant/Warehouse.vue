<!-- resources/js/Pages/Accountant/Warehouse.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'СКЛАДСКОЙ УЧЕТ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="space-y-6">
            <!-- Основная таблица склада -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">СКЛАДСКОЙ УЧЕТ</h2>
                
                <!-- Поиск и фильтры -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <input type="text" placeholder="Поиск материалов..." 
                           v-model="searchQuery"
                           class="flex-1 px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                    <select v-model="statusFilter" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                        <option value="all">Все материалы</option>
                        <option value="critical">Критичные</option>
                        <option value="low">Мало</option>
                        <option value="normal">Норма</option>
                    </select>
                </div>

                <!-- Таблица материалов -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left py-3 text-black dark:text-white font-medium">Наименование</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Остаток</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Ед. изм.</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Мин. остаток</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Цена за ед.</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="material in filteredMaterials" :key="material.id" 
                                class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                <td class="py-3 text-black dark:text-white/70">{{ material.name }}</td>
                                <td class="py-3 text-black dark:text-white/70 font-medium" :class="{'text-red-600': material.current_balance <= material.min_stock}">
                                    {{ material.current_balance }}
                                </td>
                                <td class="py-3 text-black dark:text-white/70">{{ material.unit }}</td>
                                <td class="py-3 text-black dark:text-white/70">{{ material.min_stock }}</td>
                                <td class="py-3 text-black dark:text-white/70">{{ formatPrice(material.price_per_unit) }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full" 
                                          :class="{
                                              'bg-red-100 text-red-800': material.status === 'critical',
                                              'bg-yellow-100 text-yellow-800': material.status === 'low',
                                              'bg-green-100 text-green-800': material.status === 'normal'
                                          }">
                                        {{ material.status === 'critical' ? 'Критично' : material.status === 'low' ? 'Мало' : 'Норма' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <button @click="addToOrder(material)" 
                                            class="text-[#3b82f6] hover:underline text-sm"
                                            :disabled="material.inOrder">
                                        {{ material.inOrder ? 'В заказе' : 'В заказ' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Блок закупки -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-xl font-semibold text-black dark:text-white mb-4">ОФОРМЛЕНИЕ ЗАКУПКИ</h3>
                
                <!-- Выбор поставщика -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">Поставщик</label>
                    <select v-model="selectedSupplier" class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                        <option value="">-- Выберите поставщика --</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.supplier_name }}
                        </option>
                    </select>
                </div>

                <!-- Корзина заказа -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">Корзина заказа</label>
                    <div class="border border-gray-200 dark:border-zinc-700 rounded-lg">
                        <div v-if="orderItems.length === 0" class="p-4 text-center text-gray-500">
                            Корзина пуста
                        </div>
                        <div v-else>
                            <div v-for="(item, index) in orderItems" :key="index" 
                                 class="flex justify-between items-center p-3 border-b border-gray-200 dark:border-zinc-700 last:border-0">
                                <div>
                                    <span class="text-black dark:text-white">{{ item.name }}</span>
                                    <span class="text-sm text-gray-500 ml-2">{{ item.quantity }} {{ item.unit }}</span>
                                    <div class="text-sm text-gray-500">Цена: {{ formatPrice(item.price_per_unit) }}/ед.</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="number" v-model="item.price_per_unit" step="1" min="0"
                                           class="w-24 px-2 py-1 border rounded-md text-sm" />
                                    <button @click="removeFromOrder(index)" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-zinc-800 flex justify-between font-medium">
                                <span>Итого:</span>
                                <span class="text-[#3b82f6]">{{ formatPrice(orderTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Кнопка оформления -->
                <button @click="submitOrder" 
                        :disabled="orderItems.length === 0 || !selectedSupplier"
                        class="w-full px-4 py-3 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Оформить заявку на закупку
                </button>

                <!-- Статус заявки -->
                <div v-if="orderStatus" class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-center">
                    {{ orderStatus }}
                </div>
            </div>

            <!-- Активные заказы -->
            <div v-if="activeOrders.length > 0" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-xl font-semibold text-black dark:text-white mb-4">АКТИВНЫЕ ЗАКАЗЫ</h3>
                <div class="space-y-3">
                    <div v-for="order in activeOrders" :key="order.id" 
                         class="flex justify-between items-center p-3 border rounded-lg">
                        <div>
                            <p class="font-medium">Заказ №{{ order.number }}</p>
                            <p class="text-sm text-gray-500">Поставщик: {{ order.supplier_name }}</p>
                            <p class="text-sm text-gray-500">Дата: {{ formatDate(order.date) }}</p>
                        </div>
                        <button @click="receiveOrder(order.id)" 
                                class="px-3 py-1 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 text-sm">
                            Принять на склад
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно выбора количества -->
        <Teleport to="body">
            <div v-if="showQuantityModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showQuantityModal = false"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Добавить в заказ</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Материал: {{ selectedMaterial?.name }}</label>
                            <label class="block text-sm font-medium mb-1 mt-3">Количество ({{ selectedMaterial?.unit }})</label>
                            <input type="number" v-model="quantityToOrder" min="1" 
                                   class="w-full px-3 py-2 border rounded-md" />
                            <label class="block text-sm font-medium mb-1 mt-3">Цена за единицу (₽)</label>
                            <input type="number" v-model="pricePerUnit" min="0" step="1"
                                   class="w-full px-3 py-2 border rounded-md" />
                        </div>
                        <div class="flex gap-3">
                            <button @click="confirmAddToOrder" 
                                    class="flex-1 px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90">
                                Добавить
                            </button>
                            <button @click="showQuantityModal = false" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
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
    materials: Array,
    suppliers: Array,
    activeOrders: Array,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const searchQuery = ref('');
const statusFilter = ref('all');
const selectedSupplier = ref('');
const orderItems = ref([]);
const orderStatus = ref('');
const showQuantityModal = ref(false);
const selectedMaterial = ref(null);
const quantityToOrder = ref(1);
const pricePerUnit = ref(0);

const filteredMaterials = computed(() => {
    let filtered = [...props.materials];
    
    if (searchQuery.value) {
        filtered = filtered.filter(m => 
            m.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    
    if (statusFilter.value !== 'all') {
        filtered = filtered.filter(m => m.status === statusFilter.value);
    }
    
    return filtered;
});

const orderTotal = computed(() => {
    return orderItems.value.reduce((sum, item) => sum + (item.quantity * item.price_per_unit), 0);
});

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

const addToOrder = (material) => {
    selectedMaterial.value = material;
    quantityToOrder.value = material.min_stock - material.current_balance > 0 
        ? material.min_stock - material.current_balance 
        : 10;
    pricePerUnit.value = material.price_per_unit || 100;
    showQuantityModal.value = true;
};

const confirmAddToOrder = () => {
    if (quantityToOrder.value <= 0) return;
    
    const existingIndex = orderItems.value.findIndex(i => i.id === selectedMaterial.value.id);
    if (existingIndex >= 0) {
        orderItems.value[existingIndex].quantity += quantityToOrder.value;
    } else {
        orderItems.value.push({
            id: selectedMaterial.value.id,
            name: selectedMaterial.value.name,
            quantity: quantityToOrder.value,
            unit: selectedMaterial.value.unit,
            price_per_unit: pricePerUnit.value
        });
    }
    selectedMaterial.value.inOrder = true;
    showQuantityModal.value = false;
};

const removeFromOrder = (index) => {
    orderItems.value.splice(index, 1);
};

const submitOrder = async () => {
    if (orderItems.value.length === 0 || !selectedSupplier.value) return;
    
    try {
        const response = await axios.post('/api/accountant/orders/create', {
            supplier_id: selectedSupplier.value,
            items: orderItems.value.map(item => ({
                material_id: item.id,
                quantity: item.quantity,
                price_per_unit: item.price_per_unit
            }))
        });
        
        orderStatus.value = response.data.message;
        orderItems.value = [];
        selectedSupplier.value = '';
        
        setTimeout(() => router.reload(), 2000);
    } catch (error) {
        orderStatus.value = 'Ошибка при создании заказа';
    }
};

const receiveOrder = async (orderId) => {
    if (!confirm('Подтвердить получение заказа?')) return;
    
    try {
        await axios.post(`/api/accountant/orders/${orderId}/receive`);
        router.reload();
    } catch (error) {
        alert('Ошибка при приемке заказа');
    }
};
</script>