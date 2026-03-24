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
        <!-- Блок выбора периода -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">
                        Месяц расчета
                    </label>
                    <select v-model="selectedMonth" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                        <option v-for="m in months" :key="m.value" :value="m.value">
                            {{ m.name }}
                        </option>
                    </select>
                </div>
                
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-black dark:text-white/70 mb-2">
                        Год
                    </label>
                    <select v-model="selectedYear" 
                            class="w-full px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                        <option v-for="y in years" :key="y" :value="y">
                            {{ y }}
                        </option>
                    </select>
                </div>
                
                <button @click="loadSalaryData" 
                        class="px-6 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition">
                    Рассчитать
                </button>
            </div>
        </div>

        <!-- Таблица сотрудников -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">
                КАЛЬКУЛЯТОР РАСЧЕТА ЗА {{ getMonthName(selectedMonth) }} {{ selectedYear }}
            </h2>
            
            <!-- Таблица сотрудников -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сотрудник</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Должность</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Ставка (₽/час)</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Отработано часов</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Начислено</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="employee in employeesList" :key="employee.id" 
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
                            <td class="py-3 text-black dark:text-white/70">
                                <div class="flex items-center gap-2">
                                    <input type="number" 
                                           v-model="employee.hours_worked" 
                                           step="0.5"
                                           min="0"
                                           :disabled="employee.is_paid"
                                           class="w-24 px-2 py-1 border rounded-md focus:ring-2 focus:ring-[#3b82f6] focus:border-transparent"
                                           :class="{'bg-gray-100 dark:bg-zinc-700': employee.is_paid}" />
                                    <span class="text-sm text-gray-500">ч.</span>
                                </div>
                            </td>
                            <td class="py-3 text-black dark:text-white/70 font-medium text-[#22c55e]">
                                {{ formatPrice(calculateEmployeeSalary(employee)) }}
                            </td>
                            <td class="py-3">
                                <div class="flex gap-2">
                                    <button v-if="!employee.is_paid" 
                                            @click="calculateAndSave(employee)"
                                            class="px-3 py-1 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition text-sm">
                                        Сохранить расчет
                                    </button>
                                    <button v-else 
                                            @click="viewSalaryDetails(employee)"
                                            class="px-3 py-1 border border-[#3b82f6] text-[#3b82f6] rounded-md hover:bg-[#3b82f6]/10 transition text-sm">
                                        Просмотр чека
                                    </button>
                                    <button @click="openRateModal(employee)" 
                                            class="px-3 py-1 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition text-sm">
                                        Ставка
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-zinc-700">
                            <td colspan="4" class="py-3 text-right font-medium text-black dark:text-white">Итого:</td>
                            <td class="py-3 font-medium text-[#22c55e]">{{ formatPrice(totalSalary) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Кнопка массовой выплаты -->
            <div v-if="hasUnpaidEmployees" class="mt-6 pt-4 border-t border-gray-200 dark:border-zinc-700">
                <button @click="payAllSalaries" 
                        class="px-6 py-2 bg-[#22c55e] text-white rounded-md hover:bg-[#22c55e]/90 transition">
                    Выплатить зарплату всем сотрудникам за {{ getMonthName(selectedMonth) }} {{ selectedYear }}
                </button>
            </div>
        </div>

        <!-- История начислений (оставляем как есть, только небольшие правки) -->
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow mt-6">
            <h2 class="text-2xl font-semibold text-black dark:text-white mb-6">ИСТОРИЯ НАЧИСЛЕНИЙ</h2>
            
            <!-- Фильтры -->
            <div class="flex flex-wrap gap-4 mb-6">
                <input type="text" v-model="historyFilters.employee_name" 
                       placeholder="Поиск по сотруднику..."
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md w-64" />
                <select v-model="historyFilters.month" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                    <option value="">Все месяцы</option>
                    <option v-for="m in months" :key="m.value" :value="m.value">{{ m.name }}</option>
                </select>
                <select v-model="historyFilters.year" class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md">
                    <option value="">Все годы</option>
                    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
                <button @click="resetHistoryFilters" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Сбросить
                </button>
            </div>

            <!-- Общая сумма -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Всего выплачено за выбранный период</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ formatPrice(historyTotalSum) }}</p>
            </div>

            <!-- Таблица истории -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Дата начисления</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сотрудник</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Должность</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Период</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Отработано часов</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Ставка</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Сумма</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="salary in paginatedHistory" :key="salary.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ salary.formatted_date }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ salary.employee_name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ getRoleName(salary.employee_role) }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ getMonthName(salary.month) }} {{ salary.year }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ salary.hours_worked }} ч.</td>
                            <td class="py-3 text-black dark:text-white/70">{{ formatPrice(salary.hourly_rate) }} ₽/ч</td>
                            <td class="py-3 text-black dark:text-white/70 font-medium text-[#22c55e]">{{ formatPrice(salary.total_amount) }}</td>
                            <td class="py-3">
                                <button @click="viewSalaryReceipt(salary.id)"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">
                                    Просмотр чека
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ paginatedHistory.length }} из {{ salaryHistory.length }} записей
                </div>
                <div class="flex gap-2">
                    <button v-if="historyPage > 1" @click="historyPage--" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Назад
                    </button>
                    <span class="px-3 py-1">Страница {{ historyPage }} из {{ historyTotalPages }}</span>
                    <button v-if="historyPage < historyTotalPages" @click="historyPage++" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Вперед
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно редактирования ставки (оставляем как есть) -->
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
                                            <td class="px-4 py-2">Оплата по часам</td>
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
                                <p>Отработано часов: {{ selectedSalary.hours_or_days }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно чека зарплаты (оставляем как есть) -->
        <Teleport to="body">
            <div v-if="showSalaryReceiptModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSalaryReceiptModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <!-- ... содержимое чека ... -->
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold text-black dark:text-white">Расчетный листок</h3>
                            <button @click="showSalaryReceiptModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6" id="salary-receipt-content" v-if="selectedSalaryReceipt">
                            <!-- ... остальной код чека ... -->
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold text-black dark:text-white">ELENA Beauty Clinic</h2>
                                <p class="text-gray-600 dark:text-gray-400">Расчетный листок № {{ selectedSalaryReceipt.salary_id }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Дата формирования: {{ selectedSalaryReceipt.created_at }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-zinc-700">
                                <div>
                                    <p class="text-sm text-gray-500">Сотрудник:</p>
                                    <p class="font-medium text-black dark:text-white">{{ selectedSalaryReceipt.employee_name }}</p>
                                    <p class="text-sm text-gray-500">Должность: {{ selectedSalaryReceipt.employee_role }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Период начисления:</p>
                                    <p class="font-medium text-black dark:text-white">{{ selectedSalaryReceipt.month_name }} {{ selectedSalaryReceipt.year }}</p>
                                    <p class="text-sm text-gray-500">Ставка: {{ formatPrice(selectedSalaryReceipt.hourly_rate) }} ₽/час</p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h4 class="font-semibold text-black dark:text-white mb-3">Детали расчета</h4>
                                <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg p-4">
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">Отработано часов:</span>
                                        <span class="font-medium">{{ selectedSalaryReceipt.hours_worked }} ч.</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-200 dark:border-zinc-700">
                                        <span class="text-gray-600">Оклад:</span>
                                        <span class="font-medium">{{ formatPrice(selectedSalaryReceipt.calculation_details.base_salary) }}</span>
                                    </div>
                                    <div v-if="selectedSalaryReceipt.calculation_details.bonus > 0" class="flex justify-between py-2">
                                        <span class="text-gray-600">Премия:</span>
                                        <span class="font-medium text-green-600">{{ formatPrice(selectedSalaryReceipt.calculation_details.bonus) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-200 dark:border-zinc-700">
                                        <span class="text-gray-600">Начислено всего:</span>
                                        <span class="font-bold">{{ formatPrice(selectedSalaryReceipt.calculation_details.total_accrued) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2">
                                        <span class="text-gray-600">НДФЛ (13%):</span>
                                        <span class="font-medium text-red-600">{{ formatPrice(selectedSalaryReceipt.calculation_details.ndfl) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 border-t border-gray-200 dark:border-zinc-700">
                                        <span class="text-lg font-bold text-black dark:text-white">К выплате:</span>
                                        <span class="text-xl font-bold text-green-600">{{ formatPrice(selectedSalaryReceipt.calculation_details.net_salary) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-zinc-700 pt-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Статус выплаты:</span>
                                    <span class="font-medium" :class="selectedSalaryReceipt.is_paid ? 'text-green-600' : 'text-yellow-600'">
                                        {{ selectedSalaryReceipt.is_paid ? 'Выплачено' : 'Начислено' }}
                                    </span>
                                </div>
                                <div v-if="selectedSalaryReceipt.payment_date" class="flex justify-between mt-2">
                                    <span class="text-gray-600">Дата выплаты:</span>
                                    <span class="font-medium">{{ selectedSalaryReceipt.payment_date }}</span>
                                </div>
                            </div>

                            <div class="mt-8 pt-4 text-center text-sm text-gray-500">
                                <p>Бухгалтер: {{ accountant.employee_name }}</p>
                                <p class="mt-2">Расчет произведен автоматически</p>
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-end gap-3">
                            <button @click="printSalaryReceipt" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                🖨️ Печать
                            </button>
                            <button @click="exportSalaryToExcel" 
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                                📊 Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import * as XLSX from 'xlsx';

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

// Состояния
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());
const employeesList = ref([]);
const loading = ref(false);

// Состояния для модальных окон
const showRateModal = ref(false);
const selectedEmployeeForRate = ref(null);
const hourlyRateValue = ref(0);
const showDetailsModal = ref(false);
const selectedSalary = ref(null);
const showSalaryReceiptModal = ref(false);
const selectedSalaryReceipt = ref(null);

// История начислений
const salaryHistory = ref([]);
const historyTotalSum = ref(0);
const historyFilters = ref({
    employee_name: '',
    month: '',
    year: ''
});
const historyPage = ref(1);
const itemsPerPage = 10;

// Данные для выбора
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

const years = [2023, 2024, 2025, 2026, 2027, 2028];

// Computed свойства
const hasUnpaidEmployees = computed(() => {
    return employeesList.value.some(e => !e.is_paid && e.hours_worked > 0);
});

const totalSalary = computed(() => {
    return employeesList.value.reduce((sum, emp) => {
        return sum + calculateEmployeeSalary(emp);
    }, 0);
});

const paginatedHistory = computed(() => {
    const start = (historyPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return salaryHistory.value.slice(start, end);
});

const historyTotalPages = computed(() => {
    return Math.ceil(salaryHistory.value.length / itemsPerPage);
});

// Методы
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

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const calculateEmployeeSalary = (employee) => {
    if (!employee.hours_worked || employee.hours_worked <= 0) return 0;
    const rate = employee.hourly_rate || 0;
    const totalAccrued = rate * employee.hours_worked;
    const ndfl = totalAccrued * 0.13;
    return totalAccrued - ndfl;
};

// Загрузка данных о зарплате
const loadSalaryData = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/salary/calculate', {
            params: {
                month: selectedMonth.value,
                year: selectedYear.value
            }
        });
        
        // Преобразуем данные для отображения
        employeesList.value = response.data.employees.map(emp => ({
            id: emp.id,
            name: emp.name,
            role: emp.role,
            hourly_rate: emp.hourly_rate,
            hours_worked: emp.hours_worked || 0,
            total_amount: emp.total_amount,
            is_paid: emp.is_paid || false,
            salary_id: emp.salary_id
        }));
    } catch (error) {
        console.error('Error loading salary data:', error);
        alert('Ошибка при загрузке данных');
    } finally {
        loading.value = false;
    }
};

// Сохранение расчета
const calculateAndSave = async (employee) => {
    if (!employee.hours_worked || employee.hours_worked <= 0) {
        alert('Укажите количество отработанных часов');
        return;
    }
    
    try {
        await axios.post('/api/accountant/salary/calculate', {
            employee_id: employee.id,
            month: selectedMonth.value,
            year: selectedYear.value,
            payment_type: 'hourly',
            hours_or_days: employee.hours_worked,
            bonus: 0
        });
        
        alert(`Расчет для ${employee.name} сохранен`);
        await loadSalaryData(); // Перезагружаем данные
    } catch (error) {
        console.error('Error saving salary:', error);
        alert('Ошибка при сохранении расчета');
    }
};

// Просмотр деталей
const viewSalaryDetails = (employee) => {
    // Ищем сохраненную зарплату
    const salary = employeesList.value.find(e => e.id === employee.id);
    if (salary && salary.calculation_details) {
        selectedSalary.value = {
            employee_name: employee.name,
            month: selectedMonth.value,
            year: selectedYear.value,
            ...salary.calculation_details
        };
        showDetailsModal.value = true;
    } else {
        alert('Детали расчета не найдены');
    }
};

// Редактирование ставки
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
        await loadSalaryData(); // Перезагружаем данные
    } catch (error) {
        console.error('Error updating rate:', error);
        alert('Ошибка при обновлении ставки');
    } finally {
        loading.value = false;
    }
};

// Выплата зарплаты
const payAllSalaries = async () => {
    const unpaidEmployees = employeesList.value.filter(e => !e.is_paid && e.hours_worked > 0);
    if (unpaidEmployees.length === 0) {
        alert('Нет невыплаченных зарплат');
        return;
    }
    
    if (!confirm(`Выплатить зарплату ${unpaidEmployees.length} сотрудникам за ${getMonthName(selectedMonth.value)} ${selectedYear.value}?`)) return;
    
    try {
        await axios.post('/api/accountant/salary/pay-all', {
            month: selectedMonth.value,
            year: selectedYear.value
        });
        
        alert('Зарплата успешно выплачена');
        await loadSalaryData(); // Перезагружаем данные
        await loadSalaryHistory(); // Обновляем историю
    } catch (error) {
        console.error('Error paying salaries:', error);
        alert('Ошибка при выплате зарплаты');
    }
};

// История начислений
const loadSalaryHistory = async () => {
    try {
        const response = await axios.get('/api/accountant/salary/history', {
            params: historyFilters.value
        });
        salaryHistory.value = response.data.salaries;
        historyTotalSum.value = response.data.total_sum;
        historyPage.value = 1;
    } catch (error) {
        console.error('Ошибка загрузки истории:', error);
    }
};

const resetHistoryFilters = () => {
    historyFilters.value = {
        employee_name: '',
        month: '',
        year: ''
    };
};

const viewSalaryReceipt = async (salaryId) => {
    try {
        const response = await axios.get(`/api/accountant/salary/receipt/${salaryId}`);
        selectedSalaryReceipt.value = response.data;
        showSalaryReceiptModal.value = true;
    } catch (error) {
        console.error('Ошибка загрузки чека:', error);
        alert('Ошибка при загрузке данных чека');
    }
};

const printSalaryReceipt = () => {
    const printContent = document.getElementById('salary-receipt-content');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Расчетный листок ${selectedSalaryReceipt.value.employee_name} ${selectedSalaryReceipt.value.month_name} ${selectedSalaryReceipt.value.year}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .receipt { max-width: 800px; margin: 0 auto; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    th { background-color: #f5f5f5; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    @media print { button { display: none; } }
                </style>
            </head>
            <body>
                <div class="receipt">${printContent.innerHTML}</div>
                <script>window.onload = () => { window.print(); setTimeout(() => window.close(), 500); }<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
};

const exportSalaryToExcel = () => {
    try {
        const data = selectedSalaryReceipt.value;
        if (!data) return;
        
        const excelData = [
            ['ELENA Beauty Clinic'],
            ['Расчетный листок'],
            [],
            ['Сотрудник:', data.employee_name],
            ['Должность:', data.employee_role],
            ['Период:', `${data.month_name} ${data.year}`],
            ['Дата формирования:', data.created_at],
            [],
            ['ДЕТАЛИ РАСЧЕТА'],
            ['Показатель', 'Значение (₽)'],
            ['Отработано часов', data.hours_worked],
            ['Ставка (₽/час)', data.hourly_rate],
            ['Оклад', data.calculation_details.base_salary],
            ['Начислено всего', data.calculation_details.total_accrued],
            ['НДФЛ (13%)', data.calculation_details.ndfl],
            ['К выплате', data.calculation_details.net_salary],
            [],
            ['СТАТУС ВЫПЛАТЫ'],
            ['Статус:', data.is_paid ? 'Выплачено' : 'Начислено'],
            data.payment_date ? ['Дата выплаты:', data.payment_date] : [],
            [],
            ['Бухгалтер:', props.accountant.employee_name]
        ];
        
        const ws = XLSX.utils.aoa_to_sheet(excelData);
        ws['!cols'] = [{ wch: 25 }, { wch: 30 }];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, `Расчет_${data.employee_name}_${data.month_name}_${data.year}`);
        XLSX.writeFile(wb, `Расчетный_листок_${data.employee_name}_${data.month_name}_${data.year}.xlsx`);
    } catch (error) {
        console.error('Ошибка при экспорте:', error);
        alert('Ошибка при экспорте в Excel');
    }
};

// Отслеживаем изменение фильтров
watch(historyFilters, () => {
    loadSalaryHistory();
}, { deep: true });

// Загружаем данные при монтировании
onMounted(() => {
    loadSalaryData();
    loadSalaryHistory();
});
</script>

<style scoped>
/* Стили при необходимости */
</style>