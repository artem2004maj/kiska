<!-- resources/js/Pages/Admin/Dashboard.vue -->
<template>
    <AdminLayout 
        :admin="admin"
        :pageTitle="'ГЛАВНАЯ'"
        :criticalCount="stats?.critical_count || 0"
        :todayAppointments="stats?.today_appointments"
        :todayClients="stats?.today_clients"
    >
        <!-- Карточки статистики -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <!-- Записи сегодня -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Записи сегодня</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats?.today_appointments || 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Клиентов сегодня -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Клиентов сегодня</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats?.today_clients || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Активные записи -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Активные записи</p>
                        <p class="text-2xl font-bold text-purple-600">{{ stats?.active_appointments || 0 }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Критические материалы -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Критические материалы</p>
                        <p class="text-2xl font-bold text-red-600">{{ stats?.critical_count || 0 }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Быстрые действия -->
        <div class="mb-8">
            <div class="flex flex-wrap gap-4">
                <Link href="/admin/appointments" class="flex items-center gap-2 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Записать клиента
                </Link>
                <Link href="/admin/warehouse" class="flex items-center gap-2 px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7h-4.5M20 7v4.5M20 7l-6 6m-2-2l-4 4" />
                    </svg>
                    Управление складом
                </Link>
                <Link href="/admin/orders" class="flex items-center gap-2 px-6 py-3 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                    Поставки
                </Link>
            </div>
        </div>

        <!-- Ближайшие записи -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-6 border-b border-gray-100">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">БЛИЖАЙШИЕ ЗАПИСИ</h2>
                    <Link href="/admin/appointments" class="text-sm text-blue-500 hover:text-blue-600">
                        Все записи →
                    </Link>
                </div>
            </div>
            
            <div class="p-6">
                <div v-if="loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <p class="mt-2 text-gray-500">Загрузка...</p>
                </div>
                
                <div v-else-if="upcomingAppointments.length === 0" class="text-center py-8 text-gray-500">
                    Нет ближайших записей
                </div>
                
                <div v-else class="space-y-3">
                    <div v-for="appointment in upcomingAppointments" :key="appointment.id" 
                         class="flex justify-between items-center p-4 border rounded-lg hover:bg-gray-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <span class="text-blue-600 font-bold">{{ appointment.time }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ appointment.client_name }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ appointment.doctor_name }} • {{ appointment.service_name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400">{{ formatDate(appointment.date) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    admin: Object,
    stats: Object,
    upcomingAppointments: Array
});

const loading = ref(false);

const formatDate = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });
};

onMounted(() => {
    // Можно добавить дополнительную логику при загрузке
});
</script>

<style scoped>
/* Стили при необходимости */
</style>