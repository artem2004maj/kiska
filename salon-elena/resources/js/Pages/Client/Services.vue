<!-- resources/js/Pages/Client/Services.vue -->
<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ЗАПИСЬ НА УСЛУГУ</h2>
            
            <!-- Шаги записи -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-[#14b8a6] text-white flex items-center justify-center font-bold">1</div>
                    <span class="ml-2 font-medium" :class="step >= 1 ? 'text-[#14b8a6]' : 'text-gray-400'">Выбор услуги</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
    
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                        :class="step >= 2 ? 'bg-[#14b8a6] text-white' : 'bg-gray-300 text-gray-500'">
                        2
                    </div>
                    <span class="ml-2 font-medium" :class="step >= 2 ? 'text-[#14b8a6]' : 'text-gray-400'">Выбор времени</span>
                </div>
                <div class="w-16 h-0.5 bg-gray-300"></div>
    
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                        :class="step >= 3 ? 'bg-[#14b8a6] text-white' : 'bg-gray-300 text-gray-500'">
                        3
                    </div>
                    <span class="ml-2 font-medium" :class="step >= 3 ? 'text-[#14b8a6]' : 'text-gray-400'">Подтверждение</span>
                </div>
            </div>

            <!-- Шаг 1: Выбор услуги -->
            <div v-if="step === 1" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выберите категорию</label>
                    <select v-model="selectedCategory" @change="filterByCategory" class="w-full px-4 py-2 border rounded-md">
                        <option value="">Все категории</option>
                        <option v-for="category in Object.keys(services)" :key="category" :value="category">
                            {{ category }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выберите услугу</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                        <div v-for="service in filteredServices" :key="service.service_id" 
                             @click="selectedService = service"
                             class="border rounded-lg p-4 cursor-pointer transition"
                             :class="selectedService?.service_id === service.service_id ? 'border-[#14b8a6] ring-2 ring-[#14b8a6]/20' : 'hover:border-gray-400'">
                            <h3 class="font-semibold">{{ service.service_name }}</h3>
                            <p class="text-sm text-gray-500">{{ service.service_category }}</p>
                            <p class="text-[#14b8a6] font-bold mt-2">{{ service.default_price }} ₽</p>
                            <p v-if="service.doctors?.length" class="text-xs text-gray-400 mt-1">
                                Врач: {{ service.doctors[0]?.employee_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="nextStep" :disabled="!selectedService"
                            class="px-6 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Далее
                    </button>
                </div>
            </div>

            <!-- Шаг 2: Выбор даты и времени -->
            <div v-else-if="step === 2" class="space-y-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-medium">Выбрано:</p>
                    <p>{{ selectedService?.service_name }} - {{ selectedService?.default_price }} ₽</p>
                    <p v-if="selectedDoctorName">Врач: {{ selectedDoctorName }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Выберите дату</label>
                    <input type="date" v-model="selectedDate" :min="minDate" @change="loadAvailableSlots"
                           class="w-full px-4 py-2 border rounded-md">
                </div>

                <div v-if="availableSlots.length > 0">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Доступное время</label>
                    <div class="grid grid-cols-4 gap-3">
                        <button v-for="slot in availableSlots" :key="slot"
                                @click="selectedTime = slot"
                                class="px-4 py-2 border rounded-md text-center transition"
                                :class="selectedTime === slot ? 'bg-[#14b8a6] text-white' : 'hover:bg-gray-100'">
                            {{ slot }}
                        </button>
                    </div>
                </div>

                <div v-else-if="selectedDate" class="text-center py-4 text-gray-500">
                    Нет свободного времени на выбранную дату
                </div>

                <div class="flex justify-between">
                    <button @click="step = 1" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                        Назад
                    </button>
                    <button @click="nextStep" :disabled="!selectedDate || !selectedTime"
                            class="px-6 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Далее
                    </button>
                </div>
            </div>

            <!-- Шаг 3: Подтверждение -->
            <div v-else-if="step === 3" class="space-y-6">
                <div class="bg-gray-50 p-6 rounded-lg space-y-3">
                    <h3 class="font-semibold text-lg">Проверьте данные</h3>
                    <p><span class="font-medium">Услуга:</span> {{ selectedService?.service_name }}</p>
                    <p><span class="font-medium">Врач:</span> {{ selectedDoctorName }}</p>
                    <p><span class="font-medium">Дата:</span> {{ formatDate(selectedDate) }}</p>
                    <p><span class="font-medium">Время:</span> {{ selectedTime }}</p>
                    <p><span class="font-medium">Стоимость:</span> {{ selectedService?.default_price }} ₽</p>
                </div>

                <div class="flex justify-between">
                    <button @click="step = 2" class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                        Назад
                    </button>
                    <button @click="createAppointment" :disabled="loading"
                            class="px-6 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 disabled:bg-gray-300">
                        {{ loading ? 'Создание...' : 'Подтвердить запись' }}
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
    services: Object
});

const step = ref(1);
const selectedCategory = ref('');
const selectedService = ref(null);
const selectedDate = ref('');
const selectedTime = ref('');
const availableSlots = ref([]);
const loading = ref(false);

const minDate = new Date().toISOString().split('T')[0];

const selectedDoctorName = computed(() => {
    return selectedService.value?.doctors?.[0]?.employee_name || null;
});

const filteredServices = computed(() => {
    let filtered = [];
    
    Object.values(props.services).forEach(categoryServices => {
        filtered = [...filtered, ...categoryServices];
    });
    
    if (selectedCategory.value) {
        filtered = filtered.filter(s => s.service_category === selectedCategory.value);
    }
    
    return filtered;
});

const filterByCategory = () => {
    selectedService.value = null;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const loadAvailableSlots = async () => {
    if (!selectedDate.value || !selectedService.value) return;
    
    try {
        const response = await axios.get('/api/client/available-slots', {
            params: {
                date: selectedDate.value,
                service_id: selectedService.value.service_id
            }
        });
        availableSlots.value = response.data.available_slots;
        selectedTime.value = '';
    } catch (error) {
        console.error('Error loading slots:', error);
        alert(error.response?.data?.error || 'Ошибка при загрузке времени');
    }
};

const nextStep = () => {
    step.value++;
};

const createAppointment = async () => {
    if (!selectedService.value || !selectedDate.value || !selectedTime.value) return;
    
    loading.value = true;
    
    try {
        await axios.post('/api/client/appointments', {
            service_id: selectedService.value.service_id,
            date: selectedDate.value,
            time: selectedTime.value
        });
        
        alert('Запись успешно создана!');
        router.get('/client/appointments');
    } catch (error) {
        alert(error.response?.data?.error || 'Ошибка при создании записи');
    } finally {
        loading.value = false;
    }
};

const urlParams = new URLSearchParams(window.location.search);
const preselectedService = urlParams.get('service');

if (preselectedService) {
    setTimeout(() => {
        Object.values(props.services).forEach(categoryServices => {
            const service = categoryServices.find(s => s.service_id == preselectedService);
            if (service) {
                selectedService.value = service;
                step.value = 2;
            }
        });
    }, 100);
}
</script>