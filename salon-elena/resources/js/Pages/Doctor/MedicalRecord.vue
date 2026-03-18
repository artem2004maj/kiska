<!-- resources/js/Pages/Doctor/MedicalRecord.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-semibold">МЕДИЦИНСКАЯ КАРТА</h2>
                <button @click="goBack" class="px-4 py-2 border rounded-md hover:bg-gray-100">
                    Назад
                </button>
            </div>
            
            <!-- Информация о пациенте -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-lg">{{ patient.client_name }}</h3>
                <p class="text-sm text-gray-500">📞 {{ patient.phone }}</p>
                <p class="text-sm text-gray-500">📅 {{ patient.birth_date }}</p>
            </div>
            
            <!-- История записей -->
            <div class="space-y-4">
                <h3 class="font-semibold">История посещений:</h3>
                
                <div v-for="record in records" :key="record.record_id" 
                     class="border rounded-lg p-4">
                    <p class="text-sm text-gray-500">{{ formatDate(record.visit_date) }}</p>
                    <p class="mt-2"><span class="font-medium">Анамнез:</span> {{ record.anamnesis }}</p>
                    <p><span class="font-medium">Диагноз:</span> {{ record.diagnosis }}</p>
                    <p><span class="font-medium">Противопоказания:</span> {{ record.contraindications }}</p>
                    <p class="text-sm text-gray-500 mt-2">Врач: {{ record.employee?.employee_name }}</p>
                </div>
                
                <div v-if="records.length === 0" class="text-center py-8 text-gray-500">
                    Нет записей в медицинской карте
                </div>
            </div>
            
            <!-- Форма новой записи -->
            <div class="mt-8 pt-6 border-t">
                <h3 class="font-semibold mb-4">Новая запись</h3>
                <div class="space-y-4">
                    <textarea v-model="newRecord.anamnesis" placeholder="Анамнез"
                              class="w-full px-4 py-2 border rounded-md"></textarea>
                    <textarea v-model="newRecord.diagnosis" placeholder="Диагноз"
                              class="w-full px-4 py-2 border rounded-md"></textarea>
                    <textarea v-model="newRecord.contraindications" placeholder="Противопоказания"
                              class="w-full px-4 py-2 border rounded-md"></textarea>
                    <button @click="saveRecord" 
                            class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90">
                        Сохранить запись
                    </button>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    doctor: Object,
    patient: Object,
    records: Array,
    appointments: Array
});

const newRecord = ref({
    anamnesis: '',
    diagnosis: '',
    contraindications: ''
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU');
};

const goBack = () => {
    router.get('/doctor/patients');
};

const saveRecord = async () => {
    try {
        await axios.post(`/api/doctor/medical-records/${props.patient.client_id}`, newRecord.value);
        alert('Запись сохранена');
        router.reload();
    } catch (error) {
        console.error('Error saving record:', error);
    }
};
</script>