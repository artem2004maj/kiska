<!-- resources/js/Pages/Director/Dashboard.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'ГЛАВНАЯ'"
    >
        <div class="space-y-6">
            <!-- Приветствие -->
            <div class="bg-gradient-to-r from-[#8b5cf6] to-[#6d28d9] rounded-lg p-6 text-white">
                <h2 class="text-2xl font-bold mb-2">Добро пожаловать, {{ director?.employee_name }}!</h2>
                <p class="text-purple-100">Панель управления салоном красоты "ELENA"</p>
            </div>
            
            <!-- Уведомления о новых поставках -->
            <div v-if="pendingOrders.length > 0" class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800 dark:text-yellow-300">Новые заказы на поставку</h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                            Требуется ваше подтверждение: {{ pendingOrders.length }} заказ(ов) ожидают рассмотрения
                        </p>
                        <Link href="/director/supply-control" 
                              class="mt-2 inline-block text-sm text-yellow-800 dark:text-yellow-300 hover:underline">
                            Перейти к поставкам →
                        </Link>
                    </div>
                </div>
            </div>
            
            <!-- Быстрые ссылки -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link href="/director/analytics" 
                      class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow hover:shadow-lg transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#8b5cf6]/10 flex items-center justify-center group-hover:bg-[#8b5cf6]/20 transition">
                            <span class="text-2xl">📊</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Аналитика</h3>
                            <p class="text-sm text-gray-500">Просмотр отчетов и статистики</p>
                        </div>
                    </div>
                </Link>
                
                <Link href="/director/services" 
                      class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow hover:shadow-lg transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#8b5cf6]/10 flex items-center justify-center group-hover:bg-[#8b5cf6]/20 transition">
                            <span class="text-2xl">💅</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Услуги</h3>
                            <p class="text-sm text-gray-500">Управление услугами</p>
                        </div>
                    </div>
                </Link>
                
                <Link href="/director/staff" 
                      class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow hover:shadow-lg transition group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-[#8b5cf6]/10 flex items-center justify-center group-hover:bg-[#8b5cf6]/20 transition">
                            <span class="text-2xl">👥</span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Персонал</h3>
                            <p class="text-sm text-gray-500">Управление сотрудниками</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </DirectorLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';

const props = defineProps({
    director: Object,
    pendingOrders: Array
});
</script>