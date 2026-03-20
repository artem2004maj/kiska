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

        <!-- Ближайшая запись -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">БЛИЖАЙШАЯ ЗАПИСЬ</h2>
            <div v-if="nextAppointment" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                <div class="grid gap-4">
                    <div class="flex items-center gap-8 text-black dark:text-white/70">
                        <span class="font-medium min-w-24">Дата:</span>
                        <span>{{ formatDate(nextAppointment.date) }}</span>
                    </div>
                    <div class="flex items-center gap-8 text-black dark:text-white/70">
                        <span class="font-medium min-w-24">Время:</span>
                        <span>{{ formatTime(nextAppointment.date) }}</span>
                    </div>
                    <div class="flex items-center gap-8 text-black dark:text-white/70">
                        <span class="font-medium min-w-24">Услуга:</span>
                        <span>{{ nextAppointment.service_names || 'Услуга не указана' }}</span>
                    </div>
                    <div class="flex items-center gap-8 text-black dark:text-white/70">
                        <span class="font-medium min-w-24">Врач:</span>
                        <span>{{ nextAppointment.employee?.employee_name || 'Не указан' }}</span>
                    </div>
                    <div class="flex gap-4 mt-4">
                        <button @click="cancelAppointment(nextAppointment.appointment_id)" 
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                            Отменить
                        </button>
                        <button @click="rescheduleAppointment(nextAppointment.appointment_id)" 
                                class="px-4 py-2 border border-[#14b8a6] text-[#14b8a6] rounded-md hover:bg-[#14b8a6]/10 transition">
                            Перенести
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white dark:bg-zinc-900 rounded-lg p-6 text-center text-gray-500">
                У вас нет предстоящих записей
            </div>
        </div>

        <!-- Лента уведомлений -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">УВЕДОМЛЕНИЯ</h2>
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                <div class="space-y-3">
                    <div v-for="notification in notifications" :key="notification.id" 
                         class="flex items-center gap-3 text-black dark:text-white/70">
                        <span class="w-2 h-2 bg-[#14b8a6] rounded-full"></span>
                        <span>{{ notification.message }}</span>
                        <button v-if="notification.type === 'feedback'" 
                                @click="goToFeedback(notification.appointment_id)"
                                class="ml-auto text-sm text-[#14b8a6] hover:underline">
                            Оставить отзыв
                        </button>
                    </div>
                    <div v-if="notifications.length === 0" class="text-center py-4 text-gray-500">
                        Нет новых уведомлений
                    </div>
                </div>
            </div>
        </div>

        <!-- Популярные услуги -->
        <div>
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ПОПУЛЯРНЫЕ УСЛУГИ</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="service in popularServices" :key="service.service_id" 
                     class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                    <h3 class="font-semibold text-lg mb-2 text-black dark:text-white">{{ service.service_name }}</h3>
                    <p class="text-sm text-gray-500 mb-2">{{ service.service_category }}</p>
                    <p class="text-[#14b8a6] font-medium mb-3">{{ service.default_price }} ₽</p>
                    <button @click="bookService(service.service_id)" 
                            class="w-full px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                        Записаться
                    </button>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
    nextAppointment: Object,
    notifications: Array,
    promotions: Array,
    popularServices: Array
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

const getServiceNames = (appointment) => {
    if (!appointment?.provided_services?.length) return 'Услуга не указана';
    return appointment.provided_services.map(ps => ps.service?.service_name).join(', ');
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
</script>