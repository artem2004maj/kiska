<!-- resources/js/Pages/Doctor/MedicalRecord.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <!-- Кнопка назад -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold">МЕДИЦИНСКАЯ КАРТА</h2>
                <button @click="goBack" class="px-4 py-2 border rounded-md hover:bg-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к пациентам
                </button>
            </div>
            
            <!-- Информация о пациенте -->
            <div class="bg-[#2A7F6E]/5 rounded-lg p-4 mb-6 flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-[#2A7F6E]/20 flex items-center justify-center overflow-hidden flex-shrink-0">
                    <span class="text-2xl font-medium text-[#2A7F6E]">
                        {{ getInitials(patient.client_name) }}
                    </span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">{{ patient.client_name }}</h3>
                    <p class="text-sm text-gray-500">📞 {{ patient.phone }}</p>
                    <p class="text-sm text-gray-500">📅 {{ formatDate(patient.birth_date) }}</p>
                </div>
            </div>
            
            <!-- Выбор приема для создания записи -->
            <div class="mb-6">
                <h3 class="font-semibold mb-3">Выберите прием для добавления записи</h3>
                <select v-model="selectedAppointmentId" class="w-full px-4 py-2 border rounded-md">
                    <option value="">-- Выберите прием --</option>
                    <option v-for="apt in appointmentsWithoutRecord" :key="apt.appointment_id" :value="apt.appointment_id">
                        {{ formatDate(apt.date) }} - {{ getServiceName(apt) }} ({{ formatTime(apt.date) }})
                    </option>
                </select>
            </div>
            
            <!-- Форма создания/редактирования записи -->
            <div v-if="selectedAppointmentId" class="border-t pt-6 mb-8">
                <h3 class="font-semibold text-lg mb-4">Новая запись</h3>
                
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Прием:</span> 
                        {{ getSelectedAppointmentDate }} в {{ getSelectedAppointmentTime }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Услуга:</span> {{ getSelectedAppointmentService }}
                    </p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Дата визита</label>
                        <input type="date" v-model="form.visit_date" 
                               class="w-full px-4 py-2 border rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Анамнез</label>
                        <textarea v-model="form.anamnesis" rows="3"
                                  class="w-full px-4 py-2 border rounded-md"
                                  placeholder="Жалобы пациента, история заболевания..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Диагноз</label>
                        <textarea v-model="form.diagnosis" rows="3"
                                  class="w-full px-4 py-2 border rounded-md"
                                  placeholder="Клинический диагноз..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Противопоказания</label>
                        <textarea v-model="form.contraindications" rows="2"
                                  class="w-full px-4 py-2 border rounded-md"
                                  placeholder="Аллергии, противопоказания к процедурам..."></textarea>
                    </div>
                    <button @click="saveRecord" 
                            class="px-6 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90">
                        Сохранить запись
                    </button>
                </div>
            </div>
            
            <!-- История записей -->
            <div class="border-t pt-6">
                <h3 class="font-semibold text-lg mb-4">История записей</h3>
                
                <div v-if="records.length > 0" class="space-y-4">
                    <div v-for="record in records" :key="record.record_id" 
                         class="border rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <span class="text-sm text-gray-500">
                                    {{ formatDate(record.visit_date) }}
                                </span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Врач: {{ record.employee?.employee_name }}
                                </p>
                                <p v-if="record.appointment" class="text-xs text-gray-400 mt-1">
                                    Прием: {{ formatDate(record.appointment.date) }} - 
                                    {{ getServiceNameFromAppointment(record.appointment) }}
                                </p>
                            </div>
                            <button @click="editRecord(record)" 
                                    class="text-[#2A7F6E] hover:underline text-sm">
                                Редактировать
                            </button>
                        </div>
                        
                        <div class="grid gap-3 mt-3">
                            <div v-if="record.anamnesis" class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm font-medium text-gray-700 mb-1">Анамнез:</p>
                                <p class="text-sm text-gray-600">{{ record.anamnesis }}</p>
                            </div>
                            
                            <div v-if="record.diagnosis" class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm font-medium text-gray-700 mb-1">Диагноз:</p>
                                <p class="text-sm text-gray-600">{{ record.diagnosis }}</p>
                            </div>
                            
                            <div v-if="record.contraindications" class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-sm font-medium text-gray-700 mb-1">Противопоказания:</p>
                                <p class="text-sm text-gray-600">{{ record.contraindications }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 text-gray-500">
                    Нет записей в медицинской карте
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    doctor: Object,
    patient: Object,
    records: Array,
    appointments: Array
});

const selectedAppointmentId = ref('');
const form = ref({
    visit_date: new Date().toISOString().split('T')[0],
    anamnesis: '',
    diagnosis: '',
    contraindications: ''
});

// Приемы без медицинской записи
const appointmentsWithoutRecord = computed(() => {
    return props.appointments.filter(apt => !apt.medical_record);
});

// Вспомогательные методы
const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const getServiceName = (appointment) => {
    if (!appointment.provided_services?.length) return 'Услуга не указана';
    return appointment.provided_services.map(ps => ps.service?.service_name).join(', ');
};

const getServiceNameFromAppointment = (appointment) => {
    return getServiceName(appointment);
};

// Вычисляемые свойства для выбранного приема
const selectedAppointment = computed(() => {
    if (!selectedAppointmentId.value) return null;
    return props.appointments.find(apt => apt.appointment_id == selectedAppointmentId.value);
});

const getSelectedAppointmentDate = computed(() => {
    return selectedAppointment.value ? formatDate(selectedAppointment.value.date) : '';
});

const getSelectedAppointmentTime = computed(() => {
    return selectedAppointment.value ? formatTime(selectedAppointment.value.date) : '';
});

const getSelectedAppointmentService = computed(() => {
    return selectedAppointment.value ? getServiceName(selectedAppointment.value) : '';
});

// Методы
const goBack = () => {
    router.get('/doctor/patients');
};

const saveRecord = async () => {
    if (!selectedAppointmentId.value) {
        alert('Выберите прием');
        return;
    }
    
    if (!form.value.visit_date) {
        alert('Укажите дату визита');
        return;
    }
    
    try {
        await axios.post(`/api/doctor/medical-records/${props.patient.client_id}`, {
            appointment_id: selectedAppointmentId.value,
            visit_date: form.value.visit_date,
            anamnesis: form.value.anamnesis,
            diagnosis: form.value.diagnosis,
            contraindications: form.value.contraindications
        });
        
        alert('Запись успешно сохранена');
        router.reload();
    } catch (error) {
        console.error('Error saving record:', error);
        alert(error.response?.data?.error || 'Ошибка при сохранении записи');
    }
};

const editRecord = (record) => {
    // Можно открыть модальное окно для редактирования
    // Пока просто покажем в консоли
    console.log('Edit record:', record);
};
</script>