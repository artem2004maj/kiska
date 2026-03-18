<!-- resources/js/Pages/Doctor/Services.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold mb-6">МОИ УСЛУГИ</h2>
            
            <div class="grid gap-4">
                <div v-for="service in services" :key="service.service_id" 
                     class="border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold">{{ service.service_name }}</h3>
                            <p class="text-sm text-gray-500">{{ service.service_category }}</p>
                        </div>
                        <p class="text-[#2A7F6E] font-semibold">{{ service.default_price }} ₽</p>
                    </div>
                    
                    <div v-if="service.materials?.length" class="mt-3">
                        <p class="text-sm font-medium mb-2">Необходимые материалы:</p>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="mat in service.materials" :key="mat.material_id"
                                  class="px-2 py-1 bg-gray-100 rounded-md text-xs">
                                {{ mat.name }} ({{ mat.pivot?.quantity }} {{ mat.unit }})
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<script setup>
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

defineProps({
    doctor: Object,
    services: Array
});
</script>