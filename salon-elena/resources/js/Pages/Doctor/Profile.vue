<!-- resources/js/Pages/Doctor/Profile.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow max-w-md">
            <h2 class="text-2xl font-semibold mb-6">ПРОФИЛЬ</h2>
            
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
                    Сохранить
                </button>
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

const updateProfile = async () => {
    try {
        await axios.put('/api/doctor/profile', form.value);
        alert('Профиль обновлен');
    } catch (error) {
        console.error('Error updating profile:', error);
    }
};
</script>