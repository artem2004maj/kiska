<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ИСТОРИЯ ПОСЕЩЕНИЙ</h2>
            
            <!-- Фильтры -->
            <div class="flex gap-4 mb-6">
                <select v-model="filterPeriod" class="px-4 py-2 border rounded-md">
                    <option value="all">За все время</option>
                    <option value="month">За последний месяц</option>
                    <option value="3months">За 3 месяца</option>
                    <option value="year">За год</option>
                </select>
                
                <select v-model="filterStatus" class="px-4 py-2 border rounded-md">
                    <option value="all">Все статусы</option>
                    <option value="2">Завершенные</option>
                    <option value="3">Отмененные</option>
                </select>
            </div>
            
            <!-- Список истории -->
            <div v-if="filteredHistory.length > 0" class="space-y-4">
                <div v-for="appointment in filteredHistory" :key="appointment.appointment_id" 
                     class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-sm text-gray-500">
                                    {{ formatDate(appointment.date) }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(appointment.status)">
                                    {{ getStatusText(appointment.status) }}
                                </span>
                            </div>
                            
                            <h3 class="font-semibold text-black dark:text-white text-lg">
                                {{ getServiceNames(appointment) }}
                            </h3>
                            
                            <p class="text-sm text-gray-600 mt-1 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ appointment.employee?.employee_name || 'Не указан' }}
                            </p>
                            
                            <!-- Отзыв -->
                            <div v-if="appointment.feedback" class="mt-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-1 mb-1">
                                    <svg v-for="star in 5" :key="star" 
                                         class="w-4 h-4" 
                                         :class="star <= appointment.feedback.score ? 'text-yellow-400' : 'text-gray-300'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-700">{{ appointment.feedback.comment }}</p>
                            </div>
                            
                            <!-- Кнопка отзыва -->
                            <button v-else-if="appointment.status === 2" 
                                    @click="openFeedbackModal(appointment)"
                                    class="mt-3 px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition text-sm">
                                Оставить отзыв
                            </button>
                        </div>
                        
                        <div class="text-right">
                            <p class="font-semibold text-[#14b8a6]">
                                {{ getTotalPrice(appointment) }} ₽
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-gray-500 text-lg">История посещений пуста</p>
            </div>
        </div>
        
        <!-- Модальное окно для отзыва -->
        <Teleport to="body">
            <div v-if="showFeedbackModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showFeedbackModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <h3 class="text-xl font-semibold mb-4">Оставить отзыв</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Услуга: {{ selectedAppointment?.serviceNames }}</p>
                            <p class="text-sm text-gray-600">Врач: {{ selectedAppointment?.doctorName }}</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Оценка</label>
                            <div class="flex gap-2">
                                <button v-for="star in 5" :key="star"
                                        @click="feedbackForm.score = star"
                                        class="focus:outline-none">
                                    <svg class="w-8 h-8" 
                                         :class="star <= feedbackForm.score ? 'text-yellow-400' : 'text-gray-300'"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Комментарий</label>
                            <textarea v-model="feedbackForm.comment" rows="4"
                                      class="w-full px-3 py-2 border rounded-md"
                                      placeholder="Поделитесь впечатлениями..."></textarea>
                        </div>
                        
                        <div class="flex gap-3">
                            <button @click="submitFeedback" 
                                    class="flex-1 px-4 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90"
                                    :disabled="!canSubmitFeedback">
                                Отправить
                            </button>
                            <button @click="showFeedbackModal = false" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </ClientLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
    history: Array
});

const filterPeriod = ref('all');
const filterStatus = ref('all');
const showFeedbackModal = ref(false);
const selectedAppointment = ref(null);
const feedbackForm = ref({
    score: 5,
    comment: ''
});

const canSubmitFeedback = computed(() => {
    return feedbackForm.value.score > 0 && feedbackForm.value.comment.trim().length > 0;
});

const filteredHistory = computed(() => {
    let filtered = [...props.history];
    
    // Фильтр по статусу
    if (filterStatus.value !== 'all') {
        filtered = filtered.filter(a => a.status == filterStatus.value);
    }
    
    // Фильтр по периоду
    if (filterPeriod.value !== 'all') {
        const now = new Date();
        let cutoff = new Date();
        
        switch(filterPeriod.value) {
            case 'month':
                cutoff.setMonth(now.getMonth() - 1);
                break;
            case '3months':
                cutoff.setMonth(now.getMonth() - 3);
                break;
            case 'year':
                cutoff.setFullYear(now.getFullYear() - 1);
                break;
        }
        
        filtered = filtered.filter(a => new Date(a.date) >= cutoff);
    }
    
    return filtered;
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const getServiceNames = (appointment) => {
    if (!appointment?.provided_services?.length) return 'Услуга не указана';
    return appointment.provided_services.map(ps => ps.service?.service_name).join(', ');
};

const getStatusClass = (status) => {
    switch(status) {
        case 2: return 'bg-green-100 text-green-800';
        case 3: return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch(status) {
        case 2: return 'Завершен';
        case 3: return 'Отменен';
        default: return 'Неизвестно';
    }
};

const getTotalPrice = (appointment) => {
    if (!appointment?.provided_services?.length) return 0;
    return appointment.provided_services.reduce((sum, ps) => {
        return sum + (ps.service?.default_price || 0) * ps.quantity;
    }, 0);
};

const openFeedbackModal = (appointment) => {
    selectedAppointment.value = {
        ...appointment,
        serviceNames: getServiceNames(appointment),
        doctorName: appointment.employee?.employee_name
    };
    feedbackForm.value = { score: 5, comment: '' };
    showFeedbackModal.value = true;
};

const submitFeedback = async () => {
    if (!canSubmitFeedback.value) return;
    
    try {
        await axios.post('/api/client/feedback', {
            appointment_id: selectedAppointment.value.appointment_id,
            score: feedbackForm.value.score,
            comment: feedbackForm.value.comment
        });
        
        showFeedbackModal.value = false;
        router.reload();
    } catch (error) {
        alert('Ошибка при отправке отзыва');
    }
};
</script>