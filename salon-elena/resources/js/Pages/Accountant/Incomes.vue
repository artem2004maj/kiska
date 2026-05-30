<!-- resources/js/Pages/Accountant/Incomes.vue -->
<template>
    <AccountantLayout 
        :accountant="accountant"
        :pageTitle="'ДОХОДЫ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-black dark:text-white">ДОХОДЫ ОТ УСЛУГ</h2>
                <button @click="loadIncomes" class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition">
                    🔄 Обновить
                </button>
            </div>
            
            <!-- Фильтры -->
            <div class="flex flex-wrap gap-4 mb-6">
                <input type="date" v-model="filters.date_from" 
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="date" v-model="filters.date_to"
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md" />
                <input type="text" v-model="filters.client_search" placeholder="Поиск по клиенту..."
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md w-64" />
                <input type="text" v-model="filters.service_search" placeholder="Поиск по услуге..."
                       class="px-4 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md w-64" />
                <button @click="resetFilters" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Сбросить
                </button>
            </div>

            <!-- Общая сумма -->
            <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">Всего доходов за выбранный период</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formatPrice(totalSum) }}</p>
            </div>

            <!-- Таблица доходов -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-zinc-700">
                            <th class="text-left py-3 text-black dark:text-white font-medium">Дата</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Клиент</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Услуга</th>
                            <th class="text-right py-3 text-black dark:text-white font-medium">Сумма</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Номер чека</th>
                            <th class="text-left py-3 text-black dark:text-white font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="income in paginatedIncomes" :key="income.id" 
                            class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            <td class="py-3 text-black dark:text-white/70">{{ income.formatted_date }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ income.client_name }}</td>
                            <td class="py-3 text-black dark:text-white/70">{{ income.services_list }}</td>
                            <td class="py-3 text-right font-medium text-green-600">{{ formatPrice(income.total_price) }}</td>
                            <td class="py-3">
                                <span class="text-sm font-mono text-gray-600 dark:text-gray-400">
                                    {{ income.contract_number }}
                                </span>
                            </td>
                            <td class="py-3">
                                <button @click="viewReceipt(income.id)"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-sm">
                                    Чек
                                </button>
                            </td>
                        </tr>
                        <tr v-if="paginatedIncomes.length === 0">
                            <td colspan="6" class="py-8 text-center text-gray-500">
                                Нет данных за выбранный период
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ paginatedIncomes.length }} из {{ incomes.length }} записей
                </div>
                <div class="flex gap-2">
                    <button v-if="currentPage > 1" @click="currentPage--" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Назад
                    </button>
                    <span class="px-3 py-1">Страница {{ currentPage }} из {{ totalPages }}</span>
                    <button v-if="currentPage < totalPages" @click="currentPage++" 
                            class="px-3 py-1 border rounded-md hover:bg-gray-100 dark:hover:bg-zinc-800">
                        Вперед
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно чека -->
        <Teleport to="body">
            <div v-if="showReceiptModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeReceiptModal"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Чек №{{ receiptData?.contract_number }}</h3>
                            <button @click="closeReceiptModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6" id="receipt-content" v-if="receiptData">
                            <!-- Шапка чека -->
                            <div class="text-center mb-8">
                                <img v-if="company?.logo_url" 
                                    :src="company.logo_url" 
                                    class="h-16 mx-auto mb-2 object-contain" />
                                <h2 class="text-2xl font-bold">{{ company?.name || 'Косметологическая клиника' }}</h2>
                                <p class="text-sm text-gray-500">{{ company?.legal_address || company?.actual_address || '' }}</p>
                                <p class="text-sm text-gray-500">ИНН: {{ company?.inn || '—' }} | ОГРН: {{ company?.ogrn || '—' }}</p>
                                <p class="text-sm text-gray-500">Тел.: {{ company?.phone || '' }} | Email: {{ company?.email || '' }}</p>
                                <p class="text-xl font-bold mt-4">ЧЕК № {{ receiptData.contract_number }}</p>
                                <p class="text-sm text-gray-500">Дата: {{ receiptData.date }}</p>
                            </div>

                            <!-- Информация о клиенте -->
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                                <h4 class="font-semibold mb-2">Клиент:</h4>
                                <p><strong>{{ receiptData.client?.name }}</strong></p>
                                <p class="text-sm">Телефон: {{ receiptData.client?.phone || '—' }}</p>
                                <p class="text-sm">Email: {{ receiptData.client?.email || '—' }}</p>
                            </div>

                            <!-- Услуги -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-3">Услуги</h4>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left px-4 py-2">Наименование</th>
                                                <th class="text-right px-4 py-2">Кол-во</th>
                                                <th class="text-right px-4 py-2">Цена</th>
                                                <th class="text-right px-4 py-2">Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, idx) in receiptData.services" :key="idx" class="border-t">
                                                <td class="px-4 py-2">{{ item.name }}</td>
                                                <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t font-bold">
                                                <td colspan="3" class="px-4 py-2 text-right">Итого услуги:</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(receiptData.total_services) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Материалы (если есть) -->
                            <div v-if="receiptData.materials && receiptData.materials.length > 0" class="mb-6">
                                <h4 class="font-semibold mb-3">Материалы</h4>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left px-4 py-2">Наименование</th>
                                                <th class="text-right px-4 py-2">Кол-во</th>
                                                <th class="text-right px-4 py-2">Цена</th>
                                                <th class="text-right px-4 py-2">Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, idx) in receiptData.materials" :key="idx" class="border-t">
                                                <td class="px-4 py-2">{{ item.name }} \({ item.unit }\)</td>
                                                <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-t font-bold">
                                                <td colspan="3" class="px-4 py-2 text-right">Итого материалы:</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(receiptData.total_materials) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Итог -->
                            <div class="mt-6 pt-4 border-t">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>ИТОГО К ОПЛАТЕ:</span>
                                    <span class="text-green-600">{{ formatPrice(receiptData.total_amount) }}</span>
                                </div>
                                <p class="text-sm text-gray-500 text-center mt-4">В том числе НДС не облагается</p>
                            </div>

                            <!-- Подписи -->
                            <div class="grid grid-cols-2 gap-8 mt-8 pt-4 border-t">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Врач</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ receiptData.doctor?.name || '—' }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Бухгалтер</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ receiptData.accountant?.name || accountant?.employee_name }}</p>
                                </div>
                            </div>

                            <!-- Подвал -->
                            <div class="mt-8 pt-4 text-center text-xs text-gray-400">
                                Спасибо за визит! Ждем вас снова.
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t px-6 py-4 flex justify-end gap-3">
                            <button @click="printReceipt" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                🖨️ Печать
                            </button>
                            <button @click="exportReceiptToExcel" 
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
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    accountant: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number,
    company: Object
});

