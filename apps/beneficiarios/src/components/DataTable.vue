<script setup>
import axios from 'axios'
import { onClickOutside } from '@vueuse/core'
import { ref, computed, onMounted, watchEffect } from 'vue'

import { useGlobalStore } from '../stores/global'

import Tabla from './Tabla.vue'

const global = useGlobalStore()

// -------------PROPERTIES--------------


const search = ref('')
const startIndex = ref(1)
const endIndex = ref(1)
const currentPage = ref(1)
const rowsPerPage = ref(10)
const searchables = []
const sortColumn = ref(null)
const sortDir = ref('asc')
const sortType = ref(false)
const loadingExportData = ref(false)
const selectItems = ref([])
const selectAll = ref(null)
const openFilterOptions = ref(false)
const filters = ref([{ field: '', value: '', operator : '=' }])
const target = ref(null)

const emit = defineEmits(['selectdAllItems'])

const props = defineProps({
    headers: null,
    data: null,
    color: {
        default: 'bg-color-1 text-color-4'
    },
    loading: {
        type: Boolean,
        default: false
    },
    excel: {
        type: Boolean,
        default: true
    },
    filterAdvance: {
        type: Boolean,
        default: true
    },
    multiSelect: false,

    itemsSelected: {
        type: Array,
        default: () => []
    },
    rowsPerPage: {
        type: Number,
        default: 10
    }
})


// -------------COMPUTATED--------------


props.headers.map(el => {
    searchables.push(el.key.toLowerCase().trim())
})


const data = computed(() => props.data)


const isDate = (value) => {
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    return dateRegex.test(value) && !isNaN(parseDate(value));
}

// Función para convertir una fecha en formato DD/MM/YYYY a un objeto Date
const parseDate = (value) => {
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, month - 1, day)
}

const filteredData = computed(() => {
    currentPage.value = 1;
    const searchTerms = search.value.toLowerCase().trim().split(';').map(term => term.trim());

    return sortedItems.value.filter((item) => {
        return filters.value.every((filter) => {
            if (filter.field && filter.value) {
                const values = filter.value.split(';').map(value => value.trim().toLowerCase());
                const itemValue = String(getObjectValue(item, filter.field)).toLowerCase();

                return values.some(value => {
                    if (!isNaN(itemValue) && !isNaN(value)) {
                        // Comparación numérica
                        const itemNumber = parseFloat(itemValue);
                        const filterNumber = parseFloat(value);
                        switch (filter.operator) {
                            case '>':
                                return itemNumber > filterNumber;
                            case '<':
                                return itemNumber < filterNumber;
                            case '=':
                                return itemNumber === filterNumber;
                            default:
                                return false;
                        }
                    } else if (isDate(itemValue) && isDate(value)) {
                        // Comparación de fechas
                        const itemDate = parseDate(itemValue);
                        const filterDate = parseDate(value);
                        switch (filter.operator) {
                            case '>':
                                return itemDate > filterDate;
                            case '<':
                                return itemDate < filterDate;
                            case '=':
                                return itemDate.getTime() === filterDate.getTime();
                            default:
                                return false;
                        }
                    } else {
                        // Comparación de texto
                        switch (filter.operator) {
                            case '>':
                                return itemValue > value;
                            case '<':
                                return itemValue < value;
                            case '=':
                                return itemValue.toLowerCase().includes(value);
                            default:
                                return false;
                        }
                    }
                });
            }
            return true;
        }) && searchTerms.every((searchTerm) => {
            return searchables.some((column) => {
                const value = getObjectValue(item, column);
                return String(value).toLowerCase().includes(searchTerm);
            });
        });
    });
}, { cache: true })

const getObjectValue  = (object, key) => {
    const keys = key.split('.')
    return keys.reduce((value, currentKey) => {
        return value && value[currentKey]
    }, object)
}

const paginatedData = computed(() => {
    startIndex.value = (currentPage.value - 1) * rowsPerPage.value
    endIndex.value = parseInt(startIndex.value) + parseInt(rowsPerPage.value)
    return filteredData.value.slice(startIndex.value, endIndex.value)
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / rowsPerPage.value))

const setCurrentPage = (page) => {
    currentPage.value = page
}

