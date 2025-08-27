<script setup>
    import axios from 'axios'
    import { ref, onMounted, computed } from 'vue'
    import Tabla from './Tabla.vue'
    import { onClickOutside } from '@vueuse/core'

    const props = defineProps({
        headers : {
            type : Array,
            default : () => [],
            required : true
        },
        url : {
            type : String,
            default : '',
            required : true
        },
        multiselect : {
            type : Boolean,
            default : false
        }
    })

    const emits = defineEmits(['selected'])

    const data = ref([])
    const loading = ref({
        fetch : false,
        export : false
    })
    const pagination = ref({
        page : 1,
        per_page : 10,
        total : null,
        last_page : null,
        from : null,
        to : null,
        current_page : null
    })
    const selectedItems = ref([])
    const search = ref('')
    const filters = ref([
        { field : '', operator : '', value : '' }
    ])

    const operator = ['=', '!=', '>', '<', '>=', '<=','in','not in', 'null', 'not null', 'between', 'not between', 'like', 'not like']
    const openFilters = ref(false)
    const hoveredColumn = ref(null);
    const target = ref(null)

    const fields = computed(() => {
        return props.headers.map(header => {
            if(header.exclude) return
            return header.key.toUpperCase()
        })
    })

    const sortData = ref({
        field_first : fields.value[0],
        field : fields.value[0],
        direction : 'asc'
    })

    const processedFilters = computed(() => {
        return filters.value.map(filter => {
            const { operator, value } = filter
            const arrayOperators = ['between', 'not between', 'in', 'not in']

            if (arrayOperators.includes(operator) && typeof value === 'string') {
                return {
                    ...filter,
                    value: convertToArray(value)
                }
            }
            return filter
        })
    })

    const convertToArray = (value) => {
        return value.split(',').map(item => {
            item = item.trim()

            if (!isNaN(item)) {
                const num = Number(item)
                return num.toString() === item ? num : item
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(item)) {
                return item
            }

            return item
        })
    }

    const fetch = async () => {
        loading.value.fetch = true
        
        try {
            const response = await axios.get(props.url,{
                params : {
                    
                    page : pagination.value.page == 0 ? pagination.value.page = 1 : pagination.value.page,
                    per_page : pagination.value.per_page,
                    sort : sortData.value,
                    searching : {
                        search : search.value,
                        fields : fields.value,
                        filters : processedFilters.value,
                    }
                }
            })
            
            const { data: rows, ...paginate } = response.data
            
            data.value = rows

            Object.assign(pagination.value,paginate)

        } catch (error) {
            console.error(error)
        } finally {
            loading.value.fetch = false
        }
    }

    const nextPage = () => {
        if(parseInt(pagination.value.page) <= parseInt(pagination.value.last_page)) {
            pagination.value.page ++
            fetch()
        }
    }

    const lastPage = () => {
        pagination.value.page = pagination.value.last_page
        fetch()
    }

    const firstPage = () => {
        pagination.value.page = 1
        fetch()
    }

    const previusPage = () => {
        if(parseInt(pagination.value.page) >= 2){
            pagination.value.page --
            fetch()
        }
    }

    const sort = (column_name) => {
        sortData.value.field = column_name
        sortData.value.direction = sortData.value.direction == 'asc' ? 'desc' : 'asc'
        fetch()
    }

    const getObjectValue  = (object, key) => {
        const keys = key.split('.')
        return keys.reduce((value, currentKey) => {
            return value && value[currentKey]
        }, object)
    }
    
    const typeValue = (value, type) => {

        let result

        switch (type) {
            case 'numeric':
                result = new Intl.NumberFormat("es-GT").format(value)
                break

            case 'currency':
                result = new Intl.NumberFormat("es-GT", {
                    'style': "currency",
                    'currency': "GTQ",
                    'minimumFractionDigits': 2,
                }).format(value)
                break

            case 'date':
                
                    const date = new Date(value)
                    const d = String(date.getDate()).padStart(2,'0')
                    const m = String(date.getMonth() + 1).padStart(2,'0')
                    const y = String(date.getFullYear())

                    result = value ? `${y}-${m}-${d}` : ''

                break

            case 'datetime':
                
                const fecha = new Date(value)
                const dia = fecha.getDate().toString().padStart(2,'0')
                const mes = fecha.getMonth().toString().padStart(2,'0')
                const anio = fecha.getFullYear().toString()
                const h = fecha.getHours().toString().padStart(2,'0')
                const mi = fecha.getMinutes().toString().padStart(2,'0')
                const s = fecha.getSeconds().toString().padStart(2,'0')

                result = `${anio}-${mes}-${dia} ${h}:${mi}:${s}`

                break

            case 'time':
                
                const f = new Date(value)
                const hours = f.getHours().toString().padStart(2,'0')
                const minutes = f.getMinutes().toString().padStart(2,'0')
                const seconds = f.getSeconds().toString().padStart(2,'0')

                result = `${hours}:${minutes}:${seconds}`

                break

            case 'phone':
                if(value == null) return ''
                
                const phone = value.toString()
                result = `${phone.substring(0,4)} - ${phone.substring(4,8)}`

                break

            default:
                result = value
                break
        }

        return result
    }

    const selected = () => {
        emits('selected',selectedItems.value)
    }

    const changePerPage = () => {
        pagination.value.page = 1
        fetch()
    }

    const exportData = async (type) => {

        loading.value.export = true

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
            link.setAttribute('download', `reporte.${type}`)

            document.body.appendChild(link)
            link.click();

            window.URL.revokeObjectURL(url)
            document.body.removeChild(link)


        } catch (error) {
            global.manejarError(error);

        } finally {

            loading.value.export = false
        }
    }

    const deleteFilter = (index) => {
        
        if(index == 0) {
            filters.value[0] = { field : '', operator : '', value : '' }
            return
        }

        filters.value.splice(index, 1)
    }

    onClickOutside(target, () => openFilters.value = false)

    onMounted(() => fetch())

