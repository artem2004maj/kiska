<!-- resources/js/Components/ReceiptModal.vue -->
<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="close"></div>
                
                <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Заголовок -->
                    <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-black dark:text-white">Чек № {{ receiptData.contract_number }}</h3>
                        <button @click="close" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Содержимое чека -->
                    <div class="p-6" id="receipt-content">
                        <!-- Шапка чека -->
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-black dark:text-white">ELENA Beauty Clinic</h2>
                            <p class="text-gray-600 dark:text-gray-400">Лицензия № ЛО-77-01-123456</p>
                            <p class="text-gray-600 dark:text-gray-400">г. Москва, ул. Тверская, д. 10</p>
                            <p class="text-gray-600 dark:text-gray-400">Тел: +7 (495) 123-45-67</p>
                        </div>

                        <!-- Информация о чеке -->
                        <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-zinc-700">
                            <div>
                                <p class="text-sm text-gray-500">Номер чека:</p>
                                <p class="font-medium text-black dark:text-white">{{ receiptData.contract_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Дата:</p>
                                <p class="font-medium text-black dark:text-white">{{ receiptData.date }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Клиент:</p>
                                <p class="font-medium text-black dark:text-white">{{ receiptData.client?.name }}</p>
                                <p class="text-sm text-gray-500">Тел: {{ receiptData.client?.phone }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Врач:</p>
                                <p class="font-medium text-black dark:text-white">{{ receiptData.doctor?.name }}</p>
                            </div>
                        </div>

                        <!-- Услуги -->
                        <div class="mb-6">
                            <h4 class="font-semibold text-black dark:text-white mb-3">Оказанные услуги</h4>
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="text-left py-2 px-3 text-sm font-medium">Наименование</th>
                                        <th class="text-center py-2 px-3 text-sm font-medium">Кол-во</th>
                                        <th class="text-right py-2 px-3 text-sm font-medium">Цена</th>
                                        <th class="text-right py-2 px-3 text-sm font-medium">Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="service in receiptData.services" :key="service.name">
                                        <td class="py-2 px-3">{{ service.name }}</td>
                                        <td class="text-center py-2 px-3">{{ service.quantity }}</td>
                                        <td class="text-right py-2 px-3">{{ formatPrice(service.price) }}</td>
                                        <td class="text-right py-2 px-3">{{ formatPrice(service.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Материалы -->
                        <div v-if="receiptData.materials && receiptData.materials.length > 0" class="mb-6">
                            <h4 class="font-semibold text-black dark:text-white mb-3">Использованные материалы</h4>
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="text-left py-2 px-3 text-sm font-medium">Наименование</th>
                                        <th class="text-center py-2 px-3 text-sm font-medium">Кол-во</th>
                                        <th class="text-right py-2 px-3 text-sm font-medium">Цена</th>
                                        <th class="text-right py-2 px-3 text-sm font-medium">Сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="material in receiptData.materials" :key="material.name">
                                        <td class="py-2 px-3">{{ material.name }}</td>
                                        <td class="text-center py-2 px-3">{{ material.quantity }} {{ material.unit }}</td>
                                        <td class="text-right py-2 px-3">{{ formatPrice(material.price) }}</td>
                                        <td class="text-right py-2 px-3">{{ formatPrice(material.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Итог -->
                        <div class="border-t border-gray-200 dark:border-zinc-700 pt-4 mt-4">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Итого услуги:</span>
                                        <span class="font-medium">{{ formatPrice(receiptData.total_services) }}</span>
                                    </div>
                                    <div v-if="receiptData.total_materials > 0" class="flex justify-between mb-2">
                                        <span class="text-gray-600">Итого материалы:</span>
                                        <span class="font-medium">{{ formatPrice(receiptData.total_materials) }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-zinc-700">
                                        <span class="text-lg font-bold">ИТОГО К ОПЛАТЕ:</span>
                                        <span class="text-xl font-bold text-green-600">{{ formatPrice(receiptData.total_amount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Подпись -->
                        <div class="mt-8 pt-4 text-center text-sm text-gray-500">
                            <p>Бухгалтер: {{ receiptData.accountant?.name }}</p>
                            <p class="mt-2">Спасибо за посещение! Будем рады видеть вас снова!</p>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 px-6 py-4 flex justify-end gap-3">
                        <button @click="printReceipt" 
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                            🖨️ Печать
                        </button>
                        <button @click="exportToExcel" 
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                            📊 Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import * as XLSX from 'xlsx';

const props = defineProps({
    show: Boolean,
    contractId: Number
});

const emit = defineEmits(['close']);

const receiptData = ref({
    contract_number: '',
    date: '',
    client: {},
    doctor: {},
    accountant: {},
    services: [],
    materials: [],
    total_services: 0,
    total_materials: 0,
    total_amount: 0
});

const loading = ref(false);

// Загрузка данных чека
const loadReceipt = async () => {
    if (!props.contractId) return;
    
    loading.value = true;
    try {
        const response = await axios.get(`/api/accountant/receipts/${props.contractId}`);
        receiptData.value = response.data;
    } catch (error) {
        console.error('Ошибка загрузки чека:', error);
        alert('Ошибка при загрузке данных чека');
    } finally {
        loading.value = false;
    }
};

// Форматирование цены
const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

// Печать чека
const printReceipt = () => {
    const printContent = document.getElementById('receipt-content');
    const originalTitle = document.title;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Чек ${receiptData.value.contract_number}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                        color: #000;
                    }
                    .receipt {
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        padding: 8px;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                    }
                    th {
                        background-color: #f5f5f5;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .total-row {
                        font-weight: bold;
                        font-size: 1.2em;
                    }
                    @media print {
                        body {
                            margin: 0;
                            padding: 20px;
                        }
                        button {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    ${printContent.innerHTML}
                </div>
                <script>
                    window.onload = () => {
                        window.print();
                        setTimeout(() => window.close(), 500);
                    };
                <\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
};

// Экспорт в Excel
const exportToExcel = () => {
    // Подготовка данных для Excel
    const excelData = [];
    
    // Заголовок
    excelData.push(['ELENA Beauty Clinic']);
    excelData.push(['Чек № ' + receiptData.value.contract_number]);
    excelData.push(['Дата: ' + receiptData.value.date]);
    excelData.push(['Клиент: ' + receiptData.value.client?.name]);
    excelData.push(['Телефон: ' + receiptData.value.client?.phone]);
    excelData.push(['Врач: ' + receiptData.value.doctor?.name]);
    excelData.push([]);
    
    // Услуги
    excelData.push(['УСЛУГИ']);
    excelData.push(['Наименование', 'Кол-во', 'Цена', 'Сумма']);
    receiptData.value.services.forEach(service => {
        excelData.push([service.name, service.quantity, service.price, service.total]);
    });
    excelData.push([]);
    
    // Материалы
    if (receiptData.value.materials && receiptData.value.materials.length > 0) {
        excelData.push(['МАТЕРИАЛЫ']);
        excelData.push(['Наименование', 'Кол-во', 'Цена', 'Сумма']);
        receiptData.value.materials.forEach(material => {
            excelData.push([material.name, `${material.quantity} ${material.unit}`, material.price, material.total]);
        });
        excelData.push([]);
    }
    
    // Итоги
    excelData.push(['Итого услуги:', '', '', receiptData.value.total_services]);
    if (receiptData.value.total_materials > 0) {
        excelData.push(['Итого материалы:', '', '', receiptData.value.total_materials]);
    }
    excelData.push(['ИТОГО К ОПЛАТЕ:', '', '', receiptData.value.total_amount]);
    excelData.push([]);
    excelData.push(['Бухгалтер: ' + receiptData.value.accountant?.name]);
    excelData.push(['Спасибо за посещение!']);
    
    // Создание и скачивание файла
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Чек_${receiptData.value.contract_number}`);
    XLSX.writeFile(wb, `Чек_${receiptData.value.contract_number}.xlsx`);
};

// Закрытие модального окна
const close = () => {
    emit('close');
};

// Загружаем данные при открытии
watch(() => props.show, (newVal) => {
    if (newVal && props.contractId) {
        loadReceipt();
    }
});
</script>