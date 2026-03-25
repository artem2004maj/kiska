<!-- resources/js/Pages/Director/Services.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'УПРАВЛЕНИЕ УСЛУГАМИ'"
    >
        <div class="space-y-6">
            <!-- Кнопка добавления -->
            <div class="flex justify-end">
                <button @click="openAddModal" 
                        class="px-4 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить услугу
                </button>
            </div>

            <!-- Таблица услуг -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700">
                                <th class="text-left py-3 text-black dark:text-white font-medium">Название</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Категория</th>
                                <th class="text-right py-3 text-black dark:text-white font-medium">Цена (₽)</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Врачи</th>
                                <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                              </tr>
                        </thead>
                        <tbody>
                            <tr v-for="service in servicesList" :key="service.service_id" 
                                class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                <td class="py-3 text-black dark:text-white/70">{{ service.service_name }}</td>
                                <td class="py-3 text-black dark:text-white/70">{{ service.service_category }}</td>
                                <td class="py-3 text-right text-black dark:text-white/70">{{ formatPrice(service.default_price) }}</td>
                                <td class="py-3">
                                    <!-- Переключатель статуса -->
                                    <button @click="toggleServiceStatus(service)" 
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:ring-offset-2"
                                            :class="service.is_active ? 'bg-[#8b5cf6]' : 'bg-gray-300 dark:bg-gray-600'">
                                        <span class="sr-only">{{ service.is_active ? 'Активна' : 'Неактивна' }}</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                              :class="service.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                    <span class="ml-2 text-xs text-gray-500">
                                        {{ service.is_active ? 'Активна' : 'Неактивна' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="doctor in service.doctors" :key="doctor.employee_id"
                                              class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                            {{ doctor.employee_name.split(' ')[0] }}
                                        </span>
                                        <button @click="openAssignModal(service)" 
                                                class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-md hover:bg-gray-200">
                                            + Назначить
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button @click="openEditModal(service)" 
                                                class="text-[#8b5cf6] hover:underline text-sm">
                                            Редактировать
                                        </button>
                                        <button @click="deleteService(service)" 
                                                class="text-red-500 hover:underline text-sm">
                                            Удалить
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="servicesList.length === 0">
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    Нет добавленных услуг
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления/редактирования услуги -->
        <Teleport to="body">
            <div v-if="showServiceModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeServiceModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-xl font-semibold mb-4">{{ isEditing ? 'Редактировать услугу' : 'Добавить услугу' }}</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Название *</label>
                                <input type="text" v-model="serviceForm.service_name" 
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Категория *</label>
                                <input type="text" v-model="serviceForm.service_category" 
                                       placeholder="Например: Инъекционная косметология"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Цена (₽) *</label>
                                <input type="number" v-model="serviceForm.default_price" min="0" step="100"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div v-if="isEditing" class="border-t pt-4">
                                <label class="block text-sm font-medium mb-2">Статус услуги</label>
                                <div class="flex items-center gap-3">
                                    <!-- Переключатель статуса в модальном окне -->
                                    <button @click="serviceForm.is_active = !serviceForm.is_active" 
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[#8b5cf6] focus:ring-offset-2"
                                            :class="serviceForm.is_active ? 'bg-[#8b5cf6]' : 'bg-gray-300 dark:bg-gray-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                              :class="serviceForm.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                    <span class="text-sm">{{ serviceForm.is_active ? 'Активна (отображается клиентам)' : 'Неактивна (скрыта от клиентов)' }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Неактивные услуги не видны клиентам, но остаются в истории записей
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button @click="saveService" :disabled="saving"
                                    class="flex-1 px-4 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 disabled:opacity-50">
                                {{ saving ? 'Сохранение...' : (isEditing ? 'Сохранить' : 'Добавить') }}
                            </button>
                            <button @click="closeServiceModal" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно назначения врачей -->
        <Teleport to="body">
            <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeAssignModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-xl font-semibold mb-4">Назначить врачей для "{{ selectedService?.service_name }}"</h3>
                        
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <div v-for="doctor in doctors" :key="doctor.employee_id" 
                                 class="flex items-center justify-between p-3 border rounded-lg">
                                <div>
                                    <p class="font-medium">{{ doctor.employee_name }}</p>
                                    <p class="text-xs text-gray-500">Врач</p>
                                </div>
                                <button @click="toggleDoctorAssignment(doctor)"
                                        :disabled="assigning"
                                        class="px-3 py-1 rounded-md text-sm transition"
                                        :class="isDoctorAssigned(doctor) 
                                            ? 'bg-red-100 text-red-600 hover:bg-red-200' 
                                            : 'bg-green-100 text-green-600 hover:bg-green-200'">
                                    {{ isDoctorAssigned(doctor) ? 'Открепить' : 'Назначить' }}
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t">
                            <button @click="closeAssignModal" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
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
    </DirectorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';

const props = defineProps({
    director: Object,
    services: Array,
    doctors: Array,
    categories: Array
});

const servicesList = ref([]);
const showServiceModal = ref(false);
const showAssignModal = ref(false);
const isEditing = ref(false);
const selectedService = ref(null);
const saving = ref(false);
const assigning = ref(false);

const serviceForm = ref({
    service_name: '',
    service_category: '',
    default_price: 0,
    is_active: true
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

const isDoctorAssigned = (doctor) => {
    if (!selectedService.value) return false;
    return selectedService.value.doctors.some(d => d.employee_id === doctor.employee_id);
};

// Переключение статуса прямо в таблице
const toggleServiceStatus = async (service) => {
    const newStatus = !service.is_active;
    
    try {
        const response = await axios.put(`/api/director/services/${service.service_id}`, {
            service_name: service.service_name,
            service_category: service.service_category,
            default_price: service.default_price,
            is_active: newStatus
        });
        
        if (response.data.success) {
            // Обновляем статус в списке
            const index = servicesList.value.findIndex(s => s.service_id === service.service_id);
            if (index !== -1) {
                servicesList.value[index].is_active = newStatus;
            }
            showNotification('success', `Услуга ${newStatus ? 'активирована' : 'деактивирована'}`);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при изменении статуса');
    }
};

const openAddModal = () => {
    isEditing.value = false;
    serviceForm.value = {
        service_name: '',
        service_category: '',
        default_price: 0,
        is_active: true
    };
    showServiceModal.value = true;
};

const openEditModal = (service) => {
    isEditing.value = true;
    serviceForm.value = {
        service_id: service.service_id,
        service_name: service.service_name,
        service_category: service.service_category,
        default_price: service.default_price,
        is_active: service.is_active
    };
    showServiceModal.value = true;
};

const openAssignModal = (service) => {
    selectedService.value = JSON.parse(JSON.stringify(service));
    showAssignModal.value = true;
};

const closeServiceModal = () => {
    showServiceModal.value = false;
    serviceForm.value = {
        service_name: '',
        service_category: '',
        default_price: 0,
        is_active: true
    };
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedService.value = null;
};

const saveService = async () => {
    saving.value = true;
    
    try {
        let response;
        if (isEditing.value) {
            response = await axios.put(`/api/director/services/${serviceForm.value.service_id}`, {
                service_name: serviceForm.value.service_name,
                service_category: serviceForm.value.service_category,
                default_price: serviceForm.value.default_price,
                is_active: serviceForm.value.is_active
            });
        } else {
            response = await axios.post('/api/director/services', {
                service_name: serviceForm.value.service_name,
                service_category: serviceForm.value.service_category,
                default_price: serviceForm.value.default_price
            });
        }
        
        if (response.data.success) {
            showNotification('success', response.data.message);
            
            if (isEditing.value) {
                const index = servicesList.value.findIndex(s => s.service_id === response.data.service.service_id);
                if (index !== -1) {
                    servicesList.value[index] = response.data.service;
                }
            } else {
                servicesList.value.push(response.data.service);
            }
            
            closeServiceModal();
        }
    } catch (error) {
        console.error('Error saving service:', error);
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении');
    } finally {
        saving.value = false;
    }
};

const deleteService = async (service) => {
    if (!confirm(`Удалить услугу "${service.service_name}"?`)) return;
    
    try {
        const response = await axios.delete(`/api/director/services/${service.service_id}`);
        if (response.data.success) {
            showNotification('success', response.data.message);
            servicesList.value = servicesList.value.filter(s => s.service_id !== service.service_id);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при удалении');
    }
};

const toggleDoctorAssignment = async (doctor) => {
    if (!selectedService.value) return;
    
    const isAssigned = isDoctorAssigned(doctor);
    assigning.value = true;
    
    try {
        let response;
        if (isAssigned) {
            response = await axios.post('/api/director/services/detach', {
                doctor_id: doctor.employee_id,
                service_id: selectedService.value.service_id
            });
        } else {
            response = await axios.post('/api/director/services/assign', {
                doctor_id: doctor.employee_id,
                service_id: selectedService.value.service_id
            });
        }
        
        if (response.data.success) {
            if (isAssigned) {
                selectedService.value.doctors = selectedService.value.doctors.filter(
                    d => d.employee_id !== doctor.employee_id
                );
            } else {
                selectedService.value.doctors.push({
                    employee_id: doctor.employee_id,
                    employee_name: doctor.employee_name
                });
            }
            
            const index = servicesList.value.findIndex(s => s.service_id === selectedService.value.service_id);
            if (index !== -1) {
                servicesList.value[index].doctors = [...selectedService.value.doctors];
            }
            
            showNotification('success', response.data.message);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка');
    } finally {
        assigning.value = false;
    }
};

onMounted(() => {
    servicesList.value = JSON.parse(JSON.stringify(props.services));
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