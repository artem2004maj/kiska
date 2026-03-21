<!-- resources/js/Pages/Client/Dashboard.vue -->
<template>
    <ClientLayout :client="client">
        <!-- Баннер/Слайдер с акциями -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-[#14b8a6] to-[#0d9488] rounded-lg p-8 text-white">
                <h2 class="text-2xl font-bold mb-2">Специальное предложение!</h2>
                <p class="mb-4">Скидка 20% на комплексный уход за лицом</p>
                <button @click="goToServices" class="px-6 py-2 bg-white text-[#14b8a6] rounded-md hover:bg-gray-100 transition font-medium">
                    Записаться
                </button>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Всего посещений</p>
                        <p class="text-2xl font-semibold">{{ stats.totalVisits || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Активных записей</p>
                        <p class="text-2xl font-semibold text-green-600">{{ stats.activeAppointments || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Отзывов</p>
                        <p class="text-2xl font-semibold text-[#14b8a6]">{{ stats.totalReviews || 0 }}</p>
                    </div>
                    <div class="p-3 bg-[#14b8a6]/10 rounded-full">
                        <svg class="w-6 h-6 text-[#14b8a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ближайшая запись -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">БЛИЖАЙШАЯ ЗАПИСЬ</h2>
            <div v-if="nextAppointment" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="flex items-start justify-between">
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-4">
                            <span class="text-sm text-gray-500">
                                {{ formatDate(nextAppointment.date) }} в {{ formatTime(nextAppointment.date) }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(nextAppointment.status)">
                                {{ getStatusText(nextAppointment.status) }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">{{ nextAppointment.service_names || 'Услуга не указана' }}</h3>
                            <p class="text-gray-600 flex items-center gap-1 mt-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ nextAppointment.employee?.employee_name || 'Не указан' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">
                    <button v-if="nextAppointment.status === 0" 
                            class="px-4 py-2 text-sm bg-yellow-500 text-white rounded-md cursor-not-allowed" disabled>
                        Ожидает подтверждения
                    </button>
                    <template v-else>
                        <button @click="cancelAppointment(nextAppointment.appointment_id)" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                            Отменить
                        </button>
                        <button @click="rescheduleAppointment(nextAppointment.appointment_id)" 
                                class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                            Перенести
                        </button>
                    </template>
                </div>
            </div>
            <div v-else class="bg-white dark:bg-zinc-900 rounded-lg p-8 text-center text-gray-500">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>У вас нет предстоящих записей</p>
                <button @click="goToServices" class="mt-4 px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                    Записаться на прием
                </button>
            </div>
        </div>

        <!-- Лента уведомлений -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold text-black dark:text-white">УВЕДОМЛЕНИЯ</h2>
                <button v-if="notifications.length > 0" 
                        @click="markAllAsRead"
                        class="text-sm text-[#14b8a6] hover:underline">
                    Отметить все как прочитанные
                </button>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
                <div class="space-y-3">
                    <div v-for="notification in notifications" :key="notification.id" 
                         class="flex items-start gap-3 p-3 rounded-lg transition cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800"
                         :class="{
                             'bg-blue-50 dark:bg-blue-900/20': notification.type === 'confirmation',
                             'bg-red-50 dark:bg-red-900/20': notification.type === 'rejection',
                             'bg-green-50 dark:bg-green-900/20': notification.type === 'completion',
                             'bg-yellow-50 dark:bg-yellow-900/20': notification.type === 'reminder',
                         }"
                         @click="markAsRead(notification.id)">
                        <div class="flex-shrink-0 mt-1">
                            <span class="w-2 h-2 rounded-full block" :class="{
                                'bg-green-500': notification.type === 'confirmation',
                                'bg-red-500': notification.type === 'rejection',
                                'bg-blue-500': notification.type === 'completion',
                                'bg-yellow-500': notification.type === 'reminder',
                                'bg-[#14b8a6]': notification.type === 'feedback'
                            }"></span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm">{{ notification.message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDateTime(notification.created_at) }}</p>
                        </div>
                        <button v-if="notification.type === 'feedback'" 
                                @click.stop="goToFeedback(notification.appointment_id)"
                                class="text-sm text-[#14b8a6] hover:underline whitespace-nowrap">
                            Оставить отзыв
                        </button>
                        <button v-else-if="notification.type === 'confirmation' && notification.appointment_id"
                                @click.stop="viewAppointment(notification.appointment_id)"
                                class="text-sm text-[#14b8a6] hover:underline whitespace-nowrap">
                            Подробнее
                        </button>
                    </div>
                    <div v-if="notifications.length === 0" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p>Нет новых уведомлений</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Популярные услуги -->
        <div>
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ПОПУЛЯРНЫЕ УСЛУГИ</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="service in popularServices" :key="service.service_id" 
                     class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow hover:shadow-lg transition cursor-pointer"
                     @click="bookService(service.service_id)">
                    <h3 class="font-semibold text-lg mb-2">{{ service.service_name }}</h3>
                    <p class="text-sm text-gray-500 mb-2">{{ service.service_category }}</p>
                    <p class="text-[#14b8a6] font-medium mb-3">{{ service.default_price }} ₽</p>
                    <button class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                        Записаться
                    </button>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
    nextAppointment: Object,
    notifications: Array,
    promotions: Array,
    popularServices: Array,
    appointments: {
        type: Array,
        default: () => []
    },
    feedback: {
        type: Array,
        default: () => []
    }
});

// Статистика
const stats = computed(() => ({
    totalVisits: props.appointments?.length || 0,
    activeAppointments: props.appointments?.filter(a => a.status === 0 || a.status === 1).length || 0,
    totalReviews: props.feedback?.length || 0
}));

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('ru-RU', { 
        hour: '2-digit', 
        minute: '2-digit'
    });
};

const formatDateTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return `${d.toLocaleDateString('ru-RU')} ${d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}`;
};

const getStatusClass = (status) => {
    switch(status) {
        case 0: return 'bg-yellow-100 text-yellow-800';
        case 1: return 'bg-green-100 text-green-800';
        case 2: return 'bg-gray-100 text-gray-800';
        case 3: return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch(status) {
        case 0: return 'Ожидает подтверждения';
        case 1: return 'Подтвержден';
        case 2: return 'Завершен';
        case 3: return 'Отменен';
        default: return 'Неизвестно';
    }
};

const goToServices = () => {
    router.get('/client/services');
};

const bookService = (serviceId) => {
    router.get(`/client/services?service=${serviceId}`);
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

const goToFeedback = (appointmentId) => {
    router.get(`/client/history?feedback=${appointmentId}`);
};

const viewAppointment = (appointmentId) => {
    router.get(`/client/appointments`);
};

const markAsRead = async (notificationId) => {
    try {
        await axios.put(`/api/client/notifications/${notificationId}/read`);
        router.reload();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.put('/api/client/notifications/read-all');
        router.reload();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
};
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>