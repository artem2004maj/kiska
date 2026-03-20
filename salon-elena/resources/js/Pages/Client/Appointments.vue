<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold mb-6">МОИ ЗАПИСИ</h2>
            
            <div v-if="appointments.length > 0" class="space-y-4">
                <div v-for="appointment in appointments" :key="appointment.appointment_id" 
                     class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-sm text-gray-500">
                                {{ formatDate(appointment.date) }} в {{ formatTime(appointment.date) }}
                            </span>
                            <h3 class="font-semibold text-lg mt-1">
                                {{ appointment.service_names || 'Услуга не указана' }}
                            </h3>
                            <p class="text-gray-600 flex items-center gap-1 mt-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ appointment.employee?.employee_name || 'Не указан' }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-xs rounded-full" :class="getStatusClass(appointment.status)">
                            {{ getStatusText(appointment.status) }}
                        </span>
                    </div>
                    
                    <div class="flex gap-3 mt-4">
                        <button v-if="appointment.status === 0 || appointment.status === 1" 
                                @click="cancelAppointment(appointment.appointment_id)" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                            Отменить
                        </button>
                        <button v-if="appointment.status === 0 || appointment.status === 1" 
                                @click="rescheduleAppointment(appointment.appointment_id)" 
                                class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                            Перенести
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 text-lg">У вас нет активных записей</p>
                <button @click="goToServices" 
                        class="mt-4 px-6 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                    Записаться на прием
                </button>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
    appointments: Array
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('ru-RU', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
};

const getStatusClass = (status) => {
    switch(status) {
        case 0: return 'bg-blue-100 text-blue-800';
        case 1: return 'bg-green-100 text-green-800';
        case 2: return 'bg-gray-100 text-gray-800';
        case 3: return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch(status) {
        case 0: return 'Запланирован';
        case 1: return 'Подтвержден';
        case 2: return 'Завершен';
        case 3: return 'Отменен';
        default: return 'Неизвестно';
    }
};

const cancelAppointment = async (id) => {
    if (!confirm('Вы уверены, что хотите отменить запись?')) return;
    
    try {
        await axios.put(`/api/client/appointments/${id}/cancel`);
        router.reload();
    } catch (error) {
        alert('Ошибка при отмене записи');
    }
};

const rescheduleAppointment = (id) => {
    router.get(`/client/services?reschedule=${id}`);
};

const goToServices = () => {
    router.get('/client/services');
};
</script>