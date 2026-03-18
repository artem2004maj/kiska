<!-- resources/js/Pages/Doctor/Profile.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 sm:p-6 shadow max-w-2xl mx-auto">
            <h2 class="text-xl sm:text-2xl font-semibold mb-4 sm:mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
                <!-- Левая колонка - Фото с превью -->
                <div class="md:col-span-1">
                    <div class="flex flex-col items-center">
                        <!-- Область фото с превью -->
                        <div class="relative group">
                            <!-- Затемнение при наведении -->
                            <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-center justify-center gap-2"
                                 @click="triggerFileInput">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-white text-sm font-medium">Изменить</span>
                            </div>
                            
                            <!-- Само фото/превью -->
                            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center overflow-hidden border-4 border-[#2A7F6E]/20 transition-transform group-hover:scale-105">
                                <!-- Превью нового фото -->
                                <img v-if="photoPreview" 
                                     :src="photoPreview" 
                                     alt="Preview"
                                     class="w-full h-full object-cover">
                                <!-- Текущее фото -->
                                <img v-else-if="doctor?.photo_url" 
                                     :src="doctor.photo_url" 
                                     :alt="doctor.employee_name"
                                     class="w-full h-full object-cover">
                                <!-- Заглушка -->
                                <svg v-else class="w-16 h-16 sm:w-20 sm:h-20 text-[#2A7F6E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            
                            <!-- Скрытый input для файла -->
                            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
                        </div>
                        
                        <!-- Имя под фото -->
                        <p class="mt-3 font-medium text-sm sm:text-base">{{ doctor?.employee_name }}</p>
                        
                        <!-- Панель управления фото (появляется при выборе файла) -->
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
                        
                        <!-- Кнопка удаления (если есть фото и нет превью) -->
                        <div v-else-if="doctor?.photo_url" class="mt-3">
                            <button @click="deletePhoto" 
                                    class="px-4 py-1.5 bg-red-500 text-white text-sm rounded-md hover:bg-red-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить фото
                            </button>
                        </div>
                        
                        <!-- Прогресс загрузки -->
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
                        
                        <!-- Подсказка -->
                        <p class="mt-2 text-xs text-gray-400 text-center">
                            PNG, JPG, GIF до 2MB<br>
                            Рекомендуемый размер: 400x400
                        </p>
                    </div>
                </div>
                
                <!-- Правая колонка - Форма профиля -->
                <div class="md:col-span-2">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Имя</label>
                            <input type="text" v-model="form.employee_name" 
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" v-model="form.email" 
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Телефон</label>
                            <input type="tel" v-model="form.employee_phone" 
                                   class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-[#2A7F6E] focus:border-transparent">
                        </div>
                        <button @click="updateProfile" 
                                class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90">
                            Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Уведомление об ошибке -->
            <Transition name="fade">
                <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                    {{ errorMessage }}
                </div>
            </Transition>
            
            <!-- Уведомление об успехе -->
            <Transition name="fade">
                <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                    {{ successMessage }}
                </div>
            </Transition>
        </div>
    </DoctorLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    doctor: Object
});

const form = ref({
    employee_name: props.doctor?.employee_name || '',
    email: props.doctor?.email || '',
    employee_phone: props.doctor?.employee_phone || ''
});

// Фото
const fileInput = ref(null);
const photoPreview = ref(null);
const selectedFile = ref(null);
const uploadProgress = ref(0);
const errorMessage = ref('');
const successMessage = ref('');

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    // Проверка размера файла (2MB)
    if (file.size > 2 * 1024 * 1024) {
        errorMessage.value = 'Файл слишком большой. Максимальный размер 2MB';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    // Проверка типа файла
    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Пожалуйста, выберите изображение';
        setTimeout(() => errorMessage.value = '', 3000);
        return;
    }
    
    selectedFile.value = file;
    errorMessage.value = '';
    
    // Создаем превью
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
        
        // Обновляем данные врача
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
    try {
        await axios.put('/api/doctor/profile', form.value);
        successMessage.value = 'Профиль обновлен';
        setTimeout(() => successMessage.value = '', 3000);
    } catch (error) {
        console.error('Error updating profile:', error);
        errorMessage.value = 'Ошибка при обновлении профиля';
        setTimeout(() => errorMessage.value = '', 3000);
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