const filters = ref({
    date_from: '',
    date_to: '',
    client_search: '',
    service_search: ''
});

const incomes = ref([]);
const totalSum = ref(0);
const currentPage = ref(1);
const itemsPerPage = 10;
const showReceiptModal = ref(false);
const selectedContractId = ref(null);
const receiptData = ref(null);
const loading = ref(false);

// Загрузка данных
const loadIncomes = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/incomes', {
            params: filters.value
        });
        incomes.value = response.data.incomes || [];
        totalSum.value = response.data.total_sum || 0;
        currentPage.value = 1;
    } catch (error) {
        console.error('Ошибка загрузки доходов:', error);
    } finally {
        loading.value = false;
    }
};

// Загрузка чека
const loadReceipt = async (contractId) => {
    try {
        const response = await axios.get(`/api/accountant/receipts/${contractId}`);
        receiptData.value = response.data;
    } catch (error) {
        console.error('Ошибка загрузки чека:', error);
        alert('Ошибка при загрузке чека');
    }
};

// Пагинация
const paginatedIncomes = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return incomes.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(incomes.value.length / itemsPerPage);
});

// Форматирование цены
const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const formatDate = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

// Сброс фильтров
const resetFilters = () => {
    filters.value = {
        date_from: '',
        date_to: '',
        client_search: '',
        service_search: ''
    };
};

// Просмотр чека
const viewReceipt = async (contractId) => {
    selectedContractId.value = contractId;
    await loadReceipt(contractId);
    showReceiptModal.value = true;
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
    receiptData.value = null;
    selectedContractId.value = null;
};

// Печать чека
const printReceipt = () => {
    const printContent = document.getElementById('receipt-content');
    const comp = props.company || {};
    const currentDate = new Date().toLocaleDateString('ru-RU');
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Чек ${receiptData.value?.contract_number}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 12pt;
                    padding: 15mm;
                }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 10px; }
                .company-name { font-size: 18pt; font-weight: bold; }
                .company-details { font-size: 10pt; color: #555; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
                th { background: #f5f5f5; text-align: center; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .signatures { display: flex; justify-content: space-between; margin-top: 30px; }
                .signature { text-align: center; flex: 1; }
                .signature-line { margin-top: 30px; padding-top: 5px; border-top: 1px solid #000; }
                .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 8pt; text-align: center; color: #888; }
                @media print { body { padding: 10mm; } button { display: none; } }
            </style>
        </head>
        <body>${printContent.innerHTML}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

// Экспорт чека в Excel
const exportReceiptToExcel = () => {
    if (!receiptData.value) return;
    
    const data = receiptData.value;
    const comp = props.company || {};
    
    const excelData = [
        [comp.name || 'Косметологическая клиника'],
        ['ЧЕК'],
        [`№ ${data.contract_number}`],
        [`Дата: ${data.date}`],
        [],
        ['КЛИЕНТ'],
        ['Имя:', data.client?.name || '—'],
        ['Телефон:', data.client?.phone || '—'],
        ['Email:', data.client?.email || '—'],
        [],
        ['УСЛУГИ'],
        ['Наименование', 'Кол-во', 'Цена', 'Сумма']
    ];
    
    data.services.forEach(service => {
        excelData.push([service.name, service.quantity, service.price, service.total]);
    });
    
    excelData.push([], ['Итого услуги:', '', '', data.total_services]);
    
    if (data.materials && data.materials.length > 0) {
        excelData.push([], ['МАТЕРИАЛЫ']);
        excelData.push(['Наименование', 'Кол-во', 'Цена', 'Сумма']);
        data.materials.forEach(material => {
            excelData.push([material.name, material.quantity, material.price, material.total]);
        });
        excelData.push([], ['Итого материалы:', '', '', data.total_materials]);
    }
    
    excelData.push([], ['ИТОГО К ОПЛАТЕ:', '', '', data.total_amount]);
    excelData.push([], ['Врач:', data.doctor?.name || '—']);
    excelData.push(['Бухгалтер:', data.accountant?.name || '—']);
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    ws['!cols'] = [{ wch: 30 }, { wch: 10 }, { wch: 12 }, { wch: 15 }];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Чек_${data.contract_number}`);
    XLSX.writeFile(wb, `Чек_${data.contract_number}.xlsx`);
};

// Отслеживаем изменение фильтров
watch(filters, () => {
    loadIncomes();
}, { deep: true });

// Загружаем данные при монтировании
loadIncomes();
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>