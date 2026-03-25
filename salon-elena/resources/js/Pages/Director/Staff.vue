<!-- resources/js/Pages/Director/Staff.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'УПРАВЛЕНИЕ ПЕРСОНАЛОМ'"
    >
        <div class="space-y-6">
            <!-- Фильтры и кнопка добавления -->
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div class="flex gap-2">
                    <button 
                        v-for="filter in roleFilters" 
                        :key="filter.value"
                        @click="selectedRole = filter.value"
                        class="px-4 py-2 rounded-md transition"
                        :class="selectedRole === filter.value 
                            ? 'bg-[#8b5cf6] text-white' 
                            : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200'"
                    >
                        {{ filter.label }}
                        <span v-if="filter.count > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">
                            {{ filter.count }}
                        </span>
                    </button>
                </div>
                
                <button @click="openAddModal" 
                        class="px-4 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Добавить сотрудника
                </button>
            </div>

            <!-- Таблица сотрудников -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow overflow-hidden">
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#8b5cf6]"></div>
                    <p class="mt-2 text-gray-500">Загрузка...</p>
                </div>

                <div v-else-if="filteredEmployees.length === 0" class="text-center py-12 text-gray-500">
                    Нет сотрудников в этой категории
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800">
                                <th class="text-left py-3 px-4">Сотрудник</th>
                                <th class="text-left py-3 px-4">Должность</th>
                                <th class="text-left py-3 px-4">Email</th>
                                <th class="text-left py-3 px-4">Телефон</th>
                                <th class="text-right py-3 px-4">Ставка (₽/час)</th>
                                <th class="text-center py-3 px-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="employee in filteredEmployees" :key="employee.employee_id" 
                                class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#8b5cf6]/10 flex items-center justify-center overflow-hidden">
                                            <img v-if="employee.photo_url" 
                                                 :src="employee.photo_url" 
                                                 :alt="employee.employee_name"
                                                 class="w-full h-full object-cover">
                                            <span v-else class="text-sm font-medium text-[#8b5cf6]">
                                                {{ getInitials(employee.employee_name) }}
                                            </span>
                                        </div>
                                        <span class="font-medium">{{ employee.employee_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs rounded-full"
                                          :class="{
                                              'bg-blue-100 text-blue-800': employee.role === 'doctor',
                                              'bg-green-100 text-green-800': employee.role === 'admin',
                                              'bg-purple-100 text-purple-800': employee.role === 'accountant',
                                              'bg-orange-100 text-orange-800': employee.role === 'director'
                                          }">
                                        {{ employee.role_text }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600">{{ employee.email }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ employee.employee_phone || '—' }}</td>
                                <td class="py-3 px-4 text-right">
                                    {{ employee.role === 'doctor' ? formatPrice(employee.hourly_rate) : '—' }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openEditModal(employee)" 
                                                class="text-[#8b5cf6] hover:underline text-sm"
                                                title="Редактировать">
                                            ✏️
                                        </button>
                                        <button v-if="employee.role === 'doctor'" 
                                                @click="openServicesModal(employee)" 
                                                class="text-green-600 hover:underline text-sm"
                                                title="Услуги">
                                            💊
                                        </button>
                                        <button @click="openScheduleModal(employee)" 
                                                class="text-blue-600 hover:underline text-sm"
                                                title="График работы">
                                            📅
                                        </button>
                                        <button v-if="employee.role !== 'director'" 
                                                @click="deleteEmployee(employee)" 
                                                class="text-red-500 hover:underline text-sm"
                                                title="Удалить">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Модальное окно добавления/редактирования сотрудника -->
        <Teleport to="body">
            <div v-if="showEmployeeModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeEmployeeModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-xl font-semibold mb-4">
                            {{ isEditing ? 'Редактировать сотрудника' : 'Добавить сотрудника' }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">ФИО *</label>
                                <input type="text" v-model="employeeForm.employee_name" required
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Должность *</label>
                                <select v-model="employeeForm.role" 
                                        class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                                    <option value="doctor">Врач</option>
                                    <option value="admin">Администратор</option>
                                    <option value="accountant">Бухгалтер</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Email *</label>
                                <input type="email" v-model="employeeForm.email" required
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Телефон</label>
                                <input type="tel" v-model="employeeForm.employee_phone" 
                                       placeholder="+7 999 999-99-99"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div v-if="employeeForm.role === 'doctor'">
                                <label class="block text-sm font-medium mb-1">Почасовая ставка (₽)</label>
                                <input type="number" v-model="employeeForm.hourly_rate" min="0" step="50"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                            </div>
                            
                            <div v-if="!isEditing">
                                <label class="block text-sm font-medium mb-1">Логин *</label>
                                <input type="text" v-model="employeeForm.login" required
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Уникальное имя для входа в систему</p>
                            </div>
                            
                            <div v-if="!isEditing" class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-md">
                                <p class="text-sm text-yellow-800 dark:text-yellow-300">
                                    🔐 Временный пароль: <strong>password123</strong>
                                </p>
                                <p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">
                                    Сотрудник сможет сменить пароль после первого входа
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 mt-6">
                            <button @click="saveEmployee" :disabled="saving"
                                    class="flex-1 px-4 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 disabled:opacity-50">
                                {{ saving ? 'Сохранение...' : (isEditing ? 'Сохранить' : 'Добавить') }}
                            </button>
                            <button @click="closeEmployeeModal" 
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно назначения услуг врачу -->
        <Teleport to="body">
            <div v-if="showServicesModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeServicesModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">
                                Услуги врача: {{ selectedEmployee?.employee_name }}
                            </h3>
                            <button @click="closeServicesModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                <div v-for="service in allServices" :key="service.service_id" 
                                     class="flex items-center justify-between p-3 border rounded-lg">
                                    <div>
                                        <p class="font-medium">{{ service.service_name }}</p>
                                        <p class="text-xs text-gray-500">{{ service.service_category }}</p>
                                    </div>
                                    <button @click="toggleServiceAssignment(service)"
                                            :disabled="assigning"
                                            class="px-3 py-1 rounded-md text-sm transition"
                                            :class="isServiceAssigned(service) 
                                                ? 'bg-red-100 text-red-600 hover:bg-red-200' 
                                                : 'bg-green-100 text-green-600 hover:bg-green-200'">
                                        {{ isServiceAssigned(service) ? 'Открепить' : 'Назначить' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t">
                            <button @click="closeServicesModal" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно настройки графика работы -->
        <Teleport to="body">
            <div v-if="showScheduleModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeScheduleModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">
                                График работы: {{ selectedEmployee?.employee_name }}
                            </h3>
                            <button @click="closeScheduleModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-6" v-if="scheduleData">
                            <div class="space-y-4">
                                <div v-for="day in scheduleData.schedule" :key="day.day" 
                                     class="border rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" v-model="day.working" 
                                                   class="w-4 h-4 rounded border-gray-300 text-[#8b5cf6] focus:ring-[#8b5cf6]">
                                            <span class="font-medium">{{ day.day_name }}</span>
                                        </label>
                                    </div>
                                    
                                    <div v-if="day.working" class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Начало работы</label>
                                            <input type="time" v-model="day.start_time" 
                                                   class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Конец работы</label>
                                            <input type="time" v-model="day.end_time" 
                                                   class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-sm font-medium mb-1">Длительность слота (мин)</label>
                                            <select v-model="day.slot_duration" 
                                                    class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#8b5cf6]">
                                                <option :value="15">15 минут</option>
                                                <option :value="30">30 минут</option>
                                                <option :value="45">45 минут</option>
                                                <option :value="60">60 минут</option>
                                                <option :value="90">90 минут</option>
                                                <option :value="120">120 минут</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t flex justify-end gap-3">
                            <button @click="saveSchedule" :disabled="savingSchedule"
                                    class="px-4 py-2 bg-[#8b5cf6] text-white rounded-md hover:bg-[#8b5cf6]/90 disabled:opacity-50">
                                {{ savingSchedule ? 'Сохранение...' : 'Сохранить расписание' }}
                            </button>
                            <button @click="closeScheduleModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
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
    </DirectorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';

const props = defineProps({
    director: Object,
    employees: Array,
    services: Array
});

const employeesList = ref([]);
const allServices = ref([]);
const loading = ref(false);
const saving = ref(false);
const assigning = ref(false);
const savingSchedule = ref(false);
const selectedRole = ref('all');
const showEmployeeModal = ref(false);
const showServicesModal = ref(false);
const showScheduleModal = ref(false);
const isEditing = ref(false);
const selectedEmployee = ref(null);
const scheduleData = ref(null);

const employeeForm = ref({
    employee_id: null,
    employee_name: '',
    role: 'doctor',
    email: '',
    employee_phone: '',
    hourly_rate: null,
    login: ''
});

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

const roleFilters = computed(() => {
    const counts = {
        all: employeesList.value.length,
        doctor: employeesList.value.filter(e => e.role === 'doctor').length,
        admin: employeesList.value.filter(e => e.role === 'admin').length,
        accountant: employeesList.value.filter(e => e.role === 'accountant').length
    };
    
    return [
        { value: 'all', label: 'Все сотрудники', count: counts.all },
        { value: 'doctor', label: 'Врачи', count: counts.doctor },
        { value: 'admin', label: 'Администраторы', count: counts.admin },
        { value: 'accountant', label: 'Бухгалтеры', count: counts.accountant }
    ];
});

const filteredEmployees = computed(() => {
    if (selectedRole.value === 'all') return employeesList.value;
    return employeesList.value.filter(e => e.role === selectedRole.value);
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

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const isServiceAssigned = (service) => {
    if (!selectedEmployee.value) return false;
    return selectedEmployee.value.services?.some(s => s.service_id === service.service_id);
};

const loadStaff = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/director/staff/list', {
            params: { role: selectedRole.value }
        });
        employeesList.value = response.data.employees;
    } catch (error) {
        console.error('Error loading staff:', error);
        showNotification('error', 'Ошибка загрузки сотрудников');
    } finally {
        loading.value = false;
    }
};

const loadServices = async () => {
    try {
        const response = await axios.get('/api/director/services/list');
        allServices.value = response.data.services;
    } catch (error) {
        console.error('Error loading services:', error);
    }
};

const openAddModal = () => {
    isEditing.value = false;
    employeeForm.value = {
        employee_id: null,
        employee_name: '',
        role: 'doctor',
        email: '',
        employee_phone: '',
        hourly_rate: null,
        login: ''
    };
    showEmployeeModal.value = true;
};

const openEditModal = (employee) => {
    isEditing.value = true;
    employeeForm.value = {
        employee_id: employee.employee_id,
        employee_name: employee.employee_name,
        role: employee.role,
        email: employee.email,
        employee_phone: employee.employee_phone || '',
        hourly_rate: employee.hourly_rate,
        login: ''
    };
    showEmployeeModal.value = true;
};

const openServicesModal = async (employee) => {
    selectedEmployee.value = employee;
    // Загружаем услуги врача
    try {
        const response = await axios.get(`/api/director/employees/${employee.employee_id}/services`);
        selectedEmployee.value.services = response.data.services;
        showServicesModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка загрузки услуг');
    }
};

const openScheduleModal = async (employee) => {
    selectedEmployee.value = employee;
    try {
        const response = await axios.get(`/api/director/employees/${employee.employee_id}/schedule`);
        scheduleData.value = response.data;
        showScheduleModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка загрузки расписания');
    }
};

const closeEmployeeModal = () => {
    showEmployeeModal.value = false;
    employeeForm.value = {
        employee_id: null,
        employee_name: '',
        role: 'doctor',
        email: '',
        employee_phone: '',
        hourly_rate: null,
        login: ''
    };
};

const closeServicesModal = () => {
    showServicesModal.value = false;
    selectedEmployee.value = null;
};

const closeScheduleModal = () => {
    showScheduleModal.value = false;
    scheduleData.value = null;
    selectedEmployee.value = null;
};

const saveEmployee = async () => {
    saving.value = true;
    
    try {
        let response;
        if (isEditing.value) {
            response = await axios.put(`/api/director/employees/${employeeForm.value.employee_id}`, employeeForm.value);
        } else {
            response = await axios.post('/api/director/employees', employeeForm.value);
        }
        
        if (response.data.success) {
            showNotification('success', response.data.message);
            closeEmployeeModal();
            await loadStaff();
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении');
    } finally {
        saving.value = false;
    }
};

const toggleServiceAssignment = async (service) => {
    if (!selectedEmployee.value) return;
    
    const isAssigned = isServiceAssigned(service);
    assigning.value = true;
    
    try {
        let response;
        if (isAssigned) {
            response = await axios.post('/api/director/services/detach', {
                doctor_id: selectedEmployee.value.employee_id,
                service_id: service.service_id
            });
        } else {
            response = await axios.post('/api/director/services/assign', {
                doctor_id: selectedEmployee.value.employee_id,
                service_id: service.service_id
            });
        }
        
        if (response.data.success) {
            if (isAssigned) {
                selectedEmployee.value.services = selectedEmployee.value.services.filter(
                    s => s.service_id !== service.service_id
                );
            } else {
                selectedEmployee.value.services.push(service);
            }
            showNotification('success', response.data.message);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка');
    } finally {
        assigning.value = false;
    }
};

const saveSchedule = async () => {
    if (!scheduleData.value) return;
    
    savingSchedule.value = true;
    
    try {
        const response = await axios.post(`/api/director/employees/${scheduleData.value.employee.employee_id}/schedule`, {
            schedule: scheduleData.value.schedule
        });
        
        if (response.data.success) {
            showNotification('success', 'Расписание сохранено');
            closeScheduleModal();
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при сохранении расписания');
    } finally {
        savingSchedule.value = false;
    }
};

const deleteEmployee = async (employee) => {
    if (!confirm(`Удалить сотрудника "${employee.employee_name}"?`)) return;
    
    try {
        const response = await axios.delete(`/api/director/employees/${employee.employee_id}`);
        if (response.data.success) {
            showNotification('success', response.data.message);
            await loadStaff();
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при удалении');
    }
};

onMounted(() => {
    loadStaff();
    loadServices();
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