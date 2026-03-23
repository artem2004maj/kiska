<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('close')"></div>
                <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Заголовок -->
                    <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-xl font-semibold">Накладная на приход материалов №{{ receiptData?.number }}</h3>
                        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Содержимое накладной -->
                    <div v-if="receiptData" class="p-6" ref="receiptContent">
                        <!-- Шапка документа -->
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold mb-2">НАКЛАДНАЯ №{{ receiptData.number }}</h2>
                            <p class="text-gray-500">на приход товарно-материальных ценностей</p>
                        </div>

                        <!-- Дата -->
                        <div class="mb-6 text-right">
                            <p>г. {{ receiptData.city }} <span class="ml-4">{{ formatDateFull(receiptData.received_at) }}</span></p>
                        </div>

                        <!-- Отправитель и получатель -->
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="border p-4 rounded-lg">
                                <h4 class="font-semibold mb-2 text-blue-600">Поставщик (Отправитель):</h4>
                                <p><span class="text-gray-500">Наименование:</span> {{ receiptData.supplier_name }}</p>
                                <p><span class="text-gray-500">ИНН:</span> {{ receiptData.supplier_inn || '—' }}</p>
                                <p><span class="text-gray-500">Адрес:</span> {{ receiptData.supplier_address || '—' }}</p>
                                <p><span class="text-gray-500">Телефон:</span> {{ receiptData.supplier_phone || '—' }}</p>
                                <p><span class="text-gray-500">Email:</span> {{ receiptData.supplier_email || '—' }}</p>
                            </div>
                            <div class="border p-4 rounded-lg">
                                <h4 class="font-semibold mb-2 text-green-600">Получатель:</h4>
                                <p><span class="text-gray-500">Организация:</span> ООО "Елена"</p>
                                <p><span class="text-gray-500">ИНН:</span> 1234567890</p>
                                <p><span class="text-gray-500">Адрес:</span> г. Москва, ул. Примерная, д. 1</p>
                                <p><span class="text-gray-500">Телефон:</span> +7 (999) 123-45-67</p>
                                <p><span class="text-gray-500">Email:</span> info@elena.ru</p>
                            </div>
                        </div>

                        <!-- Информация о заказе -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p><span class="text-gray-500">Дата заказа:</span> {{ formatDateFull(receiptData.created_at) }}</p>
                                    <p><span class="text-gray-500">Дата подтверждения:</span> {{ formatDateFull(receiptData.confirmed_at) }}</p>
                                </div>
                                <div>
                                    <p><span class="text-gray-500">Дата получения:</span> {{ formatDateFull(receiptData.received_at) }}</p>
                                    <p><span class="text-gray-500">Номер договора:</span> {{ receiptData.number }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Таблица материалов -->
                        <div class="mb-8">
                            <h4 class="font-semibold mb-3">Список материалов:</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-100 dark:bg-zinc-800">
                                            <th class="border p-2 text-left">№</th>
                                            <th class="border p-2 text-left">Наименование</th>
                                            <th class="border p-2 text-right">Количество</th>
                                            <th class="border p-2 text-right">Ед. изм.</th>
                                            <th class="border p-2 text-right">Цена за ед.</th>
                                            <th class="border p-2 text-right">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in receiptData.items" :key="index">
                                            <td class="border p-2 text-center">{{ index + 1 }}</td>
                                            <td class="border p-2">{{ item.material_name }}</td>
                                            <td class="border p-2 text-right">{{ item.quantity }}</td>
                                            <td class="border p-2 text-right">{{ item.unit || 'шт' }}</td>
                                            <td class="border p-2 text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="border p-2 text-right">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-bold bg-gray-50 dark:bg-zinc-800">
                                            <td colspan="5" class="border p-2 text-right">ИТОГО:</td>
                                            <td class="border p-2 text-right text-blue-600">{{ formatPrice(receiptData.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Подписи -->
                        <div class="grid grid-cols-2 gap-8 mt-8 pt-4 border-t">
                            <div>
                                <p class="mb-2">От поставщика:</p>
                                <p class="mt-8 pt-2 border-t border-gray-300 text-center">_________________</p>
                                <p class="text-center text-sm text-gray-500">(подпись, печать)</p>
                            </div>
                            <div>
                                <p class="mb-2">От получателя:</p>
                                <p class="mt-8 pt-2 border-t border-gray-300 text-center">_________________</p>
                                <p class="text-center text-sm text-gray-500">(подпись, печать)</p>
                            </div>
                        </div>

                        <!-- Примечание -->
                        <div class="mt-6 text-xs text-gray-400 text-center">
                            Документ сформирован автоматически {{ formatDateTimeFull(new Date()) }}
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t flex justify-end gap-3">
                        <button @click="printReceipt" 
                                class="px-4 py-2 bg-[#3b82f6] text-white rounded-md hover:bg-[#3b82f6]/90 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Печать
                        </button>
                        <button @click="exportToExcel" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Экспорт в Excel
                        </button>
                        <button @click="$emit('close')" 
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    show: Boolean,
    receiptData: Object
});

const emit = defineEmits(['close']);
const receiptContent = ref(null);

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const formatDateFull = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatDateTimeFull = (date) => {
    const d = new Date(date);
    return d.toLocaleString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const printReceipt = () => {
    const printContent = receiptContent.value;
    const originalTitle = document.title;
    document.title = `Накладная_${props.receiptData?.number}`;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Накладная ${props.receiptData?.number}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .border { border: 1px solid #ddd; }
                    .bg-gray-100 { background-color: #f3f4f6; }
                </style>
            </head>
            <body>
                ${printContent.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
    document.title = originalTitle;
};

const exportToExcel = () => {
    if (!props.receiptData) return;
    
    // Подготовка данных для Excel
    const data = [
        ['НАКЛАДНАЯ №' + props.receiptData.number],
        ['От поставщика:', props.receiptData.supplier_name],
        ['ИНН поставщика:', props.receiptData.supplier_inn || '—'],
        ['Адрес поставщика:', props.receiptData.supplier_address || '—'],
        ['Дата получения:', formatDateFull(props.receiptData.received_at)],
        [],
        ['№', 'Наименование', 'Количество', 'Ед. изм.', 'Цена за ед.', 'Сумма']
    ];
    
    props.receiptData.items.forEach((item, index) => {
        data.push([
            index + 1,
            item.material_name,
            item.quantity,
            item.unit || 'шт',
            item.price,
            item.total
        ]);
    });
    
    data.push(['', '', '', '', 'ИТОГО:', props.receiptData.total_amount]);
    
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Накладная');
    XLSX.writeFile(wb, `Накладная_${props.receiptData.number}.xlsx`);
};
</script>