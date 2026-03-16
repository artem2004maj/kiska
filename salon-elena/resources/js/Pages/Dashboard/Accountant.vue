<!-- AccountantDashboard.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <!-- Background изображение (как в Welcome.vue) -->
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#2563eb] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <!-- Шапка с сайдбаром и основным контентом -->
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар (Левое меню) -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <!-- Логотип в сайдбаре -->
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#3b82f6] lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M61.8548 14.6253..."/> <!-- Ваш логотип -->
                                </svg>
                            </div>
                            
                            <!-- Навигация сайдбара -->
                            <nav class="space-y-1">
                                <Link 
                                    href="/accountant" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/accountant' ? 'bg-[#3b82f6] text-white' : 'text-black dark:text-white/70 hover:bg-[#3b82f6]/10']"
                                >
                                    <span class="text-xl">🏠</span>
                                    <span>Главная</span>
                                    <span v-if="unpaidCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ unpaidCount }}</span>
                                </Link>
                                
                                <Link 
                                    href="/accountant/payments" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/accountant/payments' ? 'bg-[#3b82f6] text-white' : 'text-black dark:text-white/70 hover:bg-[#3b82f6]/10']"
                                >
                                    <span class="text-xl">💰</span>
                                    <span>Оплаты</span>
                                </Link>
                                
                                <Link 
                                    href="/accountant/warehouse" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/accountant/warehouse' ? 'bg-[#3b82f6] text-white' : 'text-black dark:text-white/70 hover:bg-[#3b82f6]/10']"
                                >
                                    <span class="text-xl">📦</span>
                                    <span>Склад</span>
                                    <span v-if="criticalStockCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ criticalStockCount }}</span>
                                </Link>
                                
                                <Link 
                                    href="/accountant/salary" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/accountant/salary' ? 'bg-[#3b82f6] text-white' : 'text-black dark:text-white/70 hover:bg-[#3b82f6]/10']"
                                >
                                    <span class="text-xl">💵</span>
                                    <span>Зарплата</span>
                                </Link>
                            </nav>

                            <!-- Быстрая статистика в сайдбаре -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-black dark:text-white/70">Выручка сегодня:</span>
                                        <span class="font-medium text-[#22c55e]">45 200 ₽</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-black dark:text-white/70">К оплате:</span>
                                        <span class="font-medium text-[#3b82f6]">12 500 ₽</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <!-- Шапка -->
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <!-- Заголовок в зависимости от страницы -->
                                <h1 class="text-3xl font-semibold text-black dark:text-white">
                                    {{ pageTitle }}
                                </h1>
                                
                                <!-- Правая часть шапки -->
                                <div class="flex items-center gap-4">
                                    <!-- Поиск -->
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            placeholder="Поиск..." 
                                            class="pl-10 pr-4 py-2 bg-white dark:bg-zinc-900 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 focus:outline-none focus:ring-2 focus:ring-[#3b82f6]"
                                            v-model="searchQuery"
                                        />
                                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Уведомления -->
                                    <div class="relative">
                                        <button @click="toggleNotifications" class="relative">
                                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                            <span v-if="notificationsCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                                {{ notificationsCount }}
                                            </span>
                                        </button>
                                    </div>
                                    
                                    <!-- Профиль с выпадающим меню -->
                                    <div class="relative" ref="profileMenu">
                                        <button @click="toggleProfileMenu" class="flex items-center gap-3 cursor-pointer">
                                            <span class="text-black dark:text-white font-medium">Елена Петрова</span>
                                            <div class="flex size-10 items-center justify-center rounded-full bg-[#3b82f6]/10">
                                                <svg class="size-5 text-[#3b82f6]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                            </div>
                                        </button>
                                        
                                        <!-- Выпадающее меню -->
                                        <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50">
                                            <Link href="/accountant/profile" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#3b82f6]/10">Профиль</Link>
                                            <Link href="/accountant/settings" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#3b82f6]/10">Настройки</Link>
                                            <hr class="my-1 border-gray-200 dark:border-zinc-700" />
                                            <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Выйти</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <!-- Контент в зависимости от текущего маршрута -->
                        <div v-if="currentRoute === '/accountant'">
                            <!-- Быстрые действия -->
                            <div class="mb-8">
                                <div class="flex gap-4">
                                    <button @click="openInventory" class="flex items-center gap-2 px-6 py-3 bg-[#3b82f6] text-white rounded-lg hover:bg-[#3b82f6]/90 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        Провести инвентаризацию
                                    </button>
                                    <button @click="openSalaryCalculation" class="flex items-center gap-2 px-6 py-3 bg-[#22c55e] text-white rounded-lg hover:bg-[#22c55e]/90 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Рассчитать ЗП
                                    </button>
                                </div>
                            </div>

                            <!-- Лента событий -->
                            <div class="mb-8">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">ЛЕНТА СОБЫТИЙ</h2>
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="space-y-4">
                                        <div v-for="(event, index) in events" :key="index" 
                                             class="flex items-start gap-4 p-3 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-lg transition">
                                            <div class="w-2 h-2 mt-2 rounded-full" :class="event.type === 'payment' ? 'bg-[#22c55e]' : 'bg-[#3b82f6]'"></div>
                                            <div class="flex-1">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-black dark:text-white">{{ event.description }}</p>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ event.time }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span class="font-medium text-[#22c55e]">{{ event.amount }}</span>
                                                        <button v-if="event.type === 'payment'" 
                                                                @click="acceptPayment(event)"
                                                                class="px-3 py-1 bg-[#22c55e] text-white text-sm rounded-md hover:bg-[#22c55e]/90 transition">
                                                            Принять оплату
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Краткая информация о складе -->
                            <div>
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-2xl font-semibold text-black dark:text-white">КРИТИЧЕСКИЕ ЗАПАСЫ</h2>
                                    <Link href="/accountant/warehouse" class="text-[#3b82f6] hover:underline">
                                        Весь склад →
                                    </Link>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div v-for="item in criticalMaterials" :key="item.id" 
                                         class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 border-l-4 border-red-500">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="font-semibold text-black dark:text-white">{{ item.name }}</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Остаток: {{ item.quantity }} {{ item.unit }}</p>
                                            </div>
                                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Критично</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Оплаты -->
                        <div v-else-if="currentRoute === '/accountant/payments'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ОПЛАТЫ УСЛУГ</h2>
                            
                            <!-- Фильтры -->
                            <div class="flex gap-4 mb-6">
                                <select class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" v-model="paymentFilter">
                                    <option value="all">Все оплаты</option>
                                    <option value="pending">Ожидают оплаты</option>
                                    <option value="completed">Оплачено</option>
                                </select>
                                <input type="date" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" v-model="paymentDate" />
                            </div>

                            <!-- Таблица оплат -->
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Клиент</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Сумма</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in payments" :key="payment.id" class="border-b border-gray-200 dark:border-zinc-700">
                                        <td class="py-3 text-black dark:text-white/70">{{ payment.date }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ payment.client }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ payment.service }}</td>
                                        <td class="py-3 text-black dark:text-white/70 font-medium">{{ payment.amount }}</td>
                                        <td class="py-3">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                  :class="payment.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                                {{ payment.status === 'completed' ? 'Оплачено' : 'Ожидает' }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <button v-if="payment.status === 'pending'"
                                                    @click="processPayment(payment)"
                                                    class="px-3 py-1 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition text-sm">
                                                Принять оплату
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Страница Склад -->
                        <div v-else-if="currentRoute === '/accountant/warehouse'" class="space-y-6">
                            <!-- Основная таблица склада -->
                            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">СКЛАДСКОЙ УЧЕТ</h2>
                                
                                <!-- Поиск и фильтры -->
                                <div class="flex gap-4 mb-6">
                                    <input type="text" placeholder="Поиск материалов..." 
                                           class="flex-1 px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md"
                                           v-model="materialSearch" />
                                    <select class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" v-model="materialFilter">
                                        <option value="all">Все материалы</option>
                                        <option value="critical">Критичные</option>
                                        <option value="low">Мало</option>
                                        <option value="normal">Норма</option>
                                    </select>
                                </div>

                                <!-- Таблица материалов -->
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                                            <th class="text-left py-3 text-black dark:text-white font-medium">Наименование</th>
                                            <th class="text-left py-3 text-black dark:text-white font-medium">Остаток</th>
                                            <th class="text-left py-3 text-black dark:text-white font-medium">Ед. изм.</th>
                                            <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="material in filteredMaterials" :key="material.id" class="border-b border-gray-200 dark:border-zinc-700">
                                            <td class="py-3 text-black dark:text-white/70">{{ material.name }}</td>
                                            <td class="py-3 text-black dark:text-white/70">{{ material.quantity }}</td>
                                            <td class="py-3 text-black dark:text-white/70">{{ material.unit }}</td>
                                            <td class="py-3">
                                                <span class="px-2 py-1 text-xs rounded-full" 
                                                      :class="{
                                                          'bg-red-100 text-red-800': material.status === 'critical',
                                                          'bg-yellow-100 text-yellow-800': material.status === 'low',
                                                          'bg-green-100 text-green-800': material.status === 'normal'
                                                      }">
                                                    {{ material.status === 'critical' ? 'Критично' : material.status === 'low' ? 'Мало' : 'Норма' }}
                                                </span>
                                            </td>
                                            <td class="py-3">
                                                <button @click="addToOrder(material)" 
                                                        class="text-[#3b82f6] hover:underline text-sm"
                                                        :disabled="material.inOrder">
                                                    {{ material.inOrder ? 'В заказе' : 'В заказ' }}
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Блок закупки -->
                            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                <h3 class="text-xl font-semibold text-black dark:text-white mb-4">ОФОРМЛЕНИЕ ЗАКУПКИ</h3>
                                
                                <!-- Выбор поставщика -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">Поставщик</label>
                                    <select class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" v-model="selectedSupplier">
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Корзина заказа -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">Корзина</label>
                                    <div class="border border-gray-200 dark:border-zinc-700 rounded-lg">
                                        <div v-if="orderItems.length === 0" class="p-4 text-center text-gray-500">
                                            Корзина пуста
                                        </div>
                                        <div v-else v-for="(item, index) in orderItems" :key="index" 
                                             class="flex justify-between items-center p-3 border-b border-gray-200 dark:border-zinc-700 last:border-0">
                                            <div>
                                                <span class="text-black dark:text-white">{{ item.name }}</span>
                                                <span class="text-sm text-gray-500 ml-2">{{ item.quantity }} {{ item.unit }}</span>
                                            </div>
                                            <button @click="removeFromOrder(index)" class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Кнопка оформления -->
                                <button @click="submitOrder" 
                                        :disabled="orderItems.length === 0 || !selectedSupplier"
                                        class="w-full px-4 py-3 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Оформить заявку на закупку
                                </button>

                                <!-- Статус заявки -->
                                <div v-if="lastOrderStatus" class="mt-4 p-3 bg-yellow-100 text-yellow-800 rounded-lg text-center">
                                    {{ lastOrderStatus }}
                                </div>
                            </div>
                        </div>

                        <!-- Страница Зарплата -->
                        <div v-else-if="currentRoute === '/accountant/salary'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">РАСЧЕТ ЗАРАБОТНОЙ ПЛАТЫ</h2>
                            
                            <!-- Период -->
                            <div class="flex gap-4 mb-6">
                                <select class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" v-model="salaryMonth">
                                    <option value="2026-03">Март 2026</option>
                                    <option value="2026-02">Февраль 2026</option>
                                    <option value="2026-01">Январь 2026</option>
                                </select>
                                <button @click="calculateSalary" class="px-4 py-2 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition">
                                    Рассчитать
                                </button>
                            </div>

                            <!-- Таблица зарплаты -->
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-zinc-700">
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Сотрудник</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Должность</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Отработано часов</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Ставка</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Начислено</th>
                                        <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="employee in employees" :key="employee.id" class="border-b border-gray-200 dark:border-zinc-700">
                                        <td class="py-3 text-black dark:text-white/70">{{ employee.name }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ employee.position }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ employee.hours }}</td>
                                        <td class="py-3 text-black dark:text-white/70">{{ employee.rate }} ₽/час</td>
                                        <td class="py-3 text-black dark:text-white/70 font-medium">{{ employee.salary }} ₽</td>
                                        <td class="py-3">
                                            <span class="px-2 py-1 text-xs rounded-full" 
                                                  :class="employee.paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                                {{ employee.paid ? 'Выплачено' : 'Начислено' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="border-t border-gray-200 dark:border-zinc-700">
                                        <td colspan="4" class="py-3 text-right font-medium text-black dark:text-white">Итого:</td>
                                        <td class="py-3 font-medium text-[#22c55e]">{{ totalSalary }} ₽</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Кнопка выплаты -->
                            <div class="mt-6 flex justify-end">
                                <button @click="processSalary" class="px-6 py-3 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition">
                                    Выплатить зарплату
                                </button>
                            </div>
                        </div>
                    </main>
                </div>

                <!-- Подвал -->
                <footer class="py-16 text-center text-sm text-black dark:text-white/70">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

