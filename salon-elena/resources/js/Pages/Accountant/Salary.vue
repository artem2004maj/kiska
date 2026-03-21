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
            
            <!-- Период -->
            <div class="flex flex-wrap gap-4 mb-6">
                <select v-model="selectedMonth" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.name }}</option>
                </select>
                <select v-model="selectedYear" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
                <button @click="calculateSalary" class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition">
                    Рассчитать
                </button>
                <button @click="payAllSalaries" class="px-4 py-2 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition">
                    Выплатить всем
                </button>
            </div>

            <!-- Таблица зарплаты -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сотрудник</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Должность</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Отработано часов</th>
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
                            <td class="py-3 text-black dark:text-white/70">{{ employee.hours_worked }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ formatPrice(employee.hourly_rate) }}</td>
                            <td class="py-3 text-black dark:text-white/70 font-medium text-[#22c55e]">{{ formatPrice(employee.total_amount) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full" 
                                      :class="employee.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                                    {{ employee.is_paid ? 'Выплачено' : 'Начислено' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <button v-if="!employee.is_paid" 
                                        @click="paySalary(employee.id)"
                                        class="px-3 py-1 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition text-sm">
                                    Выплатить
                                </button>
                                <span v-else class="text-xs text-gray-400">
                                    Выплачено
                                </span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-zinc-700">
                            <td colspan="4" class="py-3 text-right font-medium text-black dark:text-white">Итого:</td>
                            <td class="py-3 font-medium text-[#22c55e]">{{ formatPrice(totalSalary) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Сводка по месяцам -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="font-semibold text-lg mb-3">Сводка по месяцам</h3>
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <div v-for="month in monthlySummary" :key="month.month" 
                         class="p-3 bg-gray-50 dark:bg-zinc-800 rounded-lg text-center cursor-pointer hover:bg-gray-100"
                         @click="loadMonth(month.month, month.year)">
                        <p class="font-medium">{{ getMonthName(month.month) }}</p>
                        <p class="text-sm text-gray-500">{{ month.year }}</p>
                        <p class="text-[#22c55e] font-semibold">{{ formatPrice(month.total) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно успешной выплаты -->
        <Teleport to="body">
            <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSuccessModal = false"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-md w-full p-6 text-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Зарплата выплачена</h3>
                        <p class="text-gray-600 mb-4">{{ successMessage }}</p>
                        <button @click="showSuccessModal = false" class="px-4 py-2 bg-[#3b82f6] text-white rounded-md">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
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

const selectedMonth = ref(props.currentMonthNumber || new Date().getMonth() + 1);
const selectedYear = ref(props.currentYear || new Date().getFullYear());
const employeesData = ref(props.employees || []);
const totalSalaryData = ref(props.totalSalary || 0);
const showSuccessModal = ref(false);
const successMessage = ref('');

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

const monthlySummary = computed(() => {
    const summary = [];
    const currentYear = new Date().getFullYear();
    
    for (let i = 1; i <= 12; i++) {
        summary.push({
            month: i,
            year: currentYear,
            total: 0
        });
    }
    
    return summary;
});

const employees = computed(() => employeesData.value);
const totalSalary = computed(() => totalSalaryData.value);

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const getRoleName = (role) => {
    const roles = {
        'doctor': 'Врач',
        'admin': 'Администратор',
        'director': 'Директор',
        'accountant': 'Бухгалтер'
    };
    return roles[role] || role;
};

const getMonthName = (month) => {
    const m = months.find(m => m.value === month);
    return m ? m.name : '';
};

const calculateSalary = async () => {
    try {
        const response = await axios.post('/api/accountant/salary/calculate', {
            month: selectedMonth.value,
            year: selectedYear.value
        });
        employeesData.value = response.data.employees;
        totalSalaryData.value = response.data.totalSalary;
    } catch (error) {
        alert('Ошибка при расчете зарплаты');
    }
};

const paySalary = async (employeeId) => {
    try {
        await axios.post('/api/accountant/salary/pay', {
            employee_id: employeeId,
            month: selectedMonth.value,
            year: selectedYear.value
        });
        successMessage.value = 'Зарплата выплачена';
        showSuccessModal.value = true;
        calculateSalary();
    } catch (error) {
        alert('Ошибка при выплате зарплаты');
    }
};

const payAllSalaries = async () => {
    if (!confirm('Выплатить зарплату всем сотрудникам за выбранный месяц?')) return;
    
    try {
        const response = await axios.post('/api/accountant/salary/pay-all', {
            month: selectedMonth.value,
            year: selectedYear.value
        });
        successMessage.value = response.data.message;
        showSuccessModal.value = true;
        calculateSalary();
    } catch (error) {
        alert('Ошибка при выплате зарплаты');
    }
};

const loadMonth = (month, year) => {
    selectedMonth.value = month;
    selectedYear.value = year;
    calculateSalary();
};

onMounted(() => {
    calculateSalary();
});
</script>