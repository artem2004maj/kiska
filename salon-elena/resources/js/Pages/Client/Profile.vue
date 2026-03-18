<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Левая колонка - Аватар -->
                <div class="md:col-span-1">
                    <div class="flex flex-col items-center">
                        <div class="w-32 h-32 bg-[#14b8a6]/20 rounded-full flex items-center justify-center mb-4">
                            <span class="text-4xl font-medium text-[#14b8a6]">
                                {{ getInitials(client.client_name) }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-lg">{{ client.client_name }}</h3>
                        <p class="text-sm text-gray-500">ID: {{ client.client_id }}</p>
                        
                        <div class="mt-6 w-full">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium mb-2">Статистика</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Всего посещений:</span>
                                        <span class="font-semibold">{{ totalVisits }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Активных записей:</span>
                                        <span class="font-semibold">{{ activeAppointments }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Отзывов:</span>
                                        <span class="font-semibold">{{ totalFeedback }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Правая колонка - Форма профиля -->
                <div class="md:col-span-2">
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Имя <span class="text-red-500">*</span>
                                </label>
                                <input type="text" v-model="form.client_name" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent"
                                       :class="{ 'border-red-500': errors.client_name }">
                                <p v-if="errors.client_name" class="mt-1 text-xs text-red-500">
                                    {{ errors.client_name }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" v-model="form.email" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent"
                                       :class="{ 'border-red-500': errors.email }">
                                <p v-if="errors.email" class="mt-1 text-xs text-red-500">
                                    {{ errors.email }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Телефон
                                </label>
                                <input type="tel" v-model="form.phone" 
                                       placeholder="+7 (999) 123-45-67"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                                <p class="mt-1 text-xs text-gray-400">Формат: +7XXXXXXXXXX</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Дата рождения
                                </label>
                                <input type="date" v-model="form.birth_date"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t">
                            <h3 class="font-medium mb-4">Изменить пароль</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Текущий пароль
                                    </label>
                                    <input type="password" v-model="passwordForm.current_password"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Новый пароль
                                        </label>
                                        <input type="password" v-model="passwordForm.new_password"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Подтверждение
                                        </label>
                                        <input type="password" v-model="passwordForm.new_password_confirmation"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#14b8a6] focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="loading"
                                    class="px-6 py-2 bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                                {{ loading ? 'Сохранение...' : 'Сохранить изменения' }}
                            </button>
                            
                            <button type="button" @click="resetForm"
                                    class="px-6 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                                Отмена
                            </button>
                        </div>
                        
                        <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-md">
                            {{ successMessage }}
                        </div>
                        
                        <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-md">
                            {{ errorMessage }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import axios from 'axios';

const props = defineProps({
    client: Object,
    appointments: {
        type: Array,
        default: () => []
    },
    feedback: {
        type: Array,
        default: () => []
    }
});

// Форма профиля
const form = ref({
    client_name: props.client?.client_name || '',
    email: props.client?.email || '',
    phone: props.client?.phone || '',
    birth_date: props.client?.birth_date || ''
});

// Форма пароля
const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const loading = ref(false);
const errors = ref({});
const successMessage = ref('');
const errorMessage = ref('');

// Статистика
const totalVisits = computed(() => props.appointments?.length || 0);
const activeAppointments = computed(() => 
    props.appointments?.filter(a => a.status === 0 || a.status === 1).length || 0
);
const totalFeedback = computed(() => props.feedback?.length || 0);

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const resetForm = () => {
    form.value = {
        client_name: props.client?.client_name || '',
        email: props.client?.email || '',
        phone: props.client?.phone || '',
        birth_date: props.client?.birth_date || ''
    };
    passwordForm.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
    };
    errors.value = {};
    successMessage.value = '';
    errorMessage.value = '';
};

const updateProfile = async () => {
    loading.value = true;
    errors.value = {};
    successMessage.value = '';
    errorMessage.value = '';
    
    try {
        // Обновление основных данных
        await axios.put('/api/client/profile', form.value);
        
        // Если есть данные для смены пароля
        if (passwordForm.value.current_password || 
            passwordForm.value.new_password || 
            passwordForm.value.new_password_confirmation) {
            
            await axios.post('/api/client/change-password', passwordForm.value);
        }
        
        successMessage.value = 'Профиль успешно обновлен';
        passwordForm.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
        };
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            errorMessage.value = Object.values(errors.value).flat()[0];
        } else {
            errorMessage.value = error.response?.data?.message || 'Ошибка при обновлении профиля';
        }
    } finally {
        loading.value = false;
    }
};
</script>