<!-- resources/js/Pages/Accountant/Profile.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ПРОФИЛЬ'"
        :unpaidCount="stats.unpaid_count"
        :criticalCount="stats.critical_count"
        :todayRevenue="stats.today_revenue"
        :pendingPayments="stats.pending_payments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow max-w-2xl mx-auto">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
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
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-[#3b82f6]/10 flex items-center justify-center overflow-hidden border-4 border-[#3b82f6]/20 transition-transform group-hover:scale-105">
                                <!-- Превью нового фото -->
                                <img v-if="photoPreview" 
                                     :src="photoPreview" 
                                     alt="Preview"
                                     class="w-full h-full object-cover">
                                <!-- Текущее фото -->
                                <img v-else-if="accountant?.photo_url" 
                                     :src="accountant.photo_url" 
                                     :alt="accountant.employee_name"
                                     class="w-full h-full object-cover">
                                <!-- Заглушка с инициалами -->
                                <span v-else class="text-3xl sm:text-4xl font-medium text-[#3b82f6]">
                                    {{ getInitials(accountant?.employee_name) }}
                                </span>
                            </div>
                            
                            <!-- Скрытый input для файла -->
                            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
                        </div>
                        
                        <h3 class="mt-3 font-semibold text-lg">{{ accountant?.employee_name }}</h3>
                        <p class="text-sm text-gray-500">ID: {{ accountant?.employee_id }}</p>
                        <p class="text-xs text-gray-400 mt-1">Роль: {{ getRoleName(accountant?.role) }}</p>
                        
                        <!-- Кнопки управления фото -->
                        <div v-if="photoPreview" class="mt-3 flex gap-2">
                            <button @click="uploadPhoto" 
                                    class="px-4 py-1.5 bg-[#3b82f6] text-white text-sm rounded-md hover:bg-[#3b82f6]/90">
                                Сохранить
                            </button>
                            <button @click="cancelPhoto" 
                                    class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                        
                        <div v-else-if="accountant?.photo_url" class="mt-3">
                            <button @click="deletePhoto" 
                                    class="px-4 py-1.5 bg-red-500 text-white text-sm rounded-md hover:bg-red-600">
                                Удалить фото
                            </button>
                        </div>
                        
                        <!-- Прогресс загрузки -->
                        <div v-if="uploadProgress > 0" class="mt-3 w-full max-w-[200px]">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-[#3b82f6] h-2 rounded-full transition-all duration-300"
                                     :style="{ width: uploadProgress + '%' }"></div>
                            </div>
                        </div>
                        
                        <!-- Статистика -->
                        <div class="mt-6 w-full">
                            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                                <h4 class="font-medium mb-2">Статистика</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Обработано приемов:</span>
                                        <span class="font-semibold">{{ stats.total_appointments }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Завершено приемов:</span>
                                        <span class="font-semibold text-green-600">{{ stats.completed_appointments }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Ставка (₽/час):</span>
                                        <span class="font-semibold">{{ formatPrice(accountant?.hourly_rate) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Правая колонка - Форма профиля -->
                <div class="md:col-span-2">
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Имя <span class="text-red-500">*</span>
                            </label>
                            <input type="text" v-model="form.employee_name" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   :class="{ 'border-red-500': errors.employee_name }">
                            <p v-if="errors.employee_name" class="mt-1 text-xs text-red-500">
                                {{ errors.employee_name }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" v-model="form.email" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   :class="{ 'border-red-500': errors.email }">
                            <p v-if="errors.email" class="mt-1 text-xs text-red-500">
                                {{ errors.email }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Телефон
                            </label>
                            <input type="tel" v-model="form.employee_phone" 
                                   placeholder="+7 (999) 123-45-67"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white">
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200 dark:border-zinc-700">
                            <h3 class="font-medium mb-4">Изменить пароль</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Текущий пароль
                                    </label>
                                    <input type="password" v-model="passwordForm.current_password"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white">
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Новый пароль
                                        </label>
                                        <input type="password" v-model="passwordForm.new_password"
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Подтверждение
                                        </label>
                                        <input type="password" v-model="passwordForm.new_password_confirmation"
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent dark:bg-zinc-800 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <button type="submit" :disabled="loading"
                                    class="px-6 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                                {{ loading ? 'Сохранение...' : 'Сохранить изменения' }}
                            </button>
                            
                            <button type="button" @click="resetForm"
                                    class="px-6 py-2 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
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
    </AccountantLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    stats: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

// Форма профиля
const form = ref({
    employee_name: props.accountant?.employee_name || '',
    email: props.accountant?.email || '',
    employee_phone: props.accountant?.employee_phone || ''
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

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getRoleName = (role) => {
    const roles = {
        'admin': 'Администратор',
        'doctor': 'Врач',
        'director': 'Директор',
        'accountant': 'Бухгалтер'
    };
    return roles[role] || role;
};

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
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
        const response = await axios.post('/api/accountant/upload-photo', formData, {
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            }
        });
        
        props.accountant.photo_url = response.data.photo_url;
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
        await axios.delete('/api/accountant/delete-photo');
        props.accountant.photo_url = null;
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
        employee_name: props.accountant?.employee_name || '',
        email: props.accountant?.email || '',
        employee_phone: props.accountant?.employee_phone || ''
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
        await axios.put('/api/accountant/profile', form.value);
        
        if (passwordForm.value.current_password || 
            passwordForm.value.new_password || 
            passwordForm.value.new_password_confirmation) {
            
            // Здесь можно добавить логику смены пароля
            await axios.post('/api/accountant/change-password', passwordForm.value);
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