const sortedItems = computed(() => {
    if (sortColumn.value) {
        return data.value.sort((a, b) => {

            if (sortType.value == 'numeric') {
                const valA = Number(getObjectValue(a, sortColumn.value))
                const valB = Number(getObjectValue(b, sortColumn.value))
                return sortDir.value === 'asc' ? valA - valB : valB - valA
            }

            const valA = String(getObjectValue(a, sortColumn.value))
            const valB = String(getObjectValue(b, sortColumn.value))
            return sortDir.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA)

        });
    }
    return data.value
})

const displayedPages = computed(() => {

    const totalDisplayedPages = 6
    const halfDisplayedPages = Math.floor(totalDisplayedPages / 2)
    let startPage = Math.max(currentPage.value - halfDisplayedPages, 1)
    let endPage = Math.min(startPage + totalDisplayedPages - 1, totalPages.value)

    if (endPage - startPage + 1 < totalDisplayedPages) {
        startPage = Math.max(endPage - totalDisplayedPages + 1, 1)
    }

    return Array(endPage - startPage + 1).fill().map((_, index) => startPage + index)
})


// -------------METHODS--------------


const sort = (column, type) => {

    sortType.value = type

    if (sortColumn.value === column) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDir.value = 'asc';
    }

}

const resetPage = () => {
    currentPage.value = 1
}

const typeValue = (value, type) => {

    let result

    switch (type) {
        case 'numeric':
            result = new Intl.NumberFormat("es-GT").format(value)
            break;
        case 'currency':
            result = new Intl.NumberFormat("es-GT", {
                'style': "currency",
                'currency': "GTQ",
                'minimumFractionDigits': 2,
            }).format(value)
            break;
        case 'date':
            
                const date = new Date(value)
                const d = String(date.getDate()).padStart(2,'0')
                const m = String(date.getMonth() + 1).padStart(2,'0')
                const y = String(date.getFullYear())
    
                result = value ? `${y}-${m}-${d}` : ''

            break;
        case 'datetime':
            
            const fecha = new Date(value)
            const dia = fecha.getfecha().padStart(2,'0')
            const mes = fecha.getMonth.padStart(2,'0')
            const anio = fecha.getFullYear()
            const h = fecha.getHours().padStart(2,'0')
            const mi = fecha.getMinutes()
            const s = fecha.getSeconds()

            result = `${anio}-${mes}-${dia} ${h}:${mi}:${s}`

            break;
        default:
            result = value
            break;
    }
    return result
}

const selectdAll = () => {
    emit('selectdAllItems', selectItems.value)
}

const selectAllItems = () => {
    if (selectAll.value.checked) {

        filteredData.value.forEach(item => {
            selectItems.value.push(item)
        })
    } else {
        selectItems.value = []
    }
    emit('selectdAllItems', selectItems.value)
}

const exportData = async () => {

    loadingExportData.value = true

    try {

        const response = await axios.post('exportar-excel',
            {
                columns: props.headers,
                data: filteredData.value
            },
            {
                responseType: 'blob'
            })

        const url = window.URL.createObjectURL(new Blob([response.data]));

        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'export.xlsx')

        document.body.appendChild(link)
        link.click();

        window.URL.revokeObjectURL(url)
        document.body.removeChild(link)


    } catch (error) {
        global.manejarError(error);

    } finally {

        loadingExportData.value = false
    }
}

const addFilter = () => {
    filters.value.push({ field: '', value: '', operator : '=' })
}

const removeFilter = (index) => {
    if (index != 0) {
        delete (filters.value.splice(index, 1))
    }
}

onClickOutside(target, (event) => openFilterOptions.value = false)

onMounted(() => {
    
    selectItems.value = props.itemsSelected
    if(props.rowsPerPage) {
        rowsPerPage.value = props.rowsPerPage
    }

});

</script>

