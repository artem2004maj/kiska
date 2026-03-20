<!-- resources/js/Pages/Doctor/Dashboard.vue -->
<template>
    <DoctorLayout :doctor="doctorData">
        <!-- Статистика -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 mb-4 sm:mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Приемов сегодня</p>
                        <p class="text-base sm:text-lg lg:text-2xl font-semibold">{{ stats.todayAppointments || 0 }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-blue-100 rounded-full">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Завершено сегодня</p>
                        <p class="text-base sm:text-lg lg:text-2xl font-semibold text-green-600">{{ stats.completedToday || 0 }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-green-100 rounded-full">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Всего пациентов</p>
                        <p class="text-base sm:text-lg lg:text-2xl font-semibold text-[#2A7F6E]">{{ stats.totalPatients || 0 }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[#2A7F6E]/10 rounded-full">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#2A7F6E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs sm:text-sm text-gray-500">Ожидают подтверждения</p>
                        <p class="text-base sm:text-lg lg:text-2xl font-semibold text-yellow-600">{{ stats.pendingAppointments || 0 }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-yellow-100 rounded-full">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Календарь -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow mb-4 sm:mb-6 overflow-x-auto">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-2">
                <h2 class="text-lg sm:text-2xl font-semibold">График приемов</h2>
                <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <div class="flex gap-1 sm:gap-2">
                        <button @click="changeView('day')" 
                                class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded-md transition"
                                :class="calendarView === 'day' ? 'bg-[#2A7F6E] text-white border-[#2A7F6E]' : 'border-gray-300 hover:bg-gray-100'">
                            День
                        </button>
                        <button @click="changeView('week')" 
                                class="px-2 sm:px-3 py-1 text-xs sm:text-sm border rounded-md transition"
                                :class="calendarView === 'week' ? 'bg-[#2A7F6E] text-white border-[#2A7F6E]' : 'border-gray-300 hover:bg-gray-100'">
                            Неделя
                        </button>
                    </div>
                    <div class="flex gap-1">
                        <button @click="prevPeriod" class="p-1 sm:p-2 hover:bg-gray-100 rounded-full">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="px-2 sm:px-4 py-1 sm:py-2 text-xs sm:text-sm font-medium whitespace-nowrap">{{ currentPeriodLabel }}</span>
                        <button @click="nextPeriod" class="p-1 sm:p-2 hover:bg-gray-100 rounded-full">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="min-w-[600px] lg:min-w-0">
                <!-- Заголовки дней (недельный вид) -->
                <div v-if="calendarView === 'week'" class="grid grid-cols-8 border-b">
                    <div class="p-2 sm:p-3 bg-gray-50 font-medium text-center border-r text-xs sm:text-sm">Время</div>
                    <div v-for="day in weekDays" :key="day.date" 
                         class="p-2 sm:p-3 bg-gray-50 font-medium text-center border-r last:border-r-0 text-xs sm:text-sm">
                        <div>{{ day.name }}</div>
                        <div class="text-xs text-gray-500">{{ day.date }}</div>
                    </div>
                </div>
                
                <!-- Временные слоты (недельный вид) -->
                <div v-if="calendarView === 'week'" class="divide-y">
                    <div v-for="hour in workHours" :key="hour" class="grid grid-cols-8">
                        <div class="p-2 sm:p-3 text-xs sm:text-sm text-gray-500 border-r">{{ hour }}:00</div>
                        <div v-for="day in weekDays" :key="day.date" 
                             class="p-1 border-r last:border-r-0 min-h-[60px] sm:min-h-[80px]">
                            <div v-for="apt in getAppointmentsAtTime(day.date, hour)" :key="apt.appointment_id"
                                 @click="openAppointmentModal(apt)"
                                 class="p-1 sm:p-2 mb-1 rounded text-xs sm:text-sm cursor-pointer transition hover:shadow-md"
                                 :class="getAppointmentClass(apt.status)">
                                <div class="font-medium truncate">{{ apt.client?.client_name }}</div>
                                <div class="text-xs truncate hidden sm:block">{{ getServiceNames(apt) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Дневной вид (только рабочие часы) -->
                <div v-if="calendarView === 'day'" class="divide-y">
                    <div v-for="hour in dayWorkingHours" :key="hour" class="grid grid-cols-1">
                        <div class="flex border-b">
                            <div class="w-16 sm:w-24 p-2 sm:p-3 text-xs sm:text-sm text-gray-500 border-r">{{ hour }}:00</div>
                            <div class="flex-1 p-1 min-h-[60px] sm:min-h-[80px]">
                                <div v-for="apt in getAppointmentsAtTime(selectedDate, hour)" :key="apt.appointment_id"
                                     @click="openAppointmentModal(apt)"
                                     class="p-1 sm:p-2 mb-1 rounded text-xs sm:text-sm cursor-pointer transition hover:shadow-md"
                                     :class="getAppointmentClass(apt.status)">
                                    <div class="font-medium">{{ apt.client?.client_name }}</div>
                                    <div class="text-xs text-gray-500">{{ formatTime(apt.date) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="dayWorkingHours.length === 0" class="text-center py-8 text-gray-500">
                        В этот день врач не работает
                    </div>
                </div>
            </div>
            
            <!-- Легенда -->
            <div class="flex flex-wrap gap-2 sm:gap-4 mt-3 sm:mt-4 text-xs sm:text-sm">
                <div class="flex items-center gap-1 sm:gap-2">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-blue-200 border border-blue-400 rounded"></div>
                    <span>Запланировано</span>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-green-200 border border-green-400 rounded"></div>
                    <span>Подтверждено</span>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-yellow-200 border border-yellow-400 rounded"></div>
                    <span>Окно</span>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <div class="w-3 h-3 sm:w-4 sm:h-4 bg-gray-200 border border-gray-400 rounded"></div>
                    <span>Завершено</span>
                </div>
            </div>
        </div>

        <!-- Низкий запас материалов -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-3 sm:p-6 shadow">
            <h2 class="text-lg sm:text-2xl font-semibold mb-3 sm:mb-4">Низкий запас материалов</h2>
            <div class="space-y-2 sm:space-y-3">
                <div v-for="material in lowStockMaterials" :key="material.material_id" 
                     class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-2 sm:p-3 bg-red-50 dark:bg-red-900/20 rounded-lg gap-2">
                    <div>
                        <p class="font-medium text-sm sm:text-base">{{ material.name }}</p>
                        <p class="text-xs sm:text-sm text-gray-500">Осталось: {{ material.current_balance }} {{ material.unit }}</p>
                    </div>
                    <span class="px-2 sm:px-3 py-0.5 sm:py-1 bg-red-500 text-white rounded-full text-xs whitespace-nowrap">Мин: {{ material.min_stock }}</span>
                </div>
                <div v-if="lowStockMaterials.length === 0" class="text-center py-3 sm:py-4 text-xs sm:text-sm text-gray-500">
                    Все материалы в достаточном количестве
                </div>
            </div>
        </div>

        <!-- Модальное окно приема (без изменений) -->
        <Teleport to="body">
            <div v-if="showAppointmentModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen p-2 sm:px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showAppointmentModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-4 sm:px-6 py-3 sm:py-4 border-b flex justify-between items-center">
                            <h3 class="text-base sm:text-xl font-semibold">Детали приема</h3>
                            <button @click="showAppointmentModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedAppointment" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                            <!-- Информация о клиенте -->
                            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-3 sm:p-4">
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <div class="w-10 h-10 sm:w-16 sm:h-16 rounded-full bg-[#2A7F6E]/20 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img v-if="selectedAppointment.client?.photo_url" 
                                            :src="selectedAppointment.client.photo_url" 
                                            :alt="selectedAppointment.client.client_name"
                                            class="w-full h-full object-cover">
                                        <span v-else class="text-base sm:text-2xl font-medium text-[#2A7F6E]">
                                            {{ getPatientInitials(selectedAppointment.client) }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-sm sm:text-lg truncate">{{ selectedAppointment.client?.client_name }}</h4>
                                        <p class="text-xs sm:text-sm text-gray-500">📞 {{ selectedAppointment.client?.phone }}</p>
                                        <p class="text-xs sm:text-sm text-gray-500">📅 {{ formatDate(selectedAppointment.date) }}</p>
                                    </div>
                                    <button @click="viewMedicalRecord(selectedAppointment.client?.client_id)" 
                                            class="px-2 sm:px-4 py-1 sm:py-2 text-xs sm:text-sm border border-[#2A7F6E] text-[#2A7F6E] rounded-md hover:bg-[#2A7F6E]/10 whitespace-nowrap">
                                        Медкарта
                                    </button>
                                </div>
                            </div>

                            <!-- Услуги -->
                            <div>
                                <h5 class="text-sm sm:text-base font-medium mb-2">Услуги:</h5>
                                <div class="space-y-2">
                                    <div v-for="service in selectedAppointment.provided_services" :key="service.provided_id"
                                        class="p-2 sm:p-3 border rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <span class="text-sm sm:text-base font-medium">
                                                    {{ service.service?.service_name || 'Услуга не указана' }}
                                                </span>
                                                <p v-if="service.service?.service_category" class="text-xs text-gray-500 mt-0.5">
                                                    {{ service.service.service_category }}
                                                </p>
                                            </div>
                                            <span class="text-xs sm:text-sm text-[#2A7F6E] font-medium">
                                                {{ service.quantity }} шт · {{ service.service?.default_price || 0 }} ₽
                                            </span>
                                        </div>
                                        <p v-if="service.notes" class="text-xs text-gray-500 mt-2">{{ service.notes }}</p>
                                    </div>
                                    <div v-if="!selectedAppointment.provided_services?.length" class="text-center py-4 text-gray-500">
                                        Услуги не указаны
                                    </div>
                                </div>
                            </div>

                            <!-- Расход материалов -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="text-sm sm:text-base font-medium">Расход материалов:</h5>
                                    <span class="text-xs sm:text-sm text-gray-500">Всего: {{ totalMaterialsQuantity }} ед.</span>
                                </div>
                                
                                <div class="space-y-2 mb-3">
                                    <div v-for="(material, index) in selectedMaterials" :key="index"
                                         class="flex items-center justify-between p-2 border rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm sm:text-base font-medium">{{ getMaterialName(material.material_id) }}</span>
                                            <span class="ml-2 text-xs sm:text-sm text-gray-600">{{ material.quantity }} {{ getMaterialUnit(material.material_id) }}</span>
                                        </div>
                                        <button @click="removeMaterial(index)" class="ml-2 text-red-500 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="selectedAppointment.materials?.length" class="mt-2">
                                    <p class="text-xs sm:text-sm text-gray-500 mb-1">Ранее сохраненные:</p>
                                    <div v-for="material in selectedAppointment.materials" :key="material.consumption_id"
                                         class="flex items-center justify-between p-2 bg-gray-50 rounded-lg mb-1">
                                        <span class="text-sm">{{ material.material?.name }}</span>
                                        <span class="text-sm font-medium">{{ material.quantity }} {{ material.material?.unit }}</span>
                                    </div>
                                </div>
                                
                                <div class="mt-3 sm:mt-4 p-3 sm:p-4 border border-dashed rounded-lg">
                                    <h6 class="text-xs sm:text-sm font-medium mb-2 sm:mb-3">Добавить материал</h6>
                                    <div class="space-y-2 sm:space-y-3">
                                        <select v-model="newMaterial.material_id" 
                                                class="w-full px-2 sm:px-3 py-1.5 sm:py-2 text-sm border rounded-md">
                                            <option value="">Выберите материал</option>
                                            <option v-for="mat in availableMaterials" :key="mat.material_id" :value="mat.material_id">
                                                {{ mat.name }} (доступно: {{ mat.current_balance }} {{ mat.unit }})
                                            </option>
                                        </select>
                                        <div class="flex gap-2">
                                            <input type="number" v-model="newMaterial.quantity" min="1" 
                                                   placeholder="Количество"
                                                   class="flex-1 px-2 sm:px-3 py-1.5 sm:py-2 text-sm border rounded-md">
                                            <button @click="addToMaterialsList" 
                                                    class="px-3 sm:px-4 py-1.5 sm:py-2 bg-[#2A7F6E] text-white text-sm rounded-md hover:bg-[#2A7F6E]/90"
                                                    :disabled="!canAddMaterial">
                                                Добавить
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Статус приема и действия -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 sm:gap-3 pt-3 sm:pt-4 border-t">
                                <select v-model="selectedAppointment.status" @change="updateAppointmentStatus"
                                        class="px-2 sm:px-3 py-1.5 sm:py-2 text-sm border rounded-md">
                                    <option value="0">Запланирован</option>
                                    <option value="1">Подтвержден</option>
                                    <option value="2">Завершен</option>
                                    <option value="3">Отменен</option>
                                </select>
                                
                                <div class="flex gap-2">
                                    <button @click="saveAllMaterials" 
                                            class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700"
                                            :disabled="selectedMaterials.length === 0">
                                        Сохранить
                                    </button>
                                    <button @click="completeAppointmentWithMaterials" 
                                            class="flex-1 sm:flex-none px-4 sm:px-6 py-1.5 sm:py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                                        Завершить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </DoctorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';

const props = defineProps({
    laravelVersion: String,
    phpVersion: String,
    doctor: Object,
    appointments: Array,
    patients: Array,
    doctorServices: Array,
    materials: Array
});

const doctorData = ref(props.doctor || {});
const searchQuery = ref('');
const searchResults = ref([]);
const calendarView = ref('week');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const appointments = ref(props.appointments || []);
const schedule = ref({});

const stats = ref({
    todayAppointments: 0,
    completedToday: 0,
    totalPatients: props.patients?.length || 0,
    pendingAppointments: 0
});

const materials = ref(props.materials || []);
const lowStockMaterials = computed(() => {
    return materials.value.filter(m => m.current_balance <= m.min_stock);
});

const showAppointmentModal = ref(false);
const selectedAppointment = ref(null);
const availableMaterials = ref([]);
const selectedMaterials = ref([]);
const newMaterial = ref({ material_id: '', quantity: 1 });

// Рабочие часы для недельного вида (фиксированные 9-20)
const workHours = Array.from({ length: 11 }, (_, i) => i + 9);

// Дни недели для отображения
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

// Часы для дневного вида (только рабочие часы врача)
const dayWorkingHours = computed(() => {
    if (calendarView.value !== 'day') return [];
    
    const date = new Date(selectedDate.value);
    const dayOfWeek = date.getDay();
    const daySchedule = schedule.value[dayOfWeek];
    
    if (!daySchedule?.working) return [];
    
    const startHour = parseInt(daySchedule.start.split(':')[0]);
    const endHour = parseInt(daySchedule.end.split(':')[0]);
    const hours = [];
    for (let h = startHour; h < endHour; h++) {
        hours.push(h);
    }
    return hours;
});

const currentPeriodLabel = computed(() => {
    if (calendarView.value === 'day') {
        return new Date(selectedDate.value).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' });
    } else {
        const start = new Date(weekDays.value[0].date);
        const end = new Date(weekDays.value[6].date);
        return `${start.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' })}`;
    }
});

const totalMaterialsQuantity = computed(() => {
    return selectedMaterials.value.reduce((sum, m) => sum + m.quantity, 0);
});

const canAddMaterial = computed(() => {
    return newMaterial.value.material_id && newMaterial.value.quantity > 0;
});

const getServiceNames = (appointment) => {
    if (!appointment.provided_services?.length) return 'Услуга не указана';
    return appointment.provided_services.map(ps => ps.service?.service_name).join(', ');
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
    
    // Для дневного вида проверяем расписание
    if (calendarView.value === 'day') {
        const dayOfWeek = new Date(date).getDay();
        const daySchedule = schedule.value[dayOfWeek];
        
        if (!daySchedule?.working) return [];
        
        const startHour = parseInt(daySchedule.start.split(':')[0]);
        const endHour = parseInt(daySchedule.end.split(':')[0]);
        
        if (hour < startHour || hour >= endHour) return [];
    }
    
    return appointments.value.filter(apt => {
        const aptDate = new Date(apt.date);
        return aptDate.toISOString().split('T')[0] === date && aptDate.getHours() === hour;
    });
};

const loadAppointments = async () => {
    try {
        const response = await axios.get('/api/doctor/appointments', {
            params: { 
                view: calendarView.value,
                date: selectedDate.value
            }
        });
        
        appointments.value = response.data.appointments || response.data;
        
        if (response.data.schedule) {
            schedule.value = response.data.schedule;
        }
        
        const today = new Date().toISOString().split('T')[0];
        stats.value.todayAppointments = appointments.value.filter(apt => 
            apt.date.split('T')[0] === today
        ).length;
        stats.value.completedToday = appointments.value.filter(apt => 
            apt.date.split('T')[0] === today && apt.status === 2
        ).length;
        stats.value.pendingAppointments = appointments.value.filter(apt => 
            apt.status === 0 || apt.status === 1
        ).length;
    } catch (error) {
        console.error('Error loading appointments:', error);
    }
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

const openAppointmentModal = async (appointment) => {
    try {
        const response = await axios.get(`/api/doctor/appointments/${appointment.appointment_id}`);
        selectedAppointment.value = response.data;
        
        const materialsResponse = await axios.get('/api/doctor/materials/available');
        availableMaterials.value = materialsResponse.data;
        
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

const getMaterialName = (materialId) => {
    const material = availableMaterials.value.find(m => m.material_id === materialId);
    return material?.name || 'Неизвестный материал';
};

const getMaterialUnit = (materialId) => {
    const material = availableMaterials.value.find(m => m.material_id === materialId);
    return material?.unit || 'шт';
};

const addToMaterialsList = () => {
    if (!canAddMaterial.value) return;
    
    const existingIndex = selectedMaterials.value.findIndex(
        m => m.material_id === newMaterial.value.material_id
    );
    
    if (existingIndex >= 0) {
        selectedMaterials.value[existingIndex].quantity += newMaterial.value.quantity;
    } else {
        selectedMaterials.value.push({
            material_id: newMaterial.value.material_id,
            quantity: newMaterial.value.quantity,
            notes: ''
        });
    }
    
    newMaterial.value = { material_id: '', quantity: 1 };
};

const removeMaterial = (index) => {
    selectedMaterials.value.splice(index, 1);
};

const saveAllMaterials = async () => {
    if (selectedMaterials.value.length === 0) return;
    
    try {
        await axios.post(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}/materials/save`, {
            materials: selectedMaterials.value
        });
        
        const response = await axios.get(`/api/doctor/appointments/${selectedAppointment.value.appointment_id}`);
        selectedAppointment.value = response.data;
        selectedMaterials.value = [];
        
        alert('Материалы успешно сохранены');
    } catch (error) {
        console.error('Error saving materials:', error);
        alert(error.response?.data?.error || 'Ошибка при сохранении материалов');
    }
};

const completeAppointmentWithMaterials = async () => {
    try {
        if (selectedMaterials.value.length > 0) {
            await saveAllMaterials();
        }
        
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

onMounted(() => {
    loadAppointments();
});
</script>