// Текущий маршрут
const currentRoute = computed(() => {
    return usePage().url;
});

// Заголовок страницы
const pageTitle = computed(() => {
    switch(currentRoute.value) {
        case '/accountant': return 'ГЛАВНАЯ';
        case '/accountant/payments': return 'ОПЛАТЫ';
        case '/accountant/warehouse': return 'СКЛАДСКОЙ УЧЕТ';
        case '/accountant/salary': return 'ЗАРАБОТНАЯ ПЛАТА';
        default: return 'БУХГАЛТЕРИЯ';
    }
});

// Поиск
const searchQuery = ref('');

// Уведомления
const notificationsCount = ref(3);
const showNotifications = ref(false);

// Профиль
const showProfileMenu = ref(false);
const profileMenu = ref(null);

// Данные для главной страницы
const unpaidCount = ref(2);
const criticalStockCount = ref(3);

const events = ref([
    { type: 'payment', description: 'Иванова А.А. оказана услуга "Чистка лица"', time: 'Только что', amount: '2500 ₽' },
    { type: 'payment', description: 'Петров П. завершил прием', time: '5 мин назад', amount: '3500 ₽' },
    { type: 'payment', description: 'Сидорова М.И. оказана услуга "Пилинг"', time: '15 мин назад', amount: '1800 ₽' },
    { type: 'info', description: 'Поступила новая поставка материалов', time: '1 час назад', amount: '' },
]);

