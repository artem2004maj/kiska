<template>
    <ClientLayout :client="client">
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МЕДИЦИНСКАЯ КАРТА</h2>
            
            <!-- Информация о клиенте -->
            <div class="bg-[#14b8a6]/5 rounded-lg p-4 mb-6 flex items-center gap-4">
                <div class="w-16 h-16 bg-[#14b8a6]/20 rounded-full flex items-center justify-center">
                    <span class="text-2xl font-medium text-[#14b8a6]">
                        {{ getInitials(client.client_name) }}
                    </span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">{{ client.client_name }}</h3>
                    <p class="text-sm text-gray-500">Дата рождения: {{ formatDate(client.birth_date) }}</p>
                    <p class="text-sm text-gray-500">Телефон: {{ client.phone }}</p>
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-[#14b8a6]">{{ totalVisits }}</div>
                    <div class="text-sm text-gray-500">Всего посещений</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-[#14b8a6]">{{ uniqueDoctors }}</div>
                    <div class="text-sm text-gray-500">Специалистов</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-[#14b8a6]">{{ lastVisitDate }}</div>
                    <div class="text-sm text-gray-500">Последний визит</div>
                </div>
            </div>
            
            <!-- Записи медицинской карты -->
            <div v-if="records.length > 0" class="space-y-4">
                <div v-for="record in records" :key="record.record_id" 
                     class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="text-sm text-gray-500">
                                {{ formatDate(record.visit_date) }}
                            </span>
                            <p class="text-sm text-gray-600 mt-1">
                                Врач: {{ record.employee?.employee_name || 'Не указан' }}
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">
                            ID: {{ record.record_id }}
                        </span>
                    </div>
                    
                    <div class="grid gap-3 mt-3">
                        <div v-if="record.anamnesis" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 mb-1">Анамнез:</p>
                            <p class="text-sm text-gray-600">{{ record.anamnesis }}</p>
                        </div>
                        
                        <div v-if="record.diagnosis" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 mb-1">Диагноз:</p>
                            <p class="text-sm text-gray-600">{{ record.diagnosis }}</p>
                        </div>
                        
                        <div v-if="record.contraindications" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 mb-1">Противопоказания:</p>
                            <p class="text-sm text-gray-600">{{ record.contraindications }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12">
                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 text-lg">Медицинская карта пуста</p>
                <p class="text-gray-400 text-sm mt-2">Записи появятся после посещения врача</p>
            </div>
        </div>
    </ClientLayout>
</template>

<script setup>
import { computed } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    client: Object,
    records: Array
});

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const totalVisits = computed(() => {
    return props.records?.length || 0;
});

const uniqueDoctors = computed(() => {
    if (!props.records?.length) return 0;
    const doctors = new Set(props.records.map(r => r.employee?.employee_name).filter(Boolean));
    return doctors.size;
});

const lastVisitDate = computed(() => {
    if (!props.records?.length) return '—';
    const lastRecord = props.records[0];
    return formatDate(lastRecord.visit_date);
});
</script>