</script>

<template>
    <div class="grid gap-5 w-full p-8 border border-gray-300 bg-white rounded-lg">
        <div class="flex justify-between items-center">
            <div class="flex gap-3">
                <span>Mostrar</span>
                <select @change="changePerPage()" v-model="pagination.per_page" class="text-center focus:outline-none">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
                <span>registros</span>
            </div>
            <div class="flex items-center gap-2">
                <Input @keypress.enter="fetch()" v-model="search" icon="fas fa-search" type="search" placeholder=" Buscar..."/>
                <div class="relative" ref="target">
                    <Icon @click="openFilters = !openFilters" icon="fas fa-filter" class="icon-btn text-gray-400 text-lg" title="Filtros avanzados" :class="{'text-green-500' : filters.length > 0 && filters[0].value != ''}" />
                    <Transition name="fade">
                        <div v-show="openFilters" class="bg-white border text-gray-600 border-gray-400 shadow-xl rounded-lg space-y-2 absolute top-9 -right-16 z-10 p-2 grid justify-items-center">
                            <Button @click="filters.push({ field : '', operator : '', value : '' })" text="+" icon="fas fa-filter" class="btn-secondary" title="Agregar filtro" />
                            <div class="grid gap-2">
                                <div class="flex justify-around font-semibold uppercase">
                                    <label>Columna</label>
                                    <label>Operador</label>
                                    <label>Valores</label>
                                </div>
                                <div v-for="(filter,index) in filters" :key="index" class="flex gap-2 items-center">
                                    <select v-model="filter.field" class="form-control uppercase">
                                        <template v-for="field in props.headers">
                                            <option v-if="!field.exclude" :value="field.key">{{ field.title }}</option>
                                        </template>
                                    </select>
                                    <select v-model="filter.operator" class="form-control uppercase">
                                        <option v-for="op in operator" :key="op" :value="op">{{ op }}</option>
                                    </select>
                                    <input v-model="filter.value" type="text" class="form-control">
                                    <Icon @click="deleteFilter(index)" icon="fas fa-times" class="icon-btn text-red-500" title="Eliminar filtro" />
                                </div>
                            </div>
                            <Button @click="fetch()" icon="fas fa-check" text="Aplicar filtro" class="btn-secondary" :loading="loading.fetch" />
                        </div>
                    </Transition>
                </div>
                <Icon @click="fetch()" icon="fas fa-arrows-rotate" class="icon-btn text-gray-400 hover:text-blue-500 text-lg" title="Recargar" :class="loading.fetch ? 'animate-spin' : ''" />
            </div>
        </div>

        <Loading-Bar v-if="loading.fetch" class="h-1 bg-blue-300" />
        
        <Tabla>
            <template #thead>
                <tr class="bg-gray-50">
                    <th v-if="props.multiselect"></th>
                    <th v-for="(header,colIndex) in props.headers" @click="(header.hidden || header.exclude) ? () => {} : sort(header.key.toUpperCase())"
                        @mouseenter="hoveredColumn = colIndex "
                        @mouseleave="hoveredColumn = null"
                        :align="header.align ?? 'left'"
                        :class="[
                            header.class ?? 'uppercase',
                            hoveredColumn === colIndex  ? 'bg-gray-100' : '',
                        ]"
                        :width="header.width ?? 'auto'"
                        :key="header.key">

                        <div v-if="!header.hidden" class="flex gap-1 items-center">
                            <span v-if="sortData.field === header.key.toUpperCase()">
                                {{ sortData.direction === 'asc' ? '▲' : '▼' }}
                            </span>
                            {{ header.title }}
                            <Icon v-if="!header.exclude" icon="fas fa-sort" class="text-[10px]" />
                        </div>
                    </th>
                </tr>
            </template>
            <template #tbody>
                <slot name="tbody" :items="data">
                    <tr v-for="(item,index) in data" class="hover:bg-gray-100" :class="{'bg-gray-100' : selectedItems.includes(item)}" >
                        <td v-if="props.multiselect">
                            <input @change="selected()" type="checkbox" class="hover:scale-125 cursor-pointer" v-model="selectedItems" :value="item">
                        </td>
                        <td v-for="(header,colIndex) in props.headers" 
                            :key="index" 
                            :title="item[header.key]" 
                            :class="[
                                header.class ?? 'uppercase',
                                hoveredColumn === colIndex  ? 'bg-gray-100' : ''
                            ]"
                            :width="header.width ?? 'auto'" 
                            :align="header.align ?? 'left'" >

                            <slot :name="header.key" :item="item">
                                {{ typeValue(getObjectValue(item, header.key), header.type )}}
                                <span v-if="header.text">
                                    {{ header.text }}
                                </span>
                            </slot>
                            
                        </td>
                    </tr>
                </slot>
            </template>
        </Tabla>
        
        <div class="flex justify-between">
            <div class="flex gap-3">
                <span>Mostrando</span>
                <span>{{ pagination.from }}</span>
                <span>al</span>
                <span>{{ pagination.to }}</span>
                <span>de</span>
                <span>{{ pagination.total }}</span>
                <span>registros</span>
            </div>
            <div class="flex select-none items-center gap-2">
                <Icon @click="firstPage" icon="fas fa-backward-fast" class="icon-btn text-lg" title="Primera" />
                <Icon @click="previusPage" icon="fas fa-caret-left" class="icon-btn text-lg" title="Anterior" />
                <span>Página</span>
                <input type="number" min="1" :max="pagination.lastPage" v-model="pagination.page" @keypress.enter="fetch" class="w-14 text-center focus:outline-none">
                <span> / </span>
                <input type="text" v-model="pagination.last_page" class="w-14 text-center focus:outline-none" readonly>
                <Icon @click="nextPage" icon="fas fa-caret-right" class="icon-btn text-lg" title="Siguiente"/>
                <Icon @click="lastPage" icon="fas fa-forward-fast" class="icon-btn text-lg" title="Última" />
            </div>
            <div></div>
        </div>
        <Loading-Bar v-if="loading.fetch && pagination.per_page >= 25" class="h-1 bg-blue-300" />
    </div>
</template>

<style scoped>
    @reference 'tailwindcss';
    
    th {
        @apply font-medium cursor-pointer px-2 py-4;
    }

    td {
        @apply p-2;
    }

    .icon-btn {
        @apply cursor-pointer hover:scale-125;
    }

    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s ease;
    }

    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }

    .form-control {
        @apply
            text-center 
            focus:outline-none 
            border rounded-lg 
            border-gray-400
            p-1
        }

</style>