<!-- resources/js/Pages/Dashboard/Doctor.vue -->
<template>
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <img
            id="background"
            class="absolute -left-20 top-0 max-w-[877px]"
            src="https://laravel.com/assets/img/welcome/background.svg"
        />

        <div class="relative flex min-h-screen flex-col selection:bg-[#2A7F6E] selection:text-white">
            <div class="relative w-full max-w-7xl px-6 mx-auto">
                <div class="flex gap-8 py-10">
                    <!-- Сайдбар -->
                    <aside class="w-64 shrink-0">
                        <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 sticky top-10">
                            <div class="mb-6 flex justify-center">
                                <svg class="h-12 w-auto text-[#2A7F6E] lg:h-16" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M61.8548 14.6253..."/> 
                                </svg>
                            </div>
                            
                            <nav class="space-y-1">
                                <Link 
                                    href="/dashboard/doctor" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/dashboard/doctor' ? 'bg-[#2A7F6E] text-white' : 'text-black dark:text-white/70 hover:bg-[#2A7F6E]/10']"
                                >
                                    <span class="text-xl">📅</span>
                                    <span>Главная (График)</span>
                                </Link>
                                
                                <Link 
                                    href="/doctor/patients" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/doctor/patients' ? 'bg-[#2A7F6E] text-white' : 'text-black dark:text-white/70 hover:bg-[#2A7F6E]/10']"
                                >
                                    <span class="text-xl">👥</span>
                                    <span>Мои пациенты</span>
                                </Link>
                                
                                <Link 
                                    href="/doctor/services" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/doctor/services' ? 'bg-[#2A7F6E] text-white' : 'text-black dark:text-white/70 hover:bg-[#2A7F6E]/10']"
                                >
                                    <span class="text-xl">💼</span>
                                    <span>Мои услуги</span>
                                </Link>
                                
                                <Link 
                                    href="/doctor/profile" 
                                    class="flex items-center gap-3 px-3 py-2 rounded-md transition"
                                    :class="[currentRoute === '/doctor/profile' ? 'bg-[#2A7F6E] text-white' : 'text-black dark:text-white/70 hover:bg-[#2A7F6E]/10']"
                                >
                                    <span class="text-xl">👤</span>
                                    <span>Профиль</span>
                                </Link>
                            </nav>
                            
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-zinc-700">
                                <div class="text-sm text-black dark:text-white/70">
                                    <p class="font-medium">{{ doctorData?.employee_name || 'Врач' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ doctorData?.role || 'Доктор' }}</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Основной контент -->
                    <main class="flex-1">
                        <!-- Шапка -->
                        <header class="mb-8">
                            <div class="flex items-center justify-between">
                                <h1 class="text-3xl font-semibold text-black dark:text-white">
                                    Здравствуйте, {{ doctorData?.employee_name || 'Врач' }}!
                                </h1>
                                
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            placeholder="Поиск пациента..." 
                                            v-model="searchQuery"
                                            @keyup.enter="searchPatients"
                                            class="pl-10 pr-4 py-2 w-64 bg-white dark:bg-zinc-900 rounded-lg shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 focus:outline-none focus:ring-2 focus:ring-[#2A7F6E]"
                                        />
                                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        
                                        <div v-if="searchResults.length > 0" class="absolute left-0 right-0 mt-2 bg-white dark:bg-zinc-900 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50 max-h-96 overflow-y-auto">
                                            <div v-for="patient in searchResults" :key="patient.client_id" 
                                                 @click="goToPatient(patient.client_id)"
                                                 class="px-4 py-3 hover:bg-[#2A7F6E]/10 cursor-pointer border-b border-gray-100 dark:border-zinc-800 last:border-0">
                                                <div class="font-medium text-black dark:text-white">{{ patient.client_name }}</div>
                                                <div class="text-sm text-gray-500">{{ patient.phone }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="relative" ref="profileMenu">
                                        <button @click="toggleProfileMenu" class="flex items-center gap-3 cursor-pointer">
                                            <span class="text-black dark:text-white font-medium">{{ doctorData?.employee_name || 'Врач' }}</span>
                                            <div class="flex size-10 items-center justify-center rounded-full bg-[#2A7F6E]/10">
                                                <svg class="size-5 text-[#2A7F6E]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                            </div>
                                        </button>
                                        
                                        <div v-if="showProfileMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-zinc-900 rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 py-1 z-50">
                                            <Link href="/doctor/profile" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#2A7F6E]/10">Профиль</Link>
                                            <Link href="/doctor/settings" class="block px-4 py-2 text-sm text-black dark:text-white/70 hover:bg-[#2A7F6E]/10">Настройки</Link>
                                            <hr class="my-1 border-gray-200 dark:border-zinc-700" />
                                            <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Выйти</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header>

                        <!-- Контент в зависимости от маршрута -->
                        <template v-if="currentRoute === '/dashboard/doctor'">
                            <!-- Статистика -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Приемов сегодня</p>
                                            <p class="text-2xl font-semibold text-black dark:text-white">{{ stats.todayAppointments || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-blue-100 rounded-full">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Завершено сегодня</p>
                                            <p class="text-2xl font-semibold text-green-600">{{ stats.completedToday || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-green-100 rounded-full">
                                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Всего пациентов</p>
                                            <p class="text-2xl font-semibold text-[#2A7F6E]">{{ stats.totalPatients || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-[#2A7F6E]/10 rounded-full">
                                            <svg class="w-6 h-6 text-[#2A7F6E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Ожидают подтверждения</p>
                                            <p class="text-2xl font-semibold text-yellow-600">{{ stats.pendingAppointments || 0 }}</p>
                                        </div>
                                        <div class="p-3 bg-yellow-100 rounded-full">
                                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Календарь -->
                            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800 mb-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h2 class="text-2xl font-semibold text-black dark:text-white">График приемов</h2>
                                    <div class="flex items-center gap-4">
                                        <div class="flex gap-2">
                                            <button @click="changeView('day')" 
                                                    class="px-3 py-1 text-sm border rounded-md transition"
                                                    :class="calendarView === 'day' ? 'bg-[#2A7F6E] text-white border-[#2A7F6E]' : 'border-gray-300 hover:bg-gray-100'">
                                                День
                                            </button>
                                            <button @click="changeView('week')" 
                                                    class="px-3 py-1 text-sm border rounded-md transition"
                                                    :class="calendarView === 'week' ? 'bg-[#2A7F6E] text-white border-[#2A7F6E]' : 'border-gray-300 hover:bg-gray-100'">
                                                Неделя
                                            </button>
                                        </div>
                                        <div class="flex gap-1">
                                            <button @click="prevPeriod" class="p-2 hover:bg-gray-100 rounded-full">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                                </svg>
                                            </button>
                                            <span class="px-4 py-2 font-medium">{{ currentPeriodLabel }}</span>
                                            <button @click="nextPeriod" class="p-2 hover:bg-gray-100 rounded-full">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Календарная сетка (оставляем как есть) -->
                                <div class="border rounded-lg overflow-hidden">
                                    <!-- Заголовки дней -->
                                    <div v-if="calendarView === 'week'" class="grid grid-cols-8 border-b">
                                        <div class="p-3 bg-gray-50 font-medium text-center border-r">Время</div>
                                        <div v-for="day in weekDays" :key="day.date" 
                                             class="p-3 bg-gray-50 font-medium text-center border-r last:border-r-0">
                                            <div>{{ day.name }}</div>
                                            <div class="text-sm text-gray-500">{{ day.date }}</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Временные слоты -->
                                    <div v-if="calendarView === 'week'" class="divide-y">
                                        <div v-for="hour in workHours" :key="hour" class="grid grid-cols-8">
                                            <div class="p-3 text-sm text-gray-500 border-r">{{ hour }}:00</div>
                                            <div v-for="day in weekDays" :key="day.date" 
                                                 class="p-1 border-r last:border-r-0 min-h-[80px]">
                                                <div v-for="apt in getAppointmentsAtTime(day.date, hour)" :key="apt.appointment_id"
                                                     @click="openAppointmentModal(apt)"
                                                     class="p-2 mb-1 rounded text-sm cursor-pointer transition hover:shadow-md"
                                                     :class="getAppointmentClass(apt.status)">
                                                    <div class="font-medium truncate">{{ apt.client?.client_name }}</div>
                                                    <div class="text-xs truncate">{{ apt.provided_services?.[0]?.service?.service_name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Дневной вид -->
                                    <div v-if="calendarView === 'day'" class="divide-y">
                                        <div v-for="hour in workHours" :key="hour" class="grid grid-cols-1">
                                            <div class="flex border-b">
                                                <div class="w-24 p-3 text-sm text-gray-500 border-r">{{ hour }}:00</div>
                                                <div class="flex-1 p-1 min-h-[80px]">
                                                    <div v-for="apt in getAppointmentsAtTime(selectedDate, hour)" :key="apt.appointment_id"
                                                         @click="openAppointmentModal(apt)"
                                                         class="p-2 mb-1 rounded text-sm cursor-pointer transition hover:shadow-md"
                                                         :class="getAppointmentClass(apt.status)">
                                                        <div class="font-medium">{{ apt.client?.client_name }}</div>
                                                        <div class="text-xs">{{ apt.provided_services?.[0]?.service?.service_name }}</div>
                                                        <div class="text-xs text-gray-500">{{ formatTime(apt.date) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Легенда -->
                                <div class="flex gap-4 mt-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-blue-200 border border-blue-400 rounded"></div>
                                        <span class="text-sm">Запланировано</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-green-200 border border-green-400 rounded"></div>
                                        <span class="text-sm">Подтверждено</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-yellow-200 border border-yellow-400 rounded"></div>
                                        <span class="text-sm">Окно</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 bg-gray-200 border border-gray-400 rounded"></div>
                                        <span class="text-sm">Завершено</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Низкий запас материалов -->
                            <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                                <h2 class="text-2xl font-semibold text-black dark:text-white mb-4">Низкий запас материалов</h2>
                                <div class="space-y-3">
                                    <div v-for="material in lowStockMaterials" :key="material.material_id" 
                                         class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                        <div>
                                            <p class="font-medium text-black dark:text-white">{{ material.name }}</p>
                                            <p class="text-sm text-gray-500">Осталось: {{ material.current_balance }} {{ material.unit }}</p>
                                        </div>
                                        <span class="px-3 py-1 bg-red-500 text-white rounded-full text-xs">Мин: {{ material.min_stock }}</span>
                                    </div>
                                    <div v-if="lowStockMaterials.length === 0" class="text-center py-4 text-gray-500">
                                        Все материалы в достаточном количестве
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Страница Мои пациенты -->
                        <div v-else-if="currentRoute === '/doctor/patients'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МОИ ПАЦИЕНТЫ</h2>
                            
                            <div class="space-y-4">
                                <div v-for="patient in patients" :key="patient.client_id" 
                                     class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition cursor-pointer"
                                     @click="viewPatientCard(patient.client_id)">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-black dark:text-white">{{ patient.client_name }}</h3>
                                            <p class="text-sm text-gray-500">📞 {{ patient.phone }}</p>
                                            <p class="text-sm text-gray-500">📅 {{ patient.birth_date }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-400">Последний визит: {{ patient.last_visit || 'Нет' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Мои услуги -->
                        <div v-else-if="currentRoute === '/doctor/services'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">МОИ УСЛУГИ</h2>
                            
                            <div class="grid gap-4">
                                <div v-for="service in doctorServices" :key="service.service_id" 
                                     class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-black dark:text-white">{{ service.service_name }}</h3>
                                            <p class="text-sm text-gray-500">{{ service.service_category }}</p>
                                        </div>
                                        <p class="text-[#2A7F6E] font-semibold">{{ service.default_price }} ₽</p>
                                    </div>
                                    
                                    <!-- Материалы для услуги -->
                                    <div v-if="service.materials?.length" class="mt-3">
                                        <p class="text-sm font-medium mb-2">Необходимые материалы:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="mat in service.materials" :key="mat.material_id"
                                                  class="px-2 py-1 bg-gray-100 dark:bg-zinc-800 rounded-md text-xs">
                                                {{ mat.name }} ({{ mat.pivot?.quantity }} {{ mat.unit }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Страница Профиль -->
                        <div v-else-if="currentRoute === '/doctor/profile'" class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-white/[0.05] dark:ring-zinc-800">
                            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ПРОФИЛЬ</h2>
                            
                            <div class="max-w-md space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Имя</label>
                                    <input type="text" v-model="profileForm.employee_name" 
                                           class="w-full px-4 py-2 border rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Email</label>
                                    <input type="email" v-model="profileForm.email" 
                                           class="w-full px-4 py-2 border rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Телефон</label>
                                    <input type="tel" v-model="profileForm.employee_phone" 
                                           class="w-full px-4 py-2 border rounded-md">
                                </div>
                                <button @click="updateProfile" 
                                        class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </main>
                </div>

                <!-- Модальное окно приема (ОБНОВЛЕНО) -->
                <Teleport to="body">
                    <div v-if="showAppointmentModal" class="fixed inset-0 z-50 overflow-y-auto">
                        <div class="flex items-center justify-center min-h-screen px-4">
                            <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAppointmentModal = false"></div>
                            
                            <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                                <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                                    <h3 class="text-xl font-semibold">Детали приема</h3>
                                    <button @click="showAppointmentModal = false" class="text-gray-500 hover:text-gray-700">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div v-if="selectedAppointment" class="p-6 space-y-6">
                                    <!-- Информация о клиенте -->
                                    <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-16 h-16 bg-[#2A7F6E]/20 rounded-full flex items-center justify-center">
                                                <span class="text-2xl font-medium text-[#2A7F6E]">
                                                    {{ getPatientInitials(selectedAppointment.client) }}
                                                </span>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-lg">{{ selectedAppointment.client?.client_name }}</h4>
                                                <p class="text-sm text-gray-500">📞 {{ selectedAppointment.client?.phone }}</p>
                                                <p class="text-sm text-gray-500">📅 {{ formatDate(selectedAppointment.date) }}</p>
                                            </div>
                                            <button @click="viewMedicalRecord(selectedAppointment.client?.client_id)" 
                                                    class="px-4 py-2 border border-[#2A7F6E] text-[#2A7F6E] rounded-md hover:bg-[#2A7F6E]/10">
                                                Медкарта
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Услуги -->
                                    <div>
                                        <h5 class="font-medium mb-2">Услуги:</h5>
                                        <div class="space-y-2">
                                            <div v-for="service in selectedAppointment.provided_services" :key="service.provided_id"
                                                 class="p-3 border rounded-lg">
                                                <div class="flex justify-between">
                                                    <span class="font-medium">{{ service.service?.service_name }}</span>
                                                    <span class="text-[#2A7F6E]">{{ service.quantity }} шт</span>
                                                </div>
                                                <p v-if="service.notes" class="text-sm text-gray-500 mt-1">{{ service.notes }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Расход материалов (ОБНОВЛЕНО) -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <h5 class="font-medium">Расход материалов:</h5>
                                            <span class="text-sm text-gray-500">Всего: {{ totalMaterialsQuantity }} ед.</span>
                                        </div>
                                        
                                        <!-- Список добавленных материалов -->
                                        <div class="space-y-2 mb-3">
                                            <div v-for="(material, index) in selectedMaterials" :key="index"
                                                 class="flex items-center justify-between p-2 border rounded-lg">
                                                <div class="flex-1">
                                                    <span class="font-medium">{{ getMaterialName(material.material_id) }}</span>
                                                    <span class="ml-2 text-sm text-gray-600">{{ material.quantity }} {{ getMaterialUnit(material.material_id) }}</span>
                                                </div>
                                                <button @click="removeMaterial(index)" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Ранее сохраненные материалы -->
                                        <div v-if="selectedAppointment.materials?.length" class="mt-2">
                                            <p class="text-sm text-gray-500 mb-1">Ранее сохраненные:</p>
                                            <div v-for="material in selectedAppointment.materials" :key="material.consumption_id"
                                                 class="flex items-center justify-between p-2 bg-gray-50 rounded-lg mb-1">
                                                <span>{{ material.material?.name }}</span>
                                                <span class="font-medium">{{ material.quantity }} {{ material.material?.unit }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Добавление новых материалов -->
                                        <div class="mt-4 p-4 border border-dashed rounded-lg">
                                            <h6 class="text-sm font-medium mb-3">Добавить материал</h6>
                                            <div class="space-y-3">
                                                <select v-model="newMaterial.material_id" 
                                                        class="w-full px-3 py-2 border rounded-md">
                                                    <option value="">Выберите материал</option>
                                                    <option v-for="mat in availableMaterials" :key="mat.material_id" :value="mat.material_id">
                                                        {{ mat.name }} (доступно: {{ mat.current_balance }} {{ mat.unit }})
                                                    </option>
                                                </select>
                                                <div class="flex gap-2">
                                                    <input type="number" v-model="newMaterial.quantity" min="1" 
                                                           placeholder="Количество"
                                                           class="flex-1 px-3 py-2 border rounded-md">
                                                    <button @click="addToMaterialsList" 
                                                            class="px-4 py-2 bg-[#2A7F6E] text-white rounded-md hover:bg-[#2A7F6E]/90"
                                                            :disabled="!canAddMaterial">
                                                        Добавить в список
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Статус приема и действия -->
                                    <div class="flex items-center justify-between pt-4 border-t">
                                        <select v-model="selectedAppointment.status" @change="updateAppointmentStatus"
                                                class="px-3 py-2 border rounded-md">
                                            <option value="0">Запланирован</option>
                                            <option value="1">Подтвержден</option>
                                            <option value="2">Завершен</option>
                                            <option value="3">Отменен</option>
                                        </select>
                                        
                                        <div class="flex gap-2">
                                            <button @click="saveAllMaterials" 
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                                    :disabled="selectedMaterials.length === 0">
                                                Сохранить материалы
                                            </button>
                                            <button @click="completeAppointmentWithMaterials" 
                                                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                                Завершить прием
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Teleport>

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
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    laravelVersion: String,
    phpVersion: String,
    doctor: Object,
    appointments: Array,
    patients: Array,
    doctorServices: Array,
    materials: Array
});

// Текущий маршрут
const currentRoute = computed(() => usePage().url);

// Данные врача
const doctorData = ref(props.doctor || {});

// Состояние меню
const showProfileMenu = ref(false);
const profileMenu = ref(null);

// Поиск
const searchQuery = ref('');
const searchResults = ref([]);

// Календарь
const calendarView = ref('week');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const appointments = ref(props.appointments || []);

// Статистика
const stats = ref({
    todayAppointments: 0,
    completedToday: 0,
    totalPatients: props.patients?.length || 0,
    pendingAppointments: 0
});

// Материалы
const materials = ref(props.materials || []);
const lowStockMaterials = computed(() => {
    return materials.value.filter(m => m.current_balance <= m.min_stock);
});

// Модальное окно
const showAppointmentModal = ref(false);
const selectedAppointment = ref(null);
const availableMaterials = ref([]);

// НОВЫЕ: список выбранных материалов для текущего приема
const selectedMaterials = ref([]);
const newMaterial = ref({ material_id: '', quantity: 1 });

// Профиль
const profileForm = ref({
    employee_name: doctorData.value?.employee_name || '',
    email: doctorData.value?.email || '',
    employee_phone: doctorData.value?.employee_phone || ''
});

// Дни недели
const weekDays = computed(() => {
    const days = [];
    const start = new Date(selectedDate.value);
    start.setDate(start.getDate() - start.getDay() + 1);
    
    for (let i = 0; i < 7; i++) {
        const date = new Date(start);
        date.setDate(start.getDate() + i);
        days.push({
            name: date.toLocaleDateString('ru-RU', { weekday: 'short' }),
            date: date.toISOString().split('T')[0]
        });
    }
    return days;
});

// Рабочие часы
const workHours = Array.from({ length: 11 }, (_, i) => i + 9); // 9:00 - 20:00

// Текущий период
const currentPeriodLabel = computed(() => {
    if (calendarView.value === 'day') {
        return new Date(selectedDate.value).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });
    } else {
        const start = new Date(weekDays.value[0].date);
        const end = new Date(weekDays.value[6].date);
        return `${start.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })}`;
    }
});

// НОВЫЕ: вычисляемые свойства для материалов
const totalMaterialsQuantity = computed(() => {
    return selectedMaterials.value.reduce((sum, m) => sum + m.quantity, 0);
});

const canAddMaterial = computed(() => {
    return newMaterial.value.material_id && newMaterial.value.quantity > 0;
});

// Методы
const toggleProfileMenu = () => {
    showProfileMenu.value = !showProfileMenu.value;
};

const logout = () => {
    router.post('/logout');
};

const searchPatients = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }
    try {
        const response = await axios.get('/api/doctor/patients/search', {
            params: { query: searchQuery.value }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Search error:', error);
    }
};

const goToPatient = (clientId) => {
    router.get(`/doctor/patients/${clientId}`);
    searchResults.value = [];
    searchQuery.value = '';
};

const viewPatientCard = (clientId) => {
    router.get(`/doctor/medical-records/${clientId}`);
};

const getPatientInitials = (client) => {
    if (!client?.client_name) return '??';
    return client.client_name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

const getAppointmentClass = (status) => {
    switch(Number(status)) {
        case 0: return 'bg-blue-200 border-blue-400 hover:bg-blue-300';
        case 1: return 'bg-green-200 border-green-400 hover:bg-green-300';
        case 2: return 'bg-gray-200 border-gray-400 hover:bg-gray-300';
        case 3: return 'bg-red-200 border-red-400 hover:bg-red-300';
        default: return 'bg-gray-200';
    }
};

const getAppointmentsAtTime = (date, hour) => {
    if (!appointments.value) return [];
    return appointments.value.filter(apt => {
        const aptDate = new Date(apt.date);
        return aptDate.toISOString().split('T')[0] === date && aptDate.getHours() === hour;
    });
};

const changeView = (view) => {
    calendarView.value = view;
    loadAppointments();
};

const prevPeriod = () => {
    const date = new Date(selectedDate.value);
    if (calendarView.value === 'day') {
        date.setDate(date.getDate() - 1);
    } else {
        date.setDate(date.getDate() - 7);
    }
    selectedDate.value = date.toISOString().split('T')[0];
    loadAppointments();
};

const nextPeriod = () => {
    const date = new Date(selectedDate.value);
    if (calendarView.value === 'day') {
        date.setDate(date.getDate() + 1);
    } else {
        date.setDate(date.getDate() + 7);
    }
    selectedDate.value = date.toISOString().split('T')[0];
    loadAppointments();
};

const loadAppointments = async () => {
    try {
        const response = await axios.get('/api/doctor/appointments', {
            params: { 
                view: calendarView.value,
                date: selectedDate.value
            }
        });
        appointments.value = response.data;
        
        // Обновляем статистику
        const today = new Date().toISOString().split('T')[0];
        stats.value.todayAppointments = response.data.filter(apt => 
            apt.date.split('T')[0] === today
        ).length;
        stats.value.completedToday = response.data.filter(apt => 
            apt.date.split('T')[0] === today && apt.status === 2
        ).length;
        stats.value.pendingAppointments = response.data.filter(apt => 
            apt.status === 0 || apt.status === 1
        ).length;
    } catch (error) {
        console.error('Error loading appointments:', error);
    }
};

// НОВЫЙ: открытие модального окна с очисткой списка материалов
const openAppointmentModal = async (appointment) => {
    try {
        const response = await axios.get(`/api/doctor/appointments/${appointment.appointment_id}`);
        selectedAppointment.value = response.data;
        
        const materialsResponse = await axios.get('/api/doctor/materials/available');
        availableMaterials.value = materialsResponse.data;
        
        // Сбрасываем список выбранных материалов
        selectedMaterials.value = [];
        newMaterial.value = { material_id: '', quantity: 1 };
        
        showAppointmentModal.value = true;
    } catch (error) {
        console.error('Error loading appointment:', error);
    }
};

const updateAppointmentStatus = async () => {
    try {
        await axios.put(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/status`, {
            status: selectedAppointment.value.status
        });
        await loadAppointments();
    } catch (error) {
        console.error('Error updating status:', error);
    }
};

// НОВЫЙ: вспомогательные методы для материалов
const getMaterialName = (materialId) => {
    const material = availableMaterials.value.find(m => m.material_id === materialId);
    return material?.name || 'Неизвестный материал';
};

const getMaterialUnit = (materialId) => {
    const material = availableMaterials.value.find(m => m.material_id === materialId);
    return material?.unit || 'шт';
};

// НОВЫЙ: добавление материала в список (не в БД)
const addToMaterialsList = () => {
    if (!canAddMaterial.value) return;
    
    const existingIndex = selectedMaterials.value.findIndex(
        m => m.material_id === newMaterial.value.material_id
    );
    
    if (existingIndex >= 0) {
        // Если материал уже есть, увеличиваем количество
        selectedMaterials.value[existingIndex].quantity += newMaterial.value.quantity;
    } else {
        // Иначе добавляем новый
        selectedMaterials.value.push({
            material_id: newMaterial.value.material_id,
            quantity: newMaterial.value.quantity,
            notes: ''
        });
    }
    
    // Сбрасываем форму
    newMaterial.value = { material_id: '', quantity: 1 };
};

// НОВЫЙ: удаление материала из списка
const removeMaterial = (index) => {
    selectedMaterials.value.splice(index, 1);
};

// НОВЫЙ: сохранение всех материалов одним запросом
const saveAllMaterials = async () => {
    if (selectedMaterials.value.length === 0) return;
    
    try {
        await axios.post(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/materials/save`, {
            materials: selectedMaterials.value
        });
        
        // Обновляем данные приема
        const response = await axios.get(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}`);
        selectedAppointment.value = response.data;
        
        // Очищаем список
        selectedMaterials.value = [];
        
        alert('Материалы успешно сохранены');
    } catch (error) {
        console.error('Error saving materials:', error);
        alert(error.response?.data?.error || 'Ошибка при сохранении материалов');
    }
};

// НОВЫЙ: завершение приема с сохранением материалов
const completeAppointmentWithMaterials = async () => {
    try {
        // Сначала сохраняем материалы, если они есть
        if (selectedMaterials.value.length > 0) {
            await saveAllMaterials();
        }
        
        // Затем завершаем прием
        await axios.post(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/complete`);
        
        showAppointmentModal.value = false;
        await loadAppointments();
    } catch (error) {
        console.error('Error completing appointment:', error);
    }
};

// Сохраняем старый метод для обратной совместимости
const addMaterial = async () => {
    if (!newMaterial.value.material_id || !newMaterial.value.quantity) return;
    
    try {
        await axios.post(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/materials`, {
            material_id: newMaterial.value.material_id,
            quantity: newMaterial.value.quantity
        });
        
        const response = await axios.get(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}`);
        selectedAppointment.value = response.data;
        
        newMaterial.value = { material_id: '', quantity: 1 };
    } catch (error) {
        console.error('Error adding material:', error);
    }
};

const completeAppointment = async () => {
    try {
        await axios.post(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/complete`);
        showAppointmentModal.value = false;
        await loadAppointments();
    } catch (error) {
        console.error('Error completing appointment:', error);
    }
};

const viewMedicalRecord = (clientId) => {
    router.get(`/doctor/medical-records/${clientId}`);
    showAppointmentModal.value = false;
};

const updateProfile = async () => {
    try {
        await axios.put('/api/doctor/profile', profileForm.value);
        alert('Профиль обновлен');
    } catch (error) {
        console.error('Error updating profile:', error);
    }
};

// Закрытие меню
const handleClickOutside = (event) => {
    if (profileMenu.value && !profileMenu.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    loadAppointments();
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>