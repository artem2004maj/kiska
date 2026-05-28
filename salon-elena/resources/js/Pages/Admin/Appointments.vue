<!-- resources/js/Pages/Admin/Appointments.vue -->
<template>
    <AdminLayout 
        :admin="admin"
        :pageTitle="'ЗАПИСЬ КЛИЕНТОВ'"
        :criticalCount="0"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Левая колонка - Форма записи -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm sticky top-6">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">Новая запись</h2>
                        <p class="text-sm text-gray-500 mt-1">Заполните все поля</p>
                    </div>
                    
                    <form @submit.prevent="createAppointment" class="p-6 space-y-5">
                        <!-- Поиск клиента -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Клиент <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       v-model="clientSearch"
                                       @input="searchClients"
                                       @focus="showClientDropdown = true"
                                       placeholder="Поиск по имени, телефону или email..."
                                       class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <div v-if="showClientDropdown && clientSearchResults.length > 0" 
                                     class="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <div v-for="client in clientSearchResults" :key="client.client_id"
                                         @click="selectClient(client)"
                                         class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b last:border-0">
                                        <p class="font-medium text-gray-800">{{ client.client_name }}</p>
                                        <p class="text-xs text-gray-500">{{ client.phone }} | {{ client.email }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="selectedClient" class="mt-1 text-sm text-green-600">
                                Выбран: {{ selectedClient.client_name }}
                            </p>
                        </div>
                        
                        <!-- Или создать нового клиента -->
                        <div class="border-t pt-4">
                            <button type="button" @click="showNewClientForm = !showNewClientForm"
                                    class="text-sm text-blue-500 hover:text-blue-600">
                                + Создать нового клиента
                            </button>
                        </div>
                        
                        <!-- Форма нового клиента -->
                        <div v-if="showNewClientForm" class="space-y-4 p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-medium text-gray-700">Новый клиент</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ФИО *</label>
                                <input type="text" v-model="newClient.name" 
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
                                <input type="tel" v-model="newClient.phone" 
                                       placeholder="+7 999 123-45-67"
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" v-model="newClient.email" 
                                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <button type="button" @click="createNewClient" :disabled="creatingClient"
                                    class="w-full py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                                {{ creatingClient ? 'Создание...' : 'Создать клиента' }}
                            </button>
                        </div>
                        
                        <!-- Врач -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Врач <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.doctor_id" required
                                    @change="onDoctorChange"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите врача</option>
                                <option v-for="doctor in doctors" :key="doctor.employee_id" :value="doctor.employee_id">
                                    {{ doctor.employee_name }}
                                </option>
                            </select>
                        </div>

                        <!-- Услуга -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Услуга <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.service_id" required
                                    @change="onServiceChange"
                                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Выберите услугу</option>
                                <option v-for="service in filteredServices" :key="service.service_id" :value="service.service_id">
                                    {{ service.service_name }} - {{ formatPrice(service.default_price) }}
                                </option>
                            </select>
                            <p v-if="form.doctor_id && filteredServices.length === 0" class="mt-1 text-sm text-red-500">
                                У этого врача нет закрепленных услуг
                            </p>
                        </div>
                        
                        <!-- Дата -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Дата <span class="text-red-500">*</span>
                            </label>
                            <input type="date" v-model="form.date" 
                                   :min="minDate"
                                   @change="loadAvailableSlots"
                                   class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500" />
                        </div>
                        
                        <!-- Время -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Время <span class="text-red-500">*</span>
                            </label>
                            <div v-if="loadingSlots" class="text-center py-4">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                                <p class="text-xs text-gray-500 mt-1">Загрузка...</p>
                            </div>
                            <div v-else-if="availableSlots.length === 0 && form.date && form.doctor_id" 
                                 class="text-center py-4 text-gray-500 text-sm">
                                Нет свободных слотов на выбранную дату
                            </div>
                            <div v-else class="grid grid-cols-3 gap-2">
                                <button v-for="slot in availableSlots" :key="slot"
                                        type="button"
                                        @click="form.time = slot"
                                        class="py-2 text-sm rounded-lg border transition"
                                        :class="form.time === slot 
                                            ? 'bg-blue-500 text-white border-blue-500' 
                                            : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-blue-50'">
                                    {{ slot }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Примечание -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Примечание
                            </label>
                            <textarea v-model="form.notes" rows="2"
                                      class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500"
                                      placeholder="Дополнительная информация..."></textarea>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="!canSubmit || submitting"
                                    class="flex-1 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition disabled:opacity-50">
                                {{ submitting ? 'Сохранение...' : 'Записать клиента' }}
                            </button>
                            <button type="button" @click="resetForm"
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Сбросить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Правая колонка - Список записей -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800">Активные записи</h2>
                        <p class="text-sm text-gray-500 mt-1">Ближайшие записи клиентов</p>
                    </div>
                    
                    <div class="p-6">
                        <!-- Фильтр по дате -->
                        <div class="mb-6">
                            <select v-model="filterDate" class="px-3 py-2 border rounded-lg text-sm">
                                <option value="today">Сегодня</option>
                                <option value="tomorrow">Завтра</option>
                                <option value="week">Эта неделя</option>
                                <option value="all">Все записи</option>
                            </select>
                        </div>
                        
                        <div v-if="loadingAppointments" class="text-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                        </div>
                        
                        <div v-else-if="filteredAppointments.length === 0" class="text-center py-12 text-gray-500">
                            Нет записей
                        </div>
                        
                        <div v-else class="space-y-3">
                            <div v-for="appointment in filteredAppointments" :key="appointment.id" 
                                 class="flex justify-between items-center p-4 border rounded-lg hover:bg-gray-50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-full bg-blue-100 flex flex-col items-center justify-center">
                                        <span class="text-blue-600 font-bold">{{ appointment.time }}</span>
                                        <span class="text-xs text-blue-500">{{ formatDay(appointment.date) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ appointment.client_name }}</p>
                                        <p class="text-sm text-gray-500">👨‍⚕️ {{ appointment.doctor_name }}</p>
                                        <p class="text-xs text-gray-400">💅 {{ appointment.service_name }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="cancelAppointment(appointment.id)" 
                                            class="px-3 py-1 text-sm text-red-500 hover:bg-red-50 rounded-lg transition"
                                            title="Отменить запись">
                                        Отменить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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

<!-- resources/js/Pages/Admin/Appointments.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    admin: Object,
    doctors: Array,
    services: Array,
    appointments: Array
});

// Состояния
const submitting = ref(false);
const loadingSlots = ref(false);
const loadingAppointments = ref(false);
const showClientDropdown = ref(false);
const showNewClientForm = ref(false);
const creatingClient = ref(false);
const filterDate = ref('today');

const clientSearch = ref('');
const clientSearchResults = ref([]);
const selectedClient = ref(null);
const availableSlots = ref([]);

const form = ref({
    client_id: null,
    doctor_id: '',
    service_id: '',
    date: '',
    time: '',
    notes: ''
});

const newClient = ref({
    name: '',
    phone: '',
    email: ''
});

const appointmentsList = ref([]);
const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

// Вычисляемые свойства
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

// ФИЛЬТРОВАННЫЕ УСЛУГИ - только те, которые закреплены за выбранным врачом
const filteredServices = computed(() => {
    if (!form.value.doctor_id) return [];
    
    // Находим выбранного врача
    const selectedDoctor = props.doctors.find(d => d.employee_id === form.value.doctor_id);
    
    if (!selectedDoctor || !selectedDoctor.services) {
        return [];
    }
    
    // Возвращаем только услуги, закрепленные за врачом
    return props.services.filter(service => 
        selectedDoctor.services.some(s => s.service_id === service.service_id)
    );
});

const canSubmit = computed(() => {
    return form.value.client_id && 
           form.value.doctor_id && 
           form.value.service_id && 
           form.value.date && 
           form.value.time;
});

const filteredAppointments = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    const weekEnd = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0];
    
    return appointmentsList.value.filter(a => {
        const appDate = new Date(a.date).toISOString().split('T')[0];
        if (filterDate.value === 'today') return appDate === today;
        if (filterDate.value === 'tomorrow') return appDate === tomorrow;
        if (filterDate.value === 'week') return appDate >= today && appDate <= weekEnd;
        return true;
    });
});

