<!-- resources/js/Pages/Doctor/Patients.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold mb-6">МОИ ПАЦИЕНТЫ</h2>
            
            <!-- Поиск -->
            <div class="mb-6">
                <input type="text" v-model="searchQuery" @input="searchPatients"
                       placeholder="Поиск по имени или телефону..."
                       class="w-full px-4 py-2 border rounded-md">
            </div>
            
            <!-- Список пациентов -->
            <div class="space-y-4">
                <div v-for="patient in filteredPatients" :key="patient.client_id" 
                     class="border rounded-lg p-4 hover:shadow-md transition cursor-pointer"
                     @click="viewPatient(patient.client_id)">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="font-semibold">{{ patient.client_name }}</h3>
                            <p class="text-sm text-gray-500">📞 {{ patient.phone }}</p>
                            <p class="text-sm text-gray-500">📅 {{ patient.birth_date }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400">
                                Последний визит: {{ patient.last_visit || 'Нет' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div v-if="filteredPatients.length === 0" class="text-center py-8 text-gray-500">
                    Пациенты не найдены
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    doctor: Object,
    patients: Array
});

const searchQuery = ref('');

const filteredPatients = computed(() => {
    if (!searchQuery.value) return props.patients;
    const query = searchQuery.value.toLowerCase();
    return props.patients.filter(p => 
        p.client_name.toLowerCase().includes(query) ||
        p.phone.includes(query)
    );
});

const viewPatient = (clientId) => {
    router.get(`/doctor/medical-records/${clientId}`);
};
</script>