const criticalMaterials = ref([
    { id: 1, name: 'Шприцы 5мл', quantity: 10, unit: 'шт' },
    { id: 2, name: 'Перчатки стерильные', quantity: 5, unit: 'пар' },
    { id: 3, name: 'Анестетик', quantity: 2, unit: 'фл' },
]);

// Данные для оплат
const paymentFilter = ref('all');
const paymentDate = ref('');
const payments = ref([
    { id: 1, date: '16.03.2026', client: 'Иванова А.А.', service: 'Чистка лица', amount: '2500 ₽', status: 'pending' },
    { id: 2, date: '16.03.2026', client: 'Петров П.П.', service: 'Консультация', amount: '1500 ₽', status: 'completed' },
    { id: 3, date: '15.03.2026', client: 'Сидорова М.И.', service: 'Пилинг', amount: '1800 ₽', status: 'pending' },
]);

// Данные для склада
const materialSearch = ref('');
const materialFilter = ref('all');
const materials = ref([
    { id: 1, name: 'Шприцы 5мл', quantity: 10, unit: 'шт', status: 'critical', inOrder: false },
    { id: 2, name: 'Перчатки стерильные', quantity: 5, unit: 'пар', status: 'critical', inOrder: false },
    { id: 3, name: 'Анестетик', quantity: 2, unit: 'фл', status: 'critical', inOrder: true },
    { id: 4, name: 'Крем увлажняющий', quantity: 15, unit: 'шт', status: 'low', inOrder: false },
    { id: 5, name: 'Маски одноразовые', quantity: 50, unit: 'шт', status: 'normal', inOrder: false },
    { id: 6, name: 'Бинты', quantity: 30, unit: 'шт', status: 'normal', inOrder: false },
]);

