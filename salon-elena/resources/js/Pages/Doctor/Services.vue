<!-- resources/js/Pages/Doctor/Services.vue -->
<template>
    <DoctorLayout :doctor="doctor">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold mb-6">МОИ УСЛУГИ</h2>
            
            <div v-if="services.length > 0" class="grid gap-4">
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
                                  class="px-2 py-1 bg-gray-100 dark:bg-zinc-800 rounded-md text-xs">
                                {{ mat.name }} ({{ mat.pivot?.quantity }} {{ mat.unit }})
                            </span>
                        </div>
                    </div>
                    
                    <!-- Информация о том, что услуга закреплена за врачом -->
                    <div class="mt-3 pt-2 border-t border-gray-100 dark:border-zinc-700">
                        <p class="text-xs text-green-600 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Закреплена за вами
                        </p>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 text-lg">У вас пока нет закрепленных услуг</p>
                <p class="text-gray-400 text-sm mt-2">Обратитесь к администратору для назначения услуг</p>
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