<template>
    <section class="px-4 lg:px-7">
        <!-- FILTER -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="text-color-4 bg-transparent flex items-center px-2 py-1.5">
                <span>Mostrar</span>
                <select v-model="rowsPerPage" @change="resetPage"class="text-center bg-transparent w-full focus:outline-none ring-0">
                    <option>5</option>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>registros</span>
            </div>
            <div class="flex items-center gap-1">

                <Input icon="fas fa-search" class="h-10" type="search" placeholder="Buscar ..." v-model="search" />

                <div class="flex gap-1">
                    <!-- FILTER ADVANCE -->
                    <div class="relative" ref="target">
                        <Tool-Tip message="Filtro avanzado" class="-mt-7 text-color-4" v-if="props.filterAdvance">
                            <Icon @click="openFilterOptions = !openFilterOptions" icon="fas fa-filter" class="icon-button p-2" :class="filters.length > 0 && filters[0].value != '' ? 'bg-green-500' : 'bg-gray-400'" />
                        </Tool-Tip>
                        <div v-show="openFilterOptions" class="bg-white border rounded absolute mt-2 right-0 p-2 z-10">
                            <div class="flex justify-center py-1">
                                <button @click="addFilter"
                                    class="text-xs flex items-center gap-1 cursor-pointer active:scale-95 ">
                                    <Icon icon="fas fa-plus" class="text-green-500 " />
                                    Agregar otro filtro
                                </button>
                            </div>
                            <div v-for="(item, index) in filters" class="flex items-center gap-2 text-xs">
                                <select class="form-control uppercase" v-model="item.field">
                                    <option v-for="head in props.headers" :value="head.key">
                                            {{ head.title }}
                                    </option>
                                </select>

                                <select class="form-control" v-model="item.operator">
                                    <option value="=" selected> = </option>
                                    <option value=">">{{ `>` }}</option>
                                    <option value="<">{{ `<` }}</option>
                                </select>

                                <input type="search" class="form-control" v-model="item.value">

                                <Icon v-if="index != 0" @click="removeFilter(index)" icon="fas fa-xmark" class="text-red-500 hover:scale-110 cursor-pointer" />
                            </div>
                        </div>
                    </div>
                    <!-- END FILTER ADVANCE -->
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="props.excel && data.length > 0" @click="exportData" :icon="loadingExportData ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="loadingExportData ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="loadingExportData" />
                    </Tool-Tip>
                    <Tool-Tip message="Pdf" class="-mt-7 text-color-4">
                        <Icon v-if="props.pdf && data.length > 0" :icon="loadingExportData ? 'fas fa-spinner' : 'fas fa-file-pdf'" class="icon-button p-2 btn-danger" :class="loadingExportData ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="loadingExportData" />
                    </Tool-Tip>
                </div>
            </div>
        </div>
        <!-- END FILTER -->

        <!-- MOBILE CARDS -->
        <div class="grid gap-4 lg:hidden py-4">
            <Card v-for="item in paginatedData" :key="item.id" class="bg-violet-50 p-2">
                <table class="w-full">
                    <tr v-for="head in props.headers" class="hover:bg-violet-200">
                        <td class="px-4 font-semibold uppercase text-sm select-none" :width="head.width" align="left" :hidden="head.hidden">
                            <p class="text-color-4">{{ head.title }}</p>
                        </td>
                        <td :align="head.align ?? 'center'" :width="head.width" :hidden="head.hidden">
                            <slot :name="head.key" :item="item">
                                <p :class="head.class ?? 'text-sm'">
                                    {{ typeValue(getObjectValue(item, head.key), head.type) }}
                                </p>
                            </slot>
                        </td>
                    </tr>
                </table>
            </Card>
        </div>

        <!-- END MOBILE CARDS -->

        <!-- TABLE -->
        <Tabla class="hidden lg:block">
            <template #thead>
                <tr>
                    <th v-if="props.multiSelect">
                        <!-- <input type="checkbox" ref="selectAll" @change="selectAllItems"> -->
                    </th>
                    <th v-for="(head, index) in props.headers" :key="index" @click="sort(head.key, head.type)" scope="col" class="px-4 py-3.5 text-color-4 text-xs cursor-pointer select-none" :width="head.width" :align="head.align ?? 'left'" :hidden="head.hidden">
                        <div class="flex gap-1">
                            <span v-if="sortColumn === head.key">
                                {{ sortDir === 'asc' ? '▲' : '▼' }}
                            </span>
                            {{ head.title }}
                        </div>
                    </th>
                </tr>
            </template>
            <template #tbody>
                <slot name="tbody" :items="paginatedData">
                    <tr v-for="item in paginatedData" :key="item.id" class="hover:bg-violet-50 text-gray-800 select-none">
                        <td v-if="props.multiSelect" align="center"> 
                            <input type="checkbox" @change="selectdAll" v-model="selectItems" :value="item"> 
                        </td>
                        <td v-for="(head, index) in props.headers" class="px-4" :align="head.align ?? 'left'" :width="head.width" :key="index" :hidden="head.hidden">
                            <slot :name="head.key" :item="item">
                                <p :class="head.class ?? 'text-xs'">
                                    {{ typeValue(getObjectValue(item, head.key), head.type) }}
                                </p>
                            </slot>
                        </td>
                    </tr>
                </slot>
                <tr v-if="props.loading">
                    <td align="center" :colspan="props.headers.length" class="px-10">
                        <Loading-Bar class="h-1 bg-color-4" />
                        <span class="animate-ping">Cargando data ....</span>
                    </td>
                </tr>
                <tr v-if="data.length === 0 && props.loading === false">
                    <td align="center" :colspan="props.headers.length">
                        No hay data ....
                    </td>
                </tr>
            </template>
        </Tabla>
        <!-- END TABLE -->

        <!-- PAGINATION -->
        <div class="flex items-center justify-between pb-4">
            <!-- RESPONSIVE MOBILE BUTTONS -->
            <div v-show="filteredData.length >= rowsPerPage && displayedPages.length > 1" class="flex flex-1 justify-between md:hidden">
                <a @click="(currentPage == 1) ? currentPage = 1 : currentPage--"
                    class="relative flex items-center rounded border border-gray-300 select-none cursor-pointer btn px-4 py-2 text-sm font-medium"
                    :class="props.color">
                    Anterior
                </a>
                <a @click="(currentPage == totalPages) ? currentPage = totalPages : currentPage++"
                    class="relative ml-3 flex items-center rounded border border-gray-300 select-none cursor-pointer btn px-4 py-2 text-sm font-medium"
                    :class="props.color">
                    Siguiente
                </a>
            </div>

            <div class="hidden md:flex md:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-color-4">
                        Mostrando
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        a
                        <span class="font-medium">{{ (endIndex >= filteredData.length) ? filteredData.length : endIndex
                            }}</span>
                        de
                        <span class="font-medium">{{ filteredData.length }}</span>
                        resultados
                    </p>
                </div>
                <div v-if="itemsSelected.length > 0">
                    <p class="text-xs text-color-4">
                        Seleccionados
                        <span class="font-medium">{{ itemsSelected.length }}</span>
                        de
                        <span class="font-medium">{{ filteredData.length }}</span>
                    </p>
                </div>
                <div v-show="filteredData.length >= rowsPerPage && displayedPages.length > 1">
                    <nav class="flex gap-x-2">
                        <a v-if="currentPage > 4" @click="setCurrentPage(1)"
                            class="cursor-pointer relative flex items-center px-3 py-1.5 font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <Icon icon="fas fa-angles-left" class="text-xs" />
                        </a>
                        <a v-if="currentPage > 1" @click="(currentPage == 1) ? currentPage = 1 : currentPage--"
                            class="cursor-pointer relative flex items-center px-3 py-1.5 text-sm font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <span class="sr-only">Previous</span>
                            <Icon icon="fas fa-angle-left" class="text-xs" />

                        </a>
                        <a :class="page === currentPage ? ' z-10 ' + props.color : ''"
                            v-for="page in displayedPages" :key="page" @click="setCurrentPage(page)"
                            class="cursor-pointer select-none relative flex items-center px-3 py-1.5 text-color-4 text-sm font-semibold rounded-full hover:bg-gray-200 hover:text-color-4">
                            {{ page }}
                        </a>
                        <a v-if="currentPage < (totalPages - 1)"
                            @click="(currentPage == totalPages) ? currentPage = totalPages : currentPage++"
                            class="cursor-pointer relative flex items-center px-3 py-1.5 text-sm font-semibold text-color-4 rounded-full hover:bg-gray-200"
                            >
                            <span class="sr-only">Next</span>
                            <Icon icon="fas fa-angle-right" class="text-xs" />
                        </a>
                        <a v-if="currentPage < (totalPages - 2)" @click="setCurrentPage(totalPages)"
                            class="cursor-pointer relative flex items-center px-3 py-1.5 font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <Icon icon="fas fa-angles-right" class="text-xs" />
                        </a>
                    </nav>
                </div>
            </div>
        </div>
        <!-- END PAGINATION -->

    </section>
</template>

<style scoped>
    td {
        @apply py-1 text-gray-800;
    }

    th {
        @apply font-semibold uppercase;
    }
</style>