const filteredMaterials = computed(() => {
    let filtered = materials.value;
    
    if (materialSearch.value) {
        filtered = filtered.filter(m => 
            m.name.toLowerCase().includes(materialSearch.value.toLowerCase())
        );
    }
    
    if (materialFilter.value !== 'all') {
        filtered = filtered.filter(m => m.status === materialFilter.value);
    }
    
    return filtered;
});

// Данные для закупки
const suppliers = ref([
    { id: 1, name: 'ООО "МедСнаб"' },
    { id: 2, name: 'АО "Фармацевт"' },
    { id: 3, name: 'ИП Петров' },
]);

const selectedSupplier = ref('');
const orderItems = ref([]);
const lastOrderStatus = ref('');

// Данные для зарплаты
const salaryMonth = ref('2026-03');
const employees = ref([
    { id: 1, name: 'Иванова М.С.', position: 'Врач-косметолог', hours: 160, rate: 500, salary: 80000, paid: false },
    { id: 2, name: 'Петрова А.В.', position: 'Врач-дерматолог', hours: 120, rate: 600, salary: 72000, paid: false },
    { id: 3, name: 'Сидорова Е.Н.', position: 'Медсестра', hours: 160, rate: 300, salary: 48000, paid: true },
    { id: 4, name: 'Смирнов Д.И.', position: 'Администратор', hours: 160, rate: 250, salary: 40000, paid: false },
]);

