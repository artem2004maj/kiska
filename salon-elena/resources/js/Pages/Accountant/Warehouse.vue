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
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-black dark:text-white">СКЛАДСКОЙ УЧЕТ</h2>
                    <button @click="openAddMaterialModal" 
                            class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить материал
                    </button>
                </div>
                
                <!-- Фильтры -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <select v-model="selectedSupplierFilter" 
                            class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md min-w-[200px]"
                            @change="onSupplierFilterChange">
                        <option value="">Все поставщики</option>
                        <option v-for="supplier in suppliers" :key="supplier.supplier_id" :value="supplier.supplier_id">
                            {{ supplier.supplier_name }}
                        </option>
                    </select>
                    
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
                <div v-if="!loadingMaterials" class="overflow-x-auto">
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
                                    <div class="flex gap-2">
                                        <button @click="editMaterial(material)" 
                                                class="text-[#3b82f6] hover:underline text-sm">
                                            Редактировать
                                        </button>
                                        <button @click="deleteMaterial(material)" 
                                                class="text-red-500 hover:underline text-sm">
                                            Удалить
                                        </button>
                                        <button v-if="selectedSupplierFilter" 
                                                @click="addToOrder(material)" 
                                                class="text-[#10b981] hover:underline text-sm"
                                                :disabled="material.inOrder">
                                            {{ material.inOrder ? 'В заказе' : 'В заказ' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredMaterials.length === 0">
                                <td colspan="7" class="py-8 text-center text-gray-500">
                                    Материалы не найдены
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div v-else class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3b82f6]"></div>
                    <p class="mt-2 text-gray-500">Загрузка материалов...</p>
                </div>
            </div>

            <!-- Блок закупки -->
            <div v-if="selectedSupplierFilter" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-xl font-semibold text-black dark:text-white mb-4">
                    ОФОРМЛЕНИЕ ЗАКУПКИ ДЛЯ {{ getSelectedSupplierName() }}
                </h3>
                
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
                                    <div class="text-sm text-gray-500">Цена закупки: {{ formatPrice(item.price_per_unit) }}/ед.</div>
                                    <div class="text-xs text-gray-400">Цена продажи: {{ formatPrice(item.sale_price) }}/ед.</div>
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
                                <span>Итого закупка:</span>
                                <span class="text-[#3b82f6]">{{ formatPrice(orderTotal) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button @click="submitOrder" 
                        :disabled="orderItems.length === 0"
                        class="w-full px-4 py-3 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Оформить заявку на закупку
                </button>

                <div v-if="orderStatus" class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-center">
                    {{ orderStatus }}
                </div>
            </div>

            <!-- Активные заказы (только просмотр) -->
            <div v-if="activeOrders.length > 0" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <h3 class="text-xl font-semibold text-black dark:text-white mb-4">ЗАКАЗЫ В ПУТИ</h3>
                <div class="space-y-3">
                    <div v-for="order in activeOrders" :key="order.id" 
                         class="flex justify-between items-center p-3 border rounded-lg">
                        <div>
                            <p class="font-medium">Заказ №{{ order.number }}</p>
                            <p class="text-sm text-gray-500">Поставщик: {{ order.supplier_name }}</p>
                            <p class="text-sm text-gray-500">Дата: {{ formatDate(order.date) }}</p>
                            <div class="text-xs text-gray-400 mt-1">
                                Состав: 
                                <span v-for="(item, idx) in order.items" :key="idx">
                                    {{ item.material_name }} ({{ item.quantity }} {{ item.unit }}){{ idx < order.items.length - 1 ? ', ' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                В пути
                            </span>
                            <p class="text-sm font-medium text-blue-600 mt-1">{{ formatPrice(order.total_amount) }}</p>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3 text-center">
                    ℹ️ При поступлении заказа на склад, администратор примет его и материалы будут добавлены в наличие
                </p>
            </div>
        </div>

        <!-- Модальные окна (оставляем как есть) -->
        <Teleport to="body">
            <div v-if="showMaterialModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeMaterialModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ isEditingMaterial ? 'Редактировать материал' : 'Добавить материал' }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Наименование *</label>
                                <input type="text" v-model="materialForm.name" 
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Единица измерения *</label>
                                <input type="text" v-model="materialForm.unit" 
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Текущий остаток *</label>
                                    <input type="number" v-model="materialForm.current_balance" min="0"
                                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Мин. остаток *</label>
                                    <input type="number" v-model="materialForm.min_stock" min="0"
                                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Цена за единицу (₽)</label>
                                <input type="number" v-model="materialForm.price_per_unit" min="0" step="0.01"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button @click="saveMaterial" 
                                    :disabled="!materialForm.name || !materialForm.unit"
                                    class="flex-1 px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 disabled:opacity-50">
                                {{ isEditingMaterial ? 'Сохранить' : 'Добавить' }}
                            </button>
                            <button @click="closeMaterialModal" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

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
                            <label class="block text-sm font-medium mb-1 mt-3">Цена закупки (₽)</label>
                            <input type="number" v-model="pricePerUnit" min="0" step="1"
                                   class="w-full px-3 py-2 border rounded-md" />
                            <div class="text-xs text-gray-500 mt-2">
                                Цена продажи: {{ formatPrice(selectedMaterial?.price_per_unit) }}
                            </div>
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

        <Teleport to="body">
            <div v-if="notification.show" 
                 class="fixed bottom-4 right-4 z-50 animate-slide-up"
                 :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                <div class="px-4 py-3 rounded-lg shadow-lg text-white">
                    {{ notification.message }}
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    materials: Array,
    suppliers: Array,
    supplierMaterials: Object,
    activeOrders: Array,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

// Состояния
const searchQuery = ref('');
const statusFilter = ref('all');
const selectedSupplierFilter = ref('');
const currentMaterials = ref([]);
const loadingMaterials = ref(false);
const orderItems = ref([]);
const orderStatus = ref('');
const showMaterialModal = ref(false);
const isEditingMaterial = ref(false);
const showQuantityModal = ref(false);
const selectedMaterial = ref(null);
const quantityToOrder = ref(1);
const pricePerUnit = ref(0);

const materialForm = ref({
    material_id: null,
    name: '',
    unit: '',
    current_balance: 0,
    min_stock: 0,
    price_per_unit: null
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

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

// Фильтрация материалов
const filteredMaterials = computed(() => {
    let filtered = [...currentMaterials.value];
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

const getSelectedSupplierName = () => {
    const supplier = props.suppliers?.find(s => s.supplier_id === selectedSupplierFilter.value);
    return supplier ? supplier.supplier_name : '';
};

const orderTotal = computed(() => {
    return orderItems.value.reduce((sum, item) => sum + (item.quantity * item.price_per_unit), 0);
});

// Загрузка материалов при выборе поставщика
const onSupplierFilterChange = async () => {
    if (!selectedSupplierFilter.value) {
        // Если поставщик не выбран, показываем все материалы
        currentMaterials.value = props.materials || [];
        orderItems.value = [];
        return;
    }
    
    loadingMaterials.value = true;
    
    try {
        const response = await axios.get(`/api/accountant/suppliers/${selectedSupplierFilter.value}/materials`);
        
        currentMaterials.value = response.data.materials.map(material => {
            const currentBalance = material.current_balance || 0;
            const minStock = material.min_stock || 0;
            
            let status = 'normal';
            if (currentBalance <= minStock) {
                status = 'critical';
            } else if (currentBalance <= minStock * 2) {
                status = 'low';
            }
            
            return {
                id: material.material_id,
                name: material.name,
                unit: material.unit,
                current_balance: currentBalance,
                min_stock: minStock,
                price_per_unit: material.price_per_unit || 0,
                supplier_price: material.price,  // Цена закупки от поставщика
                status: status,
                inOrder: false
            };
        });
        
        orderItems.value = [];
        orderStatus.value = '';
        
    } catch (error) {
        console.error('Error loading supplier materials:', error);
        showNotification('error', 'Ошибка загрузки материалов поставщика');
        currentMaterials.value = props.materials || [];
    } finally {
        loadingMaterials.value = false;
    }
};

// Методы для работы с материалами
const openAddMaterialModal = () => {
    isEditingMaterial.value = false;
    materialForm.value = {
        material_id: null,
        name: '',
        unit: '',
        current_balance: 0,
        min_stock: 0,
        price_per_unit: null
    };
    showMaterialModal.value = true;
};

const editMaterial = (material) => {
    isEditingMaterial.value = true;
    materialForm.value = {
        material_id: material.id,
        name: material.name,
        unit: material.unit,
        current_balance: material.current_balance,
        min_stock: material.min_stock,
        price_per_unit: material.price_per_unit
    };
    showMaterialModal.value = true;
};

const closeMaterialModal = () => {
    showMaterialModal.value = false;
};

const saveMaterial = async () => {
    if (!materialForm.value.name || !materialForm.value.unit) {
        showNotification('error', 'Заполните обязательные поля');
        return;
    }
    
    try {
        if (isEditingMaterial.value) {
            await axios.put(`/api/accountant/materials/${materialForm.value.material_id}`, materialForm.value);
            showNotification('success', 'Материал обновлен');
        } else {
            await axios.post('/api/accountant/materials', materialForm.value);
            showNotification('success', 'Материал добавлен');
        }
        closeMaterialModal();
        
        // Если выбран поставщик, перезагружаем его материалы
        if (selectedSupplierFilter.value) {
            await onSupplierFilterChange();
        } else {
            setTimeout(() => router.reload(), 1000);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении');
    }
};

const deleteMaterial = async (material) => {
    if (!confirm(`Удалить материал "${material.name}"?`)) return;
    
    try {
        await axios.delete(`/api/accountant/materials/${material.id}`);
        showNotification('success', 'Материал удален');
        
        if (selectedSupplierFilter.value) {
            await onSupplierFilterChange();
        } else {
            setTimeout(() => router.reload(), 1000);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при удалении');
    }
};

// Методы для закупки
const addToOrder = (material) => {
    selectedMaterial.value = material;
    const suggestedQuantity = material.min_stock - material.current_balance > 0 
        ? material.min_stock - material.current_balance + Math.ceil(material.min_stock * 0.1)
        : material.min_stock;
    quantityToOrder.value = suggestedQuantity > 0 ? suggestedQuantity : 10;
    pricePerUnit.value = material.supplier_price || material.price_per_unit || 100;
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
            price_per_unit: pricePerUnit.value,
            sale_price: selectedMaterial.value.price_per_unit
        });
    }
    showQuantityModal.value = false;
};

const removeFromOrder = (index) => {
    orderItems.value.splice(index, 1);
};

const submitOrder = async () => {
    if (orderItems.value.length === 0) return;
    
    try {
        const response = await axios.post('/api/accountant/orders/create', {
            supplier_id: selectedSupplierFilter.value,
            items: orderItems.value.map(item => ({
                material_id: item.id,
                quantity: item.quantity,
                price_per_unit: item.price_per_unit
            }))
        });
        
        orderStatus.value = response.data.message;
        showNotification('success', response.data.message);
        orderItems.value = [];
        setTimeout(() => router.reload(), 2000);
    } catch (error) {
        orderStatus.value = 'Ошибка при создании заказа';
        showNotification('error', error.response?.data?.error || 'Ошибка при создании заказа');
    }
};

// Инициализация
onMounted(() => {
    currentMaterials.value = props.materials || [];
});
</script>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>