// Методы
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

const formatDay = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', { weekday: 'short', day: 'numeric' });
};

const searchClients = async () => {
    if (clientSearch.value.length < 2) {
        clientSearchResults.value = [];
        return;
    }
    
    try {
        const response = await axios.get('/api/admin/clients/search', {
            params: { query: clientSearch.value }
        });
        clientSearchResults.value = response.data.clients;
    } catch (error) {
        console.error('Error searching clients:', error);
    }
};

const selectClient = (client) => {
    selectedClient.value = client;
    form.value.client_id = client.client_id;
    clientSearch.value = client.client_name;
    showClientDropdown.value = false;
    showNewClientForm.value = false;
};

const createNewClient = async () => {
    if (!newClient.value.name || !newClient.value.phone) {
        showNotification('error', 'Заполните имя и телефон');
        return;
    }
    
    creatingClient.value = true;
    try {
        const response = await axios.post('/api/admin/clients', newClient.value);
        if (response.data.success) {
            selectedClient.value = response.data.client;
            form.value.client_id = response.data.client.client_id;
            clientSearch.value = response.data.client.client_name;
            showNewClientForm.value = false;
            newClient.value = { name: '', phone: '', email: '' };
            showNotification('success', `Клиент создан. Временный пароль: ${response.data.temp_password}`);
        }
    } catch (error) {
        showNotification('error', error.response?.data?.message || 'Ошибка при создании клиента');
    } finally {
        creatingClient.value = false;
    }
};

