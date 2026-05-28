<!-- resources/js/Pages/Admin/Warehouse.vue -->
<template>
    <AdminLayout 
        :admin="admin"
        :pageTitle="'СКЛАДСКОЙ УЧЕТ'"
        :criticalCount="criticalCount"
    >
        <div class="space-y-6">
            <!-- Основная таблица склада -->
            <div class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">СКЛАДСКОЙ УЧЕТ</h2>
                        <button @click="openAddMaterialModal" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Добавить материал
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    <!-- Фильтры -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <select v-model="selectedSupplierFilter" 
                                class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                @change="onSupplierFilterChange">
                            <option value="">Все поставщики</option>
                            <option v-for="supplier in suppliers" :key="supplier.supplier_id" :value="supplier.supplier_id">
                                {{ supplier.supplier_name }}
                            </option>
                        </select>
                        
                        <input type="text" placeholder="Поиск материалов..." 
                               v-model="searchQuery"
                               class="flex-1 min-w-[200px] px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" />
                        
                        <select v-model="statusFilter" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                            <option value="all">Все материалы</option>
                            <option value="critical">Критичные</option>
                            <option value="low">Мало</option>
                            <option value="normal">Норма</option>
                        </select>
                    </div>

                    <!-- Таблица -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Наименование</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Остаток</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Ед. изм.</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Мин. остаток</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Цена</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Статус</th>
                                    <th class="text-left py-3 text-sm font-medium text-gray-500">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="material in filteredMaterials" :key="material.id" 
                                    class="border-b border-gray-100 hover:bg-gray-50 transition">
                                    <td class="py-3 text-gray-800">{{ material.name }}</td>
                                    <td class="py-3 font-medium" :class="material.current_balance <= material.min_stock ? 'text-red-600' : 'text-gray-600'">
                                        {{ material.current_balance }}
                                    </td>
                                    <td class="py-3 text-gray-500">{{ material.unit }}</td>
                                    <td class="py-3 text-gray-500">{{ material.min_stock }}</td>
                                    <td class="py-3 text-gray-600">{{ formatPrice(material.price_per_unit) }}</td>
                                    <td class="py-3">
                                        <span class="px-2 py-1 text-xs rounded-full" 
                                              :class="{
                                                  'bg-red-100 text-red-700': material.status === 'critical',
                                                  'bg-yellow-100 text-yellow-700': material.status === 'low',
                                                  'bg-green-100 text-green-700': material.status === 'normal'
                                              }">
                                            {{ material.status === 'critical' ? 'Критично' : material.status === 'low' ? 'Мало' : 'Норма' }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <button @click="editMaterial(material)" 
                                                    class="text-blue-500 hover:text-blue-700 text-sm">
                                                ✏️ Ред.
                                            </button>
                                            <button @click="deleteMaterial(material)" 
                                                    class="text-red-500 hover:text-red-700 text-sm">
                                                🗑️ Уд.
                                            </button>
                                            <button v-if="selectedSupplierFilter" 
                                                    @click="addToOrder(material)" 
                                                    class="text-green-500 hover:text-green-700 text-sm"
                                                    :disabled="material.inOrder">
                                                {{ material.inOrder ? '📦 В заказе' : '🛒 В заказ' }}
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
                </div>
            </div>

            <!-- Блок закупки - показываем только если выбран поставщик -->
            <div v-if="selectedSupplierFilter" class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">
                        ОФОРМЛЕНИЕ ЗАКУПКИ ДЛЯ {{ getSelectedSupplierName() }}
                    </h3>
                </div>
                
                <div class="p-6">
                    <!-- Корзина заказа -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Корзина заказа</label>
                        <div class="border border-gray-200 rounded-lg">
                            <div v-if="orderItems.length === 0" class="p-4 text-center text-gray-500">
                                Корзина пуста
                            </div>
                            <div v-else>
                                <div v-for="(item, index) in orderItems" :key="index" 
                                     class="flex justify-between items-center p-3 border-b border-gray-100 last:border-0">
                                    <div>
                                        <span class="font-medium text-gray-800">{{ item.name }}</span>
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
                                <div class="p-3 bg-gray-50 flex justify-between font-medium rounded-b-lg">
                                    <span>Итого закупка:</span>
                                    <span class="text-blue-600">{{ formatPrice(orderTotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка оформления -->
                    <button @click="submitOrder" 
                            :disabled="orderItems.length === 0"
                            class="w-full px-4 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Оформить заявку на закупку
                    </button>

                    <!-- Статус заявки -->
                    <div v-if="orderStatus" class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-center text-sm">
                        {{ orderStatus }}
                    </div>
                </div>
            </div>

            <!-- Активные заказы (подтвержденные, в пути) -->
            <div v-if="activeOrders.length > 0" class="bg-white rounded-xl shadow-sm">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">ЗАКАЗЫ В ПУТИ</h3>
                </div>
                
                <div class="p-6">
                    <div class="space-y-3">
                        <div v-for="order in activeOrders" :key="order.id" 
                             class="flex justify-between items-center p-3 border rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Заказ №{{ order.number }}</p>
                                <p class="text-sm text-gray-500">Поставщик: {{ order.supplier_name }}</p>
                                <p class="text-sm text-gray-500">Дата: {{ formatDate(order.date) }}</p>
                                <div class="text-xs text-gray-400 mt-1">
                                    Состав: 
                                    <span v-for="(item, idx) in order.items" :key="idx">
                                        {{ item.material_name }} ({{ item.quantity }} {{ item.unit }}){{ idx < order.items.length - 1 ? ', ' : '' }}
                                    </span>
                                </div>
                            </div>
                            <button @click="receiveOrder(order)" 
                                    class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm">
                                Принять на склад
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления/редактирования материала -->
        <Teleport to="body">
            <div v-if="showMaterialModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeMaterialModal"></div>
                    <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">{{ isEditingMaterial ? 'Редактировать материал' : 'Добавить материал' }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Наименование *</label>
                                <input type="text" v-model="materialForm.name" 
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Единица измерения *</label>
                                <input type="text" v-model="materialForm.unit" 
                                       placeholder="шт, мл, упак, пара и т.д."
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Текущий остаток *</label>
                                    <input type="number" v-model="materialForm.current_balance" min="0"
                                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Мин. остаток *</label>
                                    <input type="number" v-model="materialForm.min_stock" min="0"
                                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Цена за единицу (₽)</label>
                                <input type="number" v-model="materialForm.price_per_unit" min="0" step="0.01"
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button @click="saveMaterial" 
                                    :disabled="!materialForm.name || !materialForm.unit"
                                    class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50">
                                {{ isEditingMaterial ? 'Сохранить' : 'Добавить' }}
                            </button>
                            <button @click="closeMaterialModal" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно выбора количества -->
        <Teleport to="body">
            <div v-if="showQuantityModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showQuantityModal = false"></div>
                    <div class="relative bg-white rounded-lg max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold mb-4">Добавить в заказ</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Материал: {{ selectedMaterial?.name }}</label>
                            <label class="block text-sm font-medium mb-1 mt-3">Количество ({{ selectedMaterial?.unit }})</label>
                            <input type="number" v-model="quantityToOrder" min="1" 
                                   class="w-full px-3 py-2 border rounded-lg" />
                            <label class="block text-sm font-medium mb-1 mt-3">Цена закупки (₽)</label>
                            <input type="number" v-model="pricePerUnit" min="0" step="1"
                                   class="w-full px-3 py-2 border rounded-lg" />
                            <div class="text-xs text-gray-500 mt-2">
                                Цена продажи: {{ formatPrice(selectedMaterial?.price_per_unit) }}
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button @click="confirmAddToOrder" 
                                    class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                Добавить
                            </button>
                            <button @click="showQuantityModal = false" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                                Отмена
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
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    admin: Object,
    materials: Array,
    suppliers: Array,
    activeOrders: Array,
    criticalCount: Number
});

// Фильтры
const searchQuery = ref('');
const statusFilter = ref('all');
const selectedSupplierFilter = ref('');
const currentMaterials = ref([]);
const orderItems = ref([]);
const orderStatus = ref('');

// Модальные окна
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

const orderTotal = computed(() => {
    return orderItems.value.reduce((sum, item) => sum + (item.quantity * item.price_per_unit), 0);
});

const getSelectedSupplierName = () => {
    const supplier = props.suppliers?.find(s => s.supplier_id === selectedSupplierFilter.value);
    return supplier ? supplier.supplier_name : '';
};

const onSupplierFilterChange = async () => {
    try {
        const response = await axios.get('/api/admin/warehouse-materials', {
            params: { supplier_id: selectedSupplierFilter.value }
        });
        currentMaterials.value = response.data;
        orderItems.value = [];
        orderStatus.value = '';
    } catch (error) {
        console.error('Error loading materials:', error);
        showNotification('error', 'Ошибка загрузки материалов');
    }
};

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
            await axios.put(`/api/admin/materials/${materialForm.value.material_id}`, materialForm.value);
            showNotification('success', 'Материал обновлен');
        } else {
            await axios.post('/api/admin/materials', materialForm.value);
            showNotification('success', 'Материал добавлен');
        }
        closeMaterialModal();
        await onSupplierFilterChange();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении');
    }
};

const deleteMaterial = async (material) => {
    if (!confirm(`Вы уверены, что хотите удалить материал "${material.name}"?`)) return;
    
    try {
        await axios.delete(`/api/admin/materials/${material.id}`);
        showNotification('success', 'Материал удален');
        await onSupplierFilterChange();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при удалении');
    }
};

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
    selectedMaterial.value.inOrder = true;
    showQuantityModal.value = false;
};

const removeFromOrder = (index) => {
    orderItems.value.splice(index, 1);
};

const submitOrder = async () => {
    if (orderItems.value.length === 0) return;
    
    try {
        const response = await axios.post('/api/admin/orders/create', {
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

const receiveOrder = async (order) => {
    if (!confirm(`Подтвердить получение заказа №${order.number}? Материалы будут добавлены на склад.`)) return;
    
    try {
        await axios.post(`/api/admin/orders/${order.id}/receive`);
        showNotification('success', 'Заказ принят на склад');
        setTimeout(() => router.reload(), 1000);
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при приемке заказа');
    }
};

onMounted(() => {
    if (props.materials) {
        currentMaterials.value = [...props.materials];
    }
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