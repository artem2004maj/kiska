<!-- resources/js/Pages/Accountant/Suppliers.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ПОСТАВЩИКИ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-black dark:text-white">СПИСОК ПОСТАВЩИКОВ</h2>
                <button @click="openAddModal" 
                        class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить поставщика
                </button>
            </div>

            <!-- Поиск -->
            <div class="mb-6">
                <input type="text" v-model="searchQuery" 
                       placeholder="Поиск по названию, контактному лицу или телефону..."
                       class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
            </div>

            <!-- Таблица поставщиков -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Название</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Контактное лицо</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Телефон</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">ИНН</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Материалы</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Ответственный</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                         </tr>
                    </thead>
                    <tbody>
                        <tr v-for="supplier in filteredSuppliers" :key="supplier.supplier_id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70 font-medium">{{ supplier.supplier_name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ supplier.contact_person || '—' }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ supplier.phone || '—' }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ supplier.inn || '—' }}</td>
                            <td class="py-3">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="material in supplier.materials" :key="material.material_id"
                                          class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">
                                        {{ material.name }}
                                    </span>
                                    <span v-if="!supplier.materials?.length" class="text-gray-400 text-xs">
                                        Нет материалов
                                    </span>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    {{ supplier.accountant_fio || 'Не назначен' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="flex gap-2">
                                    <button @click="editSupplier(supplier)" 
                                            class="text-[#3b82f6] hover:underline text-sm">
                                        Редактировать
                                    </button>
                                    <button @click="openMaterialsModal(supplier)" 
                                            class="text-[#10b981] hover:underline text-sm">
                                        Материалы
                                    </button>
                                    <button @click="deleteSupplier(supplier.supplier_id)" 
                                            class="text-red-500 hover:underline text-sm">
                                        Удалить
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredSuppliers.length === 0">
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                Поставщики не найдены
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Модальное окно добавления/редактирования поставщика -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">{{ isEditing ? 'Редактировать поставщика' : 'Добавить поставщика' }}</h3>
                            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <!-- Основные поля -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Название поставщика <span class="text-red-500">*</span></label>
                                <input type="text" v-model="form.supplier_name" 
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                    placeholder="ООО 'МедСнаб'" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Контактное лицо</label>
                                <input type="text" v-model="form.contact_person" 
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                    placeholder="Иванов Иван Иванович" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Телефон</label>
                                    <input type="tel" v-model="form.phone" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                        placeholder="+7 (999) 123-45-67" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Email</label>
                                    <input type="email" v-model="form.email" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                        placeholder="supplier@example.com" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Адрес</label>
                                <textarea v-model="form.address" rows="2" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                        placeholder="г. Москва, ул. Примерная, д. 1"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Примечания</label>
                                <textarea v-model="form.notes" rows="2" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                        placeholder="Дополнительная информация..."></textarea>
                            </div>
                            
                            <!-- Разделитель -->
                            <div class="border-t border-gray-200 dark:border-zinc-700 pt-4">
                                <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Реквизиты</h4>
                            </div>
                            
                            <!-- Реквизиты -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">ИНН</label>
                                    <input type="text" v-model="form.inn" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Директор</label>
                                    <input type="text" v-model="form.director_fio" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Банк</label>
                                <input type="text" v-model="form.bank_name" 
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">БИК</label>
                                    <input type="text" v-model="form.bic" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Расчетный счет</label>
                                    <input type="text" v-model="form.payment_account" 
                                        class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t flex justify-end gap-3">
                            <button @click="closeModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                            <button @click="saveSupplier" 
                                    :disabled="loading"
                                    class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 disabled:opacity-50">
                                {{ loading ? 'Сохранение...' : (isEditing ? 'Сохранить' : 'Добавить') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно управления материалами поставщика -->
        <Teleport to="body">
            <div v-if="showMaterialsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeMaterialsModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Материалы поставщика</h3>
                            <button @click="closeMaterialsModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedSupplier" class="p-6">
                            <div class="mb-6">
                                <h4 class="font-semibold text-lg">{{ selectedSupplier.supplier_name }}</h4>
                                <p class="text-sm text-gray-500">Добавьте материалы, которые поставляет этот поставщик</p>
                            </div>
                            
                            <!-- Форма добавления материала - УБРАНО поле delivery_days -->
                            <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg mb-6">
                                <h5 class="font-medium mb-3">Добавить материал</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <select v-model="newMaterial.material_id" class="px-3 py-2 border rounded-md">
                                        <option value="">Выберите материал</option>
                                        <option v-for="material in availableMaterials" :key="material.material_id" :value="material.material_id">
                                            {{ material.name }} ({{ material.unit }})
                                        </option>
                                    </select>
                                    <input type="number" v-model="newMaterial.price" placeholder="Цена за ед. (₽)" 
                                        class="px-3 py-2 border rounded-md" />
                                </div>
                                <button @click="addMaterialToSupplier" 
                                        :disabled="!newMaterial.material_id"
                                        class="mt-3 px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 disabled:opacity-50">
                                    Добавить
                                </button>
                            </div>
                            
                            <!-- Список материалов поставщика - с возможностью редактирования цены -->
                            <div>
                                <h5 class="font-medium mb-3">Список материалов</h5>
                                <div class="space-y-2">
                                    <div v-for="material in selectedSupplier.materials" :key="material.material_id"
                                        class="flex justify-between items-center p-3 border rounded-lg">
                                        <div class="flex-1">
                                            <p class="font-medium">{{ material.name }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-sm text-gray-500">
                                                    Цена: 
                                                    <span v-if="editingPrice.materialId !== material.material_id" class="font-mono">
                                                        {{ formatPrice(material.price) }}
                                                    </span>
                                                    <div v-else class="inline-flex items-center gap-2">
                                                        <input 
                                                            type="number" 
                                                            v-model.number="editingPrice.price" 
                                                            class="w-32 px-2 py-1 text-sm border rounded-md"
                                                            step="0.01"
                                                            min="0"
                                                            @keyup.enter="savePrice(material.material_id)"
                                                        />
                                                        <button @click="savePrice(material.material_id)" 
                                                                class="text-green-600 hover:text-green-800 text-sm">
                                                            Сохранить
                                                        </button>
                                                        <button @click="cancelEditPrice" 
                                                                class="text-gray-500 hover:text-gray-700 text-sm">
                                                            Отмена
                                                        </button>
                                                    </div>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button v-if="editingPrice.materialId !== material.material_id" 
                                                    @click="startEditPrice(material)" 
                                                    class="text-[#3b82f6] hover:text-[#3b82f6]/80 text-sm">
                                                Редактировать цену
                                            </button>
                                            <button @click="removeMaterialFromSupplier(material.material_id)" 
                                                    class="text-red-500 hover:text-red-700 text-sm">
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                    <div v-if="selectedSupplier.materials?.length === 0" class="text-center py-4 text-gray-500">
                                        Нет добавленных материалов
                                    </div>
                                </div>
                            </div>
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
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    suppliers: Array,
    allMaterials: Array,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

const searchQuery = ref('');
const showModal = ref(false);
const showMaterialsModal = ref(false);
const isEditing = ref(false);
const loading = ref(false);
const selectedSupplier = ref(null);
const newMaterial = ref({
    material_id: '',
    price: '',
});
const editingPrice = ref({
    materialId: null,
    price: null,
    originalPrice: null
});

const form = ref({
    supplier_id: null,
    supplier_name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    notes: '',
    inn: '',
    director_fio: '',
    accountant_fio: '',
    bank_name: '',
    bic: '',
    payment_account: ''
});

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

const availableMaterials = computed(() => {
    if (!selectedSupplier.value) return props.allMaterials || [];
    const existingIds = selectedSupplier.value.materials?.map(m => m.material_id) || [];
    return (props.allMaterials || []).filter(m => !existingIds.includes(m.material_id));
});

const filteredSuppliers = computed(() => {
    if (!searchQuery.value) return props.suppliers || [];
    const query = searchQuery.value.toLowerCase();
    return (props.suppliers || []).filter(s => 
        s.supplier_name.toLowerCase().includes(query) ||
        (s.contact_person && s.contact_person.toLowerCase().includes(query)) ||
        (s.phone && s.phone.includes(query))
    );
});

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const showNotification = (type, message) => {
    notification.value = { show: true, type, message };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

// Методы для поставщиков
const openAddModal = () => {
    isEditing.value = false;
    form.value = {
        supplier_id: null,
        supplier_name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        notes: '',
        inn: '',
        director_fio: '',
        accountant_fio: '',
        bank_name: '',
        bic: '',
        payment_account: ''
    };
    showModal.value = true;
};

const editSupplier = (supplier) => {
    isEditing.value = true;
    form.value = {
        supplier_id: supplier.supplier_id,
        supplier_name: supplier.supplier_name,
        contact_person: supplier.contact_person || '',
        phone: supplier.phone || '',
        email: supplier.email || '',
        address: supplier.address || '',
        notes: supplier.notes || '',
        inn: supplier.inn || '',
        director_fio: supplier.director_fio || '',
        accountant_fio: supplier.accountant_fio || '',
        bank_name: supplier.bank_name || '',
        bic: supplier.bic || '',
        payment_account: supplier.payment_account || ''
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        supplier_id: null,
        supplier_name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        notes: '',
        inn: '',
        director_fio: '',
        accountant_fio: '',
        bank_name: '',
        bic: '',
        payment_account: '',
        delivery_days: ''
    };
};

const saveSupplier = async () => {
    if (!form.value.supplier_name.trim()) {
        showNotification('error', 'Введите название поставщика');
        return;
    }
    
    loading.value = true;
    
    try {
        if (isEditing.value) {
            await axios.put(`/api/accountant/suppliers/${form.value.supplier_id}`, form.value);
            showNotification('success', 'Данные поставщика обновлены');
        } else {
            await axios.post('/api/accountant/suppliers', form.value);
            showNotification('success', 'Поставщик добавлен');
        }
        closeModal();
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении');
    } finally {
        loading.value = false;
    }
};

const deleteSupplier = async (id) => {
    if (!confirm('Вы уверены, что хотите удалить этого поставщика?')) return;
    
    try {
        await axios.delete(`/api/accountant/suppliers/${id}`);
        showNotification('success', 'Поставщик удален');
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при удалении');
    }
};

// Методы для управления материалами поставщика
const openMaterialsModal = (supplier) => {
    selectedSupplier.value = supplier;
    newMaterial.value = { material_id: '', price: '' };
    showMaterialsModal.value = true;
};

const closeMaterialsModal = () => {
    showMaterialsModal.value = false;
    selectedSupplier.value = null;
};

const addMaterialToSupplier = async () => {
    if (!newMaterial.value.material_id) return;
    
    try {
        // Исправленный URL
        await axios.post(`/api/accountant/suppliers/${selectedSupplier.value.supplier_id}/materials`, {
            material_id: newMaterial.value.material_id,
            price: newMaterial.value.price
        });
        
        showNotification('success', 'Материал добавлен');
        
        // Обновляем список материалов поставщика
        const response = await axios.get(`/api/accountant/suppliers/${selectedSupplier.value.supplier_id}/materials`);
        selectedSupplier.value.materials = response.data.materials;
        
        newMaterial.value = { material_id: '', price: '' };
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', error.response?.data?.error || 'Ошибка при добавлении материала');
    }
};
// Добавьте методы для редактирования цены
const startEditPrice = (material) => {
    editingPrice.value = {
        materialId: material.material_id,
        price: material.price,
        originalPrice: material.price
    };
};

const cancelEditPrice = () => {
    editingPrice.value = {
        materialId: null,
        price: null,
        originalPrice: null
    };
};

const savePrice = async (materialId) => {
    if (!editingPrice.value.price || editingPrice.value.price < 0) {
        showNotification('error', 'Введите корректную цену');
        return;
    }
    
    try {
        await axios.put(
            `/api/accountant/suppliers/${selectedSupplier.value.supplier_id}/materials/${materialId}/price`,
            { price: editingPrice.value.price }
        );
        
        // Обновляем цену в локальном массиве
        const material = selectedSupplier.value.materials.find(m => m.material_id === materialId);
        if (material) {
            material.price = editingPrice.value.price;
        }
        
        showNotification('success', 'Цена материала обновлена');
        cancelEditPrice();
        
    } catch (error) {
        console.error('Error updating price:', error);
        showNotification('error', error.response?.data?.error || 'Ошибка при обновлении цены');
        // Возвращаем старую цену
        editingPrice.value.price = editingPrice.value.originalPrice;
    }
};

const removeMaterialFromSupplier = async (materialId) => {
    if (!confirm('Удалить этот материал из списка поставщика?')) return;
    
    try {
        await axios.delete(`/api/accountant/suppliers/${selectedSupplier.value.supplier_id}/materials/${materialId}`);
        
        showNotification('success', 'Материал удален');
        
        // Обновляем список материалов поставщика
        selectedSupplier.value.materials = selectedSupplier.value.materials.filter(m => m.material_id !== materialId);
    } catch (error) {
        showNotification('error', 'Ошибка при удалении материала');
    }
};
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