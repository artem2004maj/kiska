<!-- resources/js/Pages/Accountant/Salary.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ЗАРАБОТНАЯ ПЛАТА'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">РАСЧЕТ ЗАРАБОТНОЙ ПЛАТЫ</h2>
            
            <!-- Таблица сотрудников -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сотрудник</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Должность</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Ставка (₽/час)</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Начислено</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Статус</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                         </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employees" :key="employee.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ employee.name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ getRoleName(employee.role) }}</td>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-black dark:text-white/70">{{ formatPrice(employee.hourly_rate) }}</span>
                                    <button @click="openRateModal(employee)" 
                                            class="text-[#3b82f6] hover:text-[#3b82f6]/80 transition"
                                            title="Редактировать ставку">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 text-black dark:text-white/70 font-medium text-[#22c55e]">{{ formatPrice(employee.total_amount) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full" 
                                      :class="employee.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ employee.is_paid ? 'Выплачено' : 'Начислено' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="flex gap-2">
                                    <button v-if="!employee.is_paid" 
                                            @click="openSalaryModal(employee.id)"
                                            class="px-3 py-1 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition text-sm">
                                        Рассчитать
                                    </button>
                                    <button v-else 
                                            @click="viewSalaryDetails(employee)"
                                            class="px-3 py-1 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition text-sm">
                                        Детали
                                    </button>
                                    <button @click="openRateModal(employee)" 
                                            class="px-3 py-1 border border-[#3b82f6] text-[#3b82f6] rounded-md hover:bg-[#3b82f6]/10 transition text-sm">
                                        Ставка
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-zinc-700">
                            <td colspan="3" class="py-3 text-right font-medium text-black dark:text-white">Итого:</td>
                            <td class="py-3 font-medium text-[#22c55e]">{{ formatPrice(totalSalary) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Модальное окно редактирования ставки -->
        <Teleport to="body">
            <div v-if="showRateModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeRateModal"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Редактирование ставки</h3>
                            <button @click="closeRateModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedEmployeeForRate" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Сотрудник</label>
                                <p class="text-gray-700 font-medium">{{ selectedEmployeeForRate.name }}</p>
                                <p class="text-sm text-gray-500">{{ getRoleName(selectedEmployeeForRate.role) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium mb-1">Почасовая ставка (₽/час)</label>
                                <input type="number" v-model="hourlyRateValue" step="50" min="0"
                                       class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent" />
                                <p class="text-xs text-gray-500 mt-1">Рекомендуемая ставка: 500-1500 ₽/час</p>
                            </div>
                            
                            <div class="flex gap-3 pt-4">
                                <button @click="updateHourlyRate" 
                                        :disabled="loading"
                                        class="flex-1 px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 disabled:opacity-50">
                                    {{ loading ? 'Сохранение...' : 'Сохранить' }}
                                </button>
                                <button @click="closeRateModal" 
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                    Отмена
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно расчета зарплаты -->
        <Teleport to="body">
            <div v-if="showSalaryModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeSalaryModal"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Расчет заработной платы</h3>
                            <button @click="closeSalaryModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedEmployee" class="p-6 space-y-6">
                            <!-- Информация о сотруднике -->
                            <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-[#3b82f6]/20 flex items-center justify-center">
                                        <span class="text-xl font-medium text-[#3b82f6]">{{ getInitials(selectedEmployee.name) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ selectedEmployee.name }}</h4>
                                        <p class="text-sm text-gray-500">{{ getRoleName(selectedEmployee.role) }}</p>
                                        <p class="text-sm text-gray-500">Ставка: {{ formatPrice(selectedEmployee.hourly_rate) }} ₽/час</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Параметры расчета -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Период</label>
                                    <div class="grid grid-cols-2 gap-4">
                                        <select v-model="calculation.month" class="px-3 py-2 border rounded-md">
                                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.name }}</option>
                                        </select>
                                        <select v-model="calculation.year" class="px-3 py-2 border rounded-md">
                                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1">Тип оплаты</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center gap-2">
                                            <input type="radio" v-model="calculation.payment_type" value="hourly" />
                                            <span>Почасовая</span>
                                        </label>
                                        <label class="flex items-center gap-2">
                                            <input type="radio" v-model="calculation.payment_type" value="daily" />
                                            <span>Дневная</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        {{ calculation.payment_type === 'hourly' ? 'Отработано часов' : 'Отработано дней' }}
                                    </label>
                                    <input type="number" v-model="calculation.hours_or_days" step="0.5" min="0"
                                           class="w-full px-3 py-2 border rounded-md" />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium mb-1">Премия (₽)</label>
                                    <input type="number" v-model="calculation.bonus" step="100" min="0"
                                           class="w-full px-3 py-2 border rounded-md" />
                                </div>
                                
                                <button @click="previewCalculation" 
                                        class="w-full py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90">
                                    Рассчитать
                                </button>
                            </div>
                            
                            <!-- Результат расчета -->
                            <div v-if="previewResult" class="border-t pt-4 space-y-3">
                                <h4 class="font-semibold text-lg">Результат расчета</h4>
                                
                                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between">
                                        <span>Оклад:</span>
                                        <span class="font-medium">{{ formatPrice(previewResult.base_salary) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Премия:</span>
                                        <span class="font-medium text-green-600">{{ formatPrice(previewResult.bonus) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t">
                                        <span>Начислено:</span>
                                        <span class="font-bold">{{ formatPrice(previewResult.total_accrued) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>НДФЛ (13%):</span>
                                        <span class="text-red-600">{{ formatPrice(previewResult.ndfl) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t">
                                        <span>К выплате:</span>
                                        <span class="font-bold text-[#22c55e] text-lg">{{ formatPrice(previewResult.net_salary) }}</span>
                                    </div>
                                </div>
                                
                                <button @click="saveCalculation" 
                                        class="w-full py-2 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90">
                                    Сохранить расчет
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно деталей зарплаты -->
        <Teleport to="body">
            <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showDetailsModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Расчетный листок</h3>
                            <button @click="showDetailsModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedSalary" class="p-6 space-y-6">
                            <div class="text-center">
                                <h4 class="font-bold text-xl">РАСЧЕТНЫЙ ЛИСТОК</h4>
                                <p class="text-gray-600">{{ selectedSalary.employee_name }}</p>
                                <p class="text-sm text-gray-500">{{ getMonthName(selectedSalary.month) }} {{ selectedSalary.year }}</p>
                            </div>
                            
                            <div class="border rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Вид начисления</th>
                                            <th class="px-4 py-2 text-right">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-t">
                                            <td class="px-4 py-2">Оплата по {{ selectedSalary.payment_type === 'hourly' ? 'часам' : 'дням' }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(selectedSalary.base_salary) }}</td>
                                        </tr>
                                        <tr v-if="selectedSalary.bonus > 0" class="border-t">
                                            <td class="px-4 py-2">Премия</td>
                                            <td class="px-4 py-2 text-right text-green-600">{{ formatPrice(selectedSalary.bonus) }}</td>
                                        </tr>
                                        <tr class="border-t font-medium">
                                            <td class="px-4 py-2">Итого начислено</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(selectedSalary.total_accrued) }}</td>
                                        </tr>
                                        <tr class="border-t">
                                            <td class="px-4 py-2">НДФЛ (13%)</td>
                                            <td class="px-4 py-2 text-right text-red-600">{{ formatPrice(selectedSalary.ndfl) }}</td>
                                        </tr>
                                        <tr class="border-t font-bold">
                                            <td class="px-4 py-2">К выплате</td>
                                            <td class="px-4 py-2 text-right text-[#22c55e] text-lg">{{ formatPrice(selectedSalary.net_salary) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="text-sm text-gray-500 text-center">
                                <p>Ставка: {{ formatPrice(selectedSalary.hourly_rate) }} ₽/час</p>
                                <p>{{ selectedSalary.payment_type === 'hourly' ? 'Отработано часов: ' + selectedSalary.hours_or_days : 'Отработано дней: ' + selectedSalary.hours_or_days }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';

const props = defineProps({
    accountant: Object,
    employees: Array,
    totalSalary: Number,
    currentMonth: String,
    currentMonthNumber: Number,
    currentYear: Number,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number
});

// Состояние для модального окна ставки
const showRateModal = ref(false);
const selectedEmployeeForRate = ref(null);
const hourlyRateValue = ref(0);
const loading = ref(false);

// Состояние для расчета зарплаты
const showSalaryModal = ref(false);
const showDetailsModal = ref(false);
const selectedEmployee = ref(null);
const selectedSalary = ref(null);
const previewResult = ref(null);

// Параметры расчета
const calculation = ref({
    employee_id: null,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    payment_type: 'hourly',
    hours_or_days: 160,
    bonus: 0
});

const months = [
    { value: 1, name: 'Январь' },
    { value: 2, name: 'Февраль' },
    { value: 3, name: 'Март' },
    { value: 4, name: 'Апрель' },
    { value: 5, name: 'Май' },
    { value: 6, name: 'Июнь' },
    { value: 7, name: 'Июль' },
    { value: 8, name: 'Август' },
    { value: 9, name: 'Сентябрь' },
    { value: 10, name: 'Октябрь' },
    { value: 11, name: 'Ноябрь' },
    { value: 12, name: 'Декабрь' }
];

const years = [2024, 2025, 2026, 2027, 2028];

const getRoleName = (role) => {
    const roles = {
        'doctor': 'Врач',
        'admin': 'Администратор',
        'director': 'Директор',
        'accountant': 'Бухгалтер'
    };
    return roles[role] || role;
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getMonthName = (month) => {
    const m = months.find(m => m.value === month);
    return m ? m.name : '';
};

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

// Методы для редактирования ставки
const openRateModal = (employee) => {
    selectedEmployeeForRate.value = employee;
    hourlyRateValue.value = employee.hourly_rate || 0;
    showRateModal.value = true;
};

const closeRateModal = () => {
    showRateModal.value = false;
    selectedEmployeeForRate.value = null;
    hourlyRateValue.value = 0;
};

const updateHourlyRate = async () => {
    if (!selectedEmployeeForRate.value) return;
    
    loading.value = true;
    
    try {
        await axios.put(`/api/accountant/employees/${selectedEmployeeForRate.value.id}/hourly-rate`, {
            hourly_rate: hourlyRateValue.value
        });
        
        alert('Ставка успешно обновлена');
        closeRateModal();
        setTimeout(() => window.location.reload(), 1000);
        
    } catch (error) {
        console.error('Error updating rate:', error);
        alert('Ошибка при обновлении ставки');
    } finally {
        loading.value = false;
    }
};

// Методы для расчета зарплаты
const openSalaryModal = (employeeId) => {
    const employee = props.employees.find(e => e.id === employeeId);
    if (employee) {
        selectedEmployee.value = employee;
        calculation.value.employee_id = employeeId;
        calculation.value.hours_or_days = 160;
        calculation.value.bonus = 0;
        previewResult.value = null;
        showSalaryModal.value = true;
    }
};

const closeSalaryModal = () => {
    showSalaryModal.value = false;
    selectedEmployee.value = null;
    previewResult.value = null;
};

const previewCalculation = async () => {
    try {
        const response = await axios.post('/api/accountant/salary/preview', calculation.value);
        previewResult.value = response.data;
    } catch (error) {
        console.error('Error previewing salary:', error);
        alert('Ошибка при расчете');
    }
};

const saveCalculation = async () => {
    try {
        await axios.post('/api/accountant/salary/calculate', calculation.value);
        alert('Расчет сохранен');
        closeSalaryModal();
        setTimeout(() => window.location.reload(), 1000);
    } catch (error) {
        console.error('Error saving calculation:', error);
        alert('Ошибка при сохранении');
    }
};

const viewSalaryDetails = (employee) => {
    selectedSalary.value = employee.calculation_details;
    if (selectedSalary.value) {
        showDetailsModal.value = true;
    } else {
        alert('Детали расчета не найдены');
    }
};
</script>