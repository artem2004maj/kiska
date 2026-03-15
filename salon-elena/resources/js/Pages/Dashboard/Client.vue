<!-- ClientDashboard.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <!-- Background изображение (как в Welcome.vue) -->
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#FF2D20] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <!-- Шапка -->
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <!-- Логотип слева -->
                    <div class="flex justify-start">
                        <svg class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Здесь код вашего логотипа из Welcome.vue -->
                            <path d="M61.8548 14.6253..."/> <!-- Сократил для читаемости, вставьте полный код логотипа -->
                        </svg>
                    </div>

                    <!-- Навигация по центру -->
                    <div class="flex gap-6 lg:justify-center">
                        <Link href="/services" class="text-black hover:text-[#FF2D20] dark:text-white dark:hover:text-[#FF2D20] transition">
                            Услуги
                        </Link>
                        <Link href="/profile" class="text-black hover:text-[#FF2D20] dark:text-white dark:hover:text-[#FF2D20] transition">
                            Профиль
                        </Link>
                    </div>

                    <!-- Приветствие и иконка пользователя справа -->
                    <div class="flex items-center gap-3 justify-end">
                        <span class="text-black dark:text-white font-medium">Привет, Анна!</span>
                        <div class="flex size-10 items-center justify-center rounded-full bg-[#FF2D20]/10">
                            <svg class="size-5 text-[#FF2D20]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                    </div>
                </header>

                <!-- Основной контент -->
                <main class="mt-6 pb-16">
                    <!-- Уведомления -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">УВЕДОМЛЕНИЯ</h2>
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 text-black dark:text-white/70">
                                    <span class="w-2 h-2 bg-[#FF2D20] rounded-full"></span>
                                    <span>Напоминание: завтра в 15:00 запись</span>
                                </div>
                                <div class="flex items-center gap-3 text-black dark:text-white/70">
                                    <span class="w-2 h-2 bg-[#FF2D20] rounded-full"></span>
                                    <span>Ваша медицинская карта обновлена</span>
                                </div>
                                <div class="flex items-center gap-3 text-black dark:text-white/70">
                                    <span class="w-2 h-2 bg-[#FF2D20] rounded-full"></span>
                                    <span>Спасибо за визит! Оставьте отзыв</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Действующая запись -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ДЕЙСТВУЮЩАЯ ЗАПИСЬ</h2>
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <div class="grid gap-4">
                                <div class="flex items-center gap-8 text-black dark:text-white/70">
                                    <span class="font-medium min-w-24">Дата:</span>
                                    <span>15 марта 2026, 15:00</span>
                                </div>
                                <div class="flex items-center gap-8 text-black dark:text-white/70">
                                    <span class="font-medium min-w-24">Услуга:</span>
                                    <span>Чистка лица</span>
                                </div>
                                <div class="flex items-center gap-8 text-black dark:text-white/70">
                                    <span class="font-medium min-w-24">Врач:</span>
                                    <span>Иванова М.С.</span>
                                </div>
                                <div class="flex gap-4 mt-4">
                                    <button class="px-4 py-2 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition">
                                        Отменить запись
                                    </button>
                                    <button class="px-4 py-2 border border-[#FF2D20] text-[#FF2D20] rounded-md hover:bg-[#FF2D20]/10 transition">
                                        Перенести
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- История посещений -->
                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-semibold text-black dark:text-white">ИСТОРИЯ ПОСЕЩЕНИЙ</h2>
                            <Link href="/history" class="text-[#FF2D20] hover:underline">
                                Вся история →
                            </Link>
                        </div>
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Врач</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Стоимость</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                                        <td class="py-3 text-black dark:text-white/70">10.03</td>
                                        <td class="py-3 text-black dark:text-white/70">Чистка лица</td>
                                        <td class="py-3 text-black dark:text-white/70">Иванова М.С.</td>
                                        <td class="py-3 text-black dark:text-white/70">2500 ₽</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-black dark:text-white/70">25.02</td>
                                        <td class="py-3 text-black dark:text-white/70">Пилинг</td>
                                        <td class="py-3 text-black dark:text-white/70">Петрова А.В.</td>
                                        <td class="py-3 text-black dark:text-white/70">1800 ₽</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>

                <!-- Подвал (как в Welcome.vue) -->
                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>