const totalSalary = computed(() => {
    return employees.value.reduce((sum, emp) => sum + emp.salary, 0);
});

// Методы
const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

const logout = () => {
    console.log('Выход из системы');
};

const openInventory = () => {
    console.log('Открыть инвентаризацию');
};

const openSalaryCalculation = () => {
    console.log('Открыть расчет ЗП');
};

const acceptPayment = (event) => {
    console.log('Принять оплату:', event);
};

const processPayment = (payment) => {
    payment.status = 'completed';
    console.log('Обработка платежа:', payment);
};

const addToOrder = (material) => {
    if (!material.inOrder) {
        orderItems.value.push({
            id: material.id,
            name: material.name,
            quantity: 10,
            unit: material.unit
        });
        material.inOrder = true;
    }
};

const removeFromOrder = (index) => {
    const material = materials.value.find(m => m.id === orderItems.value[index].id);
    if (material) {
        material.inOrder = false;
    }
    orderItems.value.splice(index, 1);
};

const submitOrder = () => {
    if (orderItems.value.length > 0 && selectedSupplier.value) {
        lastOrderStatus.value = 'Заявка отправлена на подтверждение директору';
        orderItems.value = [];
        selectedSupplier.value = '';
        
        // Сбрасываем флаги inOrder
        materials.value.forEach(m => m.inOrder = false);
        
        // Здесь будет логика отправки заявки
        console.log('Заявка отправлена');
    }
};

const calculateSalary = () => {
    console.log('Расчет зарплаты за', salaryMonth.value);
};

const processSalary = () => {
    employees.value.forEach(emp => emp.paid = true);
    console.log('Зарплата выплачена');
};

// Закрытие меню при клике вне его
const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>