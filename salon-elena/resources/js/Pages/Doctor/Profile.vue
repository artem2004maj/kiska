<!-- resources/js/Pages/Doctor/Profile.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 sm:p-6 shadow max-w-2xl mx-auto">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
                <!-- Левая колонка - Фото с превью -->
                <div class="md:col-span-1">
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center gap-2"
                                 @click="triggerFileInput">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-white text-sm font-medium">Изменить</span>
                            </div>
                            
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center overflow-hidden border-4 border-[#2A7F6E]/20 transition-transform group-hover:scale-105">
                                <img v-if="photoPreview" 
                                     :src="photoPreview" 
                                     alt="Preview"
                                     class="w-full h-full object-cover">
                                <img v-else-if="doctor?.photo_url" 
                                     :src="doctor.photo_url" 
                                     :alt="doctor.employee_name"
                                     class="w-full h-full object-cover">
                                <svg v-else class="w-16 h-16 sm:w-20 sm:h-20 text-[#2A7F6E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            
                            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
                        </div>
                        
                        <p class="mt-3 font-medium text-sm sm:text-base">{{ doctor?.employee_name }}</p>
                        
                        <div v-if="photoPreview" class="mt-3 flex gap-2">
                            <button @click="uploadPhoto" 
                                    class="px-4 py-1.5 bg-[#2A7F6E] text-white text-sm rounded-md hover:bg-[#2A7F6E]/90 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Сохранить
                            </button>
                            <button @click="cancelPhoto" 
                                    class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Отмена
                            </button>
                        </div>
                        
                        <div v-else-if="doctor?.photo_url" class="mt-3">
                            <button @click="deletePhoto" 
                                    class="px-4 py-1.5 bg-red-500 text-white text-sm rounded-md hover:bg-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить фото
                            </button>
                        </div>
                        
                        <div v-if="uploadProgress > 0" class="mt-3 w-full max-w-[200px]">
                            <div class="flex justify-between text-xs mb-1">
                                <span>Загрузка...</span>
                                <span>{{ uploadProgress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-[#2A7F6E] h-2 rounded-full transition-all duration-300"
                                     :style="{ width: uploadProgress + '%' }"></div>
                            </div>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-400 text-center">
                            PNG, JPG, GIF до 2MB<br>
                            Рекомендуемый размер: 400x400
                        </p>
                    </div>
                </div>
                
                <!-- Правая колонка - Форма профиля -->
                <div class="md:col-span-2">
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Имя</label>
                            <input type="text" v-model="form.employee_name" required
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" v-model="form.email" required
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Телефон</label>
                            <input type="tel" 
                                   v-model="phoneDisplay"
                                   @blur="formatPhoneOnBlur"
                                   @focus="showRawPhone"
                                   placeholder="+7 999 999-99-99"
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-400">Формат: +7 999 999-99-99</p>
                            <p v-if="phoneError" class="mt-1 text-xs text-red-500">
                                {{ phoneError }}
                            </p>
                        </div>
                        <button type="submit" :disabled="loading"
                                class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90 disabled:opacity-50">
                            {{ loading ? 'Сохранение...' : 'Сохранить изменения' }}
                        </button>
                    </form>
                </div>
            </div>
            
            <Transition name="fade">
                <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                    {{ errorMessage }}
                </div>
            </Transition>
            
            <Transition name="fade">
                <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                    {{ successMessage }}
                </div>
            </Transition>
        </div>
    </DoctorLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    doctor: Object
});

// Форма профиля (хранит данные в формате +7XXXXXXXXXX)
const form = ref({
    employee_name: props.doctor?.employee_name || '',
    email: props.doctor?.email || '',
    employee_phone: props.doctor?.employee_phone || ''
});

// Для отображения телефона в форматированном виде
const phoneDisplay = ref('');
const phoneError = ref('');

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Фото
const fileInput = ref(null);
const photoPreview = ref(null);
const selectedFile = ref(null);
const uploadProgress = ref(0);

// Форматирование телефона для отображения
const formatPhoneForDisplay = (phone) => {
    if (!phone) return '';
    
    // Очищаем от всех нецифровых символов
    let cleaned = phone.toString().replace(/\D/g, '');
    
    // Если номер начинается с 8, заменяем на 7
    if (cleaned.length > 0 && cleaned[0] === '8') {
        cleaned = '7' + cleaned.substring(1);
    }
    
    // Форматируем как +7 999 999-99-99
    if (cleaned.length >= 1) {
        let formatted = '+' + cleaned[0];
        if (cleaned.length > 1) formatted += ' ' + cleaned.substring(1, 4);
        if (cleaned.length > 4) formatted += ' ' + cleaned.substring(4, 7);
        if (cleaned.length > 7) formatted += '-' + cleaned.substring(7, 9);
        if (cleaned.length > 9) formatted += '-' + cleaned.substring(9, 11);
        return formatted;
    }
    
    return phone;
};

// Получить "сырой" номер (только цифры)
const getRawPhone = (phone) => {
    if (!phone) return '';
    return phone.toString().replace(/\D/g, '');
};

// При фокусе на поле - показываем только цифры
const showRawPhone = () => {
    if (phoneDisplay.value) {
        const raw = getRawPhone(phoneDisplay.value);
        phoneDisplay.value = raw;
    }
};

// При потере фокуса - форматируем
const formatPhoneOnBlur = () => {
    if (phoneDisplay.value) {
        const formatted = formatPhoneForDisplay(phoneDisplay.value);
        phoneDisplay.value = formatted;
        
        // Обновляем form.employee_phone (сохраняем в формате +7XXXXXXXXXX)
        const raw = getRawPhone(phoneDisplay.value);
        if (raw.length === 11 && raw[0] === '7') {
            form.value.employee_phone = '+' + raw;
        } else if (raw.length === 11 && raw[0] === '8') {
            form.value.employee_phone = '+7' + raw.substring(1);
        } else if (raw.length === 10) {
            form.value.employee_phone = '+7' + raw;
        } else {
            form.value.employee_phone = raw;
        }
        
        // Валидация
        const cleanForCheck = raw;
        if (cleanForCheck && cleanForCheck.length !== 11) {
            phoneError.value = 'Номер телефона должен содержать 11 цифр (например: 79991234567)';
        } else if (cleanForCheck && !/^[78][0-9]{10}$/.test(cleanForCheck)) {
            phoneError.value = 'Номер телефона должен начинаться с 7 или 8';
        } else {
            phoneError.value = '';
        }
    } else {
        form.value.employee_phone = null;
        phoneError.value = '';
    }
};

// Инициализация отображения телефона
const initPhoneDisplay = () => {
    if (form.value.employee_phone) {
        phoneDisplay.value = formatPhoneForDisplay(form.value.employee_phone);
    }
};

// Следим за изменением form.employee_phone извне
watch(() => form.value.employee_phone, () => {
    if (!document.activeElement || document.activeElement !== document.querySelector('input[type="tel"]')) {
        initPhoneDisplay();
    }
});

// Методы для фото
const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Файл слишком большой. Максимальный размер 2MB';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Пожалуйста, выберите изображение';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    selectedFile.value = file;
    errorMessage.value = '';
    
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
        const response = await axios.post('/api/doctor/upload-photo', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            }
        });
        
        props.doctor.photo_url = response.data.photo_url;
        photoPreview.value = null;
        selectedFile.value = null;
        uploadProgress.value = 0;
        
        successMessage.value = 'Фото успешно загружено!';
        setTimeout(() => successMessage.value = '', 3000);
        
    } catch (error) {
        console.error('Error uploading photo:', error);
        errorMessage.value = error.response?.data?.message || 'Ошибка при загрузке фото';
        setTimeout(() => errorMessage.value = '', 3000);
    }
};

