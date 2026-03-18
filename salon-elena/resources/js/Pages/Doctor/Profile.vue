<!-- resources/js/Pages/Doctor/Profile.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow max-w-2xl mx-auto">
            <h2 class="text-2xl font-semibold mb-6">ПРОФИЛЬ</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Левая колонка - Фото -->
                <div class="md:col-span-1">
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <!-- Фото или заглушка -->
                            <div class="w-40 h-40 rounded-full bg-[#2A7F6E]/10 flex items-center justify-center overflow-hidden border-4 border-[#2A7F6E]/20">
                                <img v-if="photoPreview || doctor?.photo_url" 
                                     :src="photoPreview || doctor?.photo_url" 
                                     :alt="doctor.employee_name"
                                     class="w-full h-full object-cover">
                                <svg v-else class="w-20 h-20 text-[#2A7F6E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            
                            <!-- Кнопка загрузки (появляется при наведении) -->
                            <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                 @click="triggerFileInput">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            
                            <!-- Скрытый input для файла -->
                            <input type="file" ref="fileInput" @change="handleFileSelect" accept="image/*" class="hidden">
                        </div>
                        
                        <!-- Кнопки управления фото -->
                        <div v-if="photoPreview || doctor?.photo_url" class="flex gap-2 mt-4">
                            <button @click="uploadPhoto" 
                                    class="px-3 py-1 bg-[#2A7F6E] text-white text-sm rounded-md hover:bg-[#2A7F6E]/90"
                                    :disabled="!photoPreview">
                                Сохранить
                            </button>
                            <button @click="deletePhoto" 
                                    class="px-3 py-1 bg-red-500 text-white text-sm rounded-md hover:bg-red-600">
                                Удалить
                            </button>
                            <button v-if="photoPreview" @click="cancelPhoto" 
                                    class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded-md hover:bg-gray-100">
                                Отмена
                            </button>
                        </div>
                        
                        <p v-if="uploadProgress > 0" class="mt-2 text-sm text-gray-600">
                            Загрузка: {{ uploadProgress }}%
                        </p>
                    </div>
                </div>
                
                <!-- Правая колонка - Форма профиля -->
                <div class="md:col-span-2">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Имя</label>
                            <input type="text" v-model="form.employee_name" 
                                   class="w-full px-4 py-2 border rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" v-model="form.email" 
                                   class="w-full px-4 py-2 border rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Телефон</label>
                            <input type="tel" v-model="form.employee_phone" 
                                   class="w-full px-4 py-2 border rounded-md">
                        </div>
                        <button @click="updateProfile" 
                                class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90">
                            Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
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

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    selectedFile.value = file;
    
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
        
        alert('Фото успешно загружено');
    } catch (error) {
        console.error('Error uploading photo:', error);
        alert('Ошибка при загрузке фото');
    }
};

const deletePhoto = async () => {
    if (!confirm('Вы уверены, что хотите удалить фото?')) return;
    
    try {
        await axios.delete('/api/doctor/delete-photo');
        
        props.doctor.photo_url = null;
        photoPreview.value = null;
        selectedFile.value = null;
        
        alert('Фото удалено');
    } catch (error) {
        console.error('Error deleting photo:', error);
        alert('Ошибка при удалении фото');
    }
};

const cancelPhoto = () => {
    photoPreview.value = null;
    selectedFile.value = null;
    fileInput.value.value = '';
};

const updateProfile = async () => {
    try {
        await axios.put('/api/doctor/profile', form.value);
        alert('Профиль обновлен');
    } catch (error) {
        console.error('Error updating profile:', error);
    }
};
</script>