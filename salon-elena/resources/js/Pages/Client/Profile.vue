<!-- resources/js/Pages/Client/Profile.vue -->
<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 sm:p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 max-w-4xl mx-auto">
            <h2 class="text-xl sm:text-2xl font-semibold text-black dark:text-white mb-4 sm:mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
                <!-- Левая колонка - Фото -->
                <div class="md:col-span-1">
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <!-- Затемнение при наведении -->
                            <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center cursor-pointer"
                                 @click="triggerFileInput">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            
                            <!-- Фото или заглушка -->
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-[#14b8a6]/10 flex items-center justify-center overflow-hidden border-4 border-[#14b8a6]/20 transition-transform group-hover:scale-105">
                                <!-- Превью нового фото -->
                                <img v-if="photoPreview" 
                                     :src="photoPreview" 
                                     alt="Preview"
                                     class="w-full h-full object-cover">
                                <!-- Текущее фото -->
                                <img v-else-if="client?.photo_url" 
                                     :src="client.photo_url" 
                                     :alt="client.client_name"
                                     class="w-full h-full object-cover">
                                <!-- Заглушка с инициалами -->
                                <span v-else class="text-3xl sm:text-4xl font-medium text-[#14b8a6]">
                                    {{ getInitials(client?.client_name) }}
                                </span>
                            </div>
                            
                            <!-- Скрытый input для файла -->
                            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
                        </div>
                        
                        <h3 class="mt-3 font-semibold text-lg">{{ client?.client_name }}</h3>
                        <p class="text-sm text-gray-500">ID: {{ client?.client_id }}</p>
                        
                        <!-- Кнопки управления фото -->
                        <div v-if="photoPreview" class="mt-3 flex gap-2">
                            <button @click="uploadPhoto" 
                                    class="px-4 py-1.5 bg-[#14b8a6] text-white text-sm rounded-md hover:bg-[#14b8a6]/90">
                                Сохранить
                            </button>
                            <button @click="cancelPhoto" 
                                    class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                        
                        <div v-else-if="client?.photo_url" class="mt-3">
                            <button @click="deletePhoto" 
                                    class="px-4 py-1.5 bg-red-500 text-white text-sm rounded-md hover:bg-red-600">
                                Удалить фото
                            </button>
                        </div>
                        
                        <!-- Прогресс загрузки -->
                        <div v-if="uploadProgress > 0" class="mt-3 w-full max-w-[200px]">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-[#14b8a6] h-2 rounded-full transition-all duration-300"
                                     :style="{ width: uploadProgress + '%' }"></div>
                            </div>
                        </div>
                        
                        <!-- Статистика -->
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
                        
                        <!-- Уведомления -->
                        <Transition name="fade">
                            <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-md">
                                {{ successMessage }}
                            </div>
                        </Transition>
                        
                        <Transition name="fade">
                            <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-md">
                                {{ errorMessage }}
                            </div>
                        </Transition>
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

// Фото
const fileInput = ref(null);
const photoPreview = ref(null);
const selectedFile = ref(null);
const uploadProgress = ref(0);

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

// Методы для фото
const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Файл слишком большой. Максимум 2MB';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Пожалуйста, выберите изображение';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    selectedFile.value = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const uploadPhoto = async () => {
    if (!selectedFile.value) return;
    
    const formData = new FormData();
    formData.append('photo', selectedFile.value);
    
    try {
        const response = await axios.post('/api/client/upload-photo', formData, {
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            }
        });
        
        props.client.photo_url = response.data.photo_url;
        photoPreview.value = null;
        selectedFile.value = null;
        uploadProgress.value = 0;
        successMessage.value = 'Фото загружено!';
        setTimeout(() => successMessage.value = '', 3000);
        
    } catch (error) {
        errorMessage.value = 'Ошибка при загрузке';
        setTimeout(() => errorMessage.value = '', 3000);
    }
};

const deletePhoto = async () => {
    if (!confirm('Удалить фото?')) return;
    
    try {
        await axios.delete('/api/client/delete-photo');
        props.client.photo_url = null;
        successMessage.value = 'Фото удалено';
        setTimeout(() => successMessage.value = '', 3000);
    } catch (error) {
        errorMessage.value = 'Ошибка при удалении';
        setTimeout(() => errorMessage.value = '', 3000);
    }
};

const cancelPhoto = () => {
    photoPreview.value = null;
    selectedFile.value = null;
    fileInput.value.value = '';
};

// Методы формы
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
        await axios.put('/api/client/profile', form.value);
        
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
        setTimeout(() => successMessage.value = '', 3000);
        
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors || {};
            errorMessage.value = Object.values(errors.value).flat()[0];
        } else {
            errorMessage.value = error.response?.data?.message || 'Ошибка при обновлении профиля';
        }
        setTimeout(() => errorMessage.value = '', 3000);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}
</style>