const deletePhoto = async () => {
    if (!confirm('Вы уверены, что хотите удалить фото?')) return;
    
    try {
        await axios.delete('/api/doctor/delete-photo');
        
        props.doctor.photo_url = null;
        photoPreview.value = null;
        selectedFile.value = null;
        
        successMessage.value = 'Фото удалено';
        setTimeout(() => successMessage.value = '', 3000);
        
    } catch (error) {
        console.error('Error deleting photo:', error);
        errorMessage.value = 'Ошибка при удалении фото';
        setTimeout(() => errorMessage.value = '', 3000);
    }
};

const cancelPhoto = () => {
    photoPreview.value = null;
    selectedFile.value = null;
    fileInput.value.value = '';
    errorMessage.value = '';
};

const updateProfile = async () => {
    if (phoneError.value) {
        errorMessage.value = phoneError.value;
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    loading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    
    try {
        await axios.put('/api/doctor/profile', form.value);
        successMessage.value = 'Профиль обновлен';
        setTimeout(() => successMessage.value = '', 3000);
    } catch (error) {
        console.error('Error updating profile:', error);
        errorMessage.value = error.response?.data?.message || 'Ошибка при обновлении профиля';
        setTimeout(() => errorMessage.value = '', 3000);
    } finally {
        loading.value = false;
    }
};

// Инициализация
initPhoneDisplay();
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