// ПРИ ВЫБОРЕ ВРАЧА - сбрасываем выбранную услугу и загружаем слоты
const onDoctorChange = () => {
    // Сбрасываем услугу, так как она может быть недоступна у нового врача
    form.value.service_id = '';
    availableSlots.value = [];
    
    // Если есть дата, загружаем слоты
    if (form.value.date) {
        loadAvailableSlots();
    }
};

// ПРИ ВЫБОРЕ УСЛУГИ - загружаем слоты
const onServiceChange = () => {
    if (form.value.date && form.value.doctor_id) {
        loadAvailableSlots();
    }
};

const loadAvailableSlots = async () => {
    if (!form.value.date || !form.value.doctor_id) return;
    
    loadingSlots.value = true;
    try {
        const response = await axios.get('/api/admin/available-slots', {
            params: {
                date: form.value.date,
                doctor_id: form.value.doctor_id,
                service_id: form.value.service_id
            }
        });
        availableSlots.value = response.data.available_slots || [];
    } catch (error) {
        console.error('Error loading slots:', error);
        availableSlots.value = [];
    } finally {
        loadingSlots.value = false;
    }
};

const createAppointment = async () => {
    if (!canSubmit.value) return;
    
    submitting.value = true;
    try {
        const response = await axios.post('/api/admin/appointments', form.value);
        if (response.data.success) {
            showNotification('success', 'Запись успешно создана');
            resetForm();
            loadAppointments();
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при создании записи');
    } finally {
        submitting.value = false;
    }
};

const cancelAppointment = async (id) => {
    if (!confirm('Отменить эту запись?')) return;
    
    try {
        await axios.delete(`/api/admin/appointments/${id}`);
        showNotification('success', 'Запись отменена');
        loadAppointments();
    } catch (error) {
        showNotification('error', 'Ошибка при отмене записи');
    }
};

const resetForm = () => {
    form.value = {
        client_id: null,
        doctor_id: '',
        service_id: '',
        date: '',
        time: '',
        notes: ''
    };
    selectedClient.value = null;
    clientSearch.value = '';
    availableSlots.value = [];
    showNewClientForm.value = false;
    newClient.value = { name: '', phone: '', email: '' };
};

const loadAppointments = async () => {
    loadingAppointments.value = true;
    try {
        const response = await axios.get('/api/admin/appointments');
        appointmentsList.value = response.data.appointments || [];
    } catch (error) {
        console.error('Error loading appointments:', error);
    } finally {
        loadingAppointments.value = false;
    }
};

onMounted(() => {
    if (props.appointments) {
        appointmentsList.value = [...props.appointments];
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