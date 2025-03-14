<script setup>
    import { ref,computed, onMounted, watch} from 'vue'
    import axios from 'axios';
    import Tabla from './Tabla.vue'
    import LoadingBar from './LoadingBar.vue'

    const props = defineProps({
        headers:null, 
        src:'',
        color:{
            default:'bg-[#DBEAFE] text-blue-muni'
        },
        reload : false
    })

    const reload = computed(() => props.reload)

    const page = ref(1)
    const search = ref('')
    const data = ref([])
    const paginate = ref({
        current_page : 0,
        from : 0,
        last_page : 0,
        per_page : 10,
        to : 0,
        total : 0,
    })
    const order = ref('desc')
    const column = ref('')
    const loading = ref(false)

    const displayedPages =  computed(() => {

        const totalDisplayedPages = 6
        const halfDisplayedPages = Math.floor(totalDisplayedPages / 2)
        let startPage = Math.max(1 - halfDisplayedPages, paginate.value.current_page)
        let endPage = Math.min(startPage + totalDisplayedPages - 1, paginate.value.last_page)

        if (endPage - startPage + 1 < totalDisplayedPages) {
            startPage = Math.max(endPage - totalDisplayedPages + 1, 1)
        }


        return Array(endPage - startPage + 1).fill().map((_, index) => startPage + index)

    })

    const getObjectValue = (object, key) => {
        const keys = key.split('.')
        return keys.reduce((value, currentKey) => {
            return value && value[currentKey]
        }, object)
    }

   
    const fetch = (page) => {
        loading.value = true
        axios.post(page == undefined ? props.src : `${props.src}?page=${page}` ,{
            'per_page' : paginate.value.per_page,
            'search' : search.value,
            'order' : order.value,
            'column' : column.value
        })
        .then(response => {
            data.value = response.data.data
            paginate.value = response.data
        })
        .catch(err => console.error(err.response.data))
        .finally(() => loading.value = false)
    }

    const changePage = (pag) => {
        page.value = pag
        fetch(page.value)
    }

    const nextPage = () => {
        if (page.value < paginate.value.last_page) {
            page.value = page.value + 1
            fetch(page.value)
        }
    }

    const previousPage = () => {
        if (page.value > 1) {
            page.value = page.value - 1
            fetch(page.value)
        }
    }

    const sort = (colum_name) => {
        column.value = colum_name
        order.value = (order.value == 'asc') ? 'desc' : 'asc'
        fetch(page.value)
    }

    const formatAsNumber = (value,symbol = false) => {
        
        let format
        
        if(symbol){
            format = new Intl.NumberFormat("es-GT", {
                'style': "currency",
                'currency': "GTQ",
                'minimumFractionDigits': 2,
            }).format(value)
        }else{
            format = new Intl.NumberFormat("es-GT", { 
                'minimumFractionDigits': 2,
            }).format(value)
        }
        return format
    }


    watch(reload,() => fetch())
    onMounted(() => {
        if (!props.reload) {
            fetch();
        }
    })


</script>

<template>
    <div class="p-5">
        <div class="mt-6 md:flex md:items-center md:justify-between">
            <div  class="text-gray-400 bg-white flex items-center border-2 px-2 py-1.5 rounded shadow-lg">
                <span>Mostrar</span>
                <select v-model="paginate.per_page" @change="fetch" class="text-center bg-white font-bold w-full focus:outline-none ring-0">
                    <option>5</option>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>registros</span>
            </div>
            <div class="relative flex items-center mt-4 md:mt-0">
                <text-field  v-model="search" @keyup="fetch" type="search" placeholder="Buscar" icon="fas fa-search" />
            </div>
        </div>

        <Tabla>
            <template #thead>
                <tr :class="color">
                    <th v-for="(head, index) in props.headers"
                        @click="(head.sort) ? sort(head.key) : ''" 
                        :key="index" 
                        class="px-4 py-3.5 text-sm font-normal rtl:text-right  cursor-pointer" 
                        :width="head.width" 
                        :align="head.align ?? 'left' ">

                        {{ head.title }}
                    </th>
                </tr>
            </template>
            <template #tbody>
                <tr v-if="loading">
                    <td align="center" :colspan="props.headers.length">
                        <LoadingBar :color="color" class="px-10" />
                        <span class="animate-ping">Cargando data ....</span> 
                    </td>
                </tr>
                <tr v-for="item in data" :key="item.id" class="hover:bg-gray-100 text-gray-800">
                    <td v-for="(head,index) in props.headers" class="px-4" :align="head.align" :width="head.width" :key="index">
                        <slot :name="head.key" :item="item">
                            {{ (head.NumberFormat) ? formatAsNumber(getObjectValue(item,head.key),head.symbol) : getObjectValue(item,head.key) }}
                        </slot>
                    </td>
                </tr>
                <tr v-if="data.length === 0 && loading === false">
                    <td align="center" :colspan="props.headers.length">
                        No hay data ....
                    </td>
                </tr>
            </template>
            <template #tfoot>
            </template>
        </Tabla>

        <div class="flex items-center justify-between">
            <div class="flex flex-1 justify-between md:hidden">
                <a @click="previousPage" 
                    class=" select-none relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Anterior
                </a>
                <a @click="nextPage" 
                    class=" select-none relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Siguiente
                </a>
            </div>

            <div class="hidden md:flex md:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-gray-500">
                        Mostrando
                        <span class="font-medium">{{ paginate.from }}</span>
                        a
                        <span class="font-medium">{{ paginate.to  }}</span>
                        de
                        <span class="font-medium">{{ paginate.total }}</span>
                        resultados
                    </p>
                </div>
                <div v-show="displayedPages.length > 1">
                    <nav class="inline-flex -space-x-px rounded-md bg-white shadow-lg">
                        <a v-if="page >= 2" @click="changePage(1)"
                            class="cursor-pointer relative inline-flex items-center rounded-l-md px-4 py-2 font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-200 focus:z-20 focus:outline-offset-0">
                            {{ 1 }}
                        </a>
                        <a v-if="page != 1" @click="previousPage" 
                        class="cursor-pointer relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-200 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Previous</span>
                            <Icon icon="fas fa-caret-left" class="text-lg" />
                        </a>
                        <a :class=" page === paginate.current_page ? ' scale-125 z-10 ' + props.color : '' " v-for="page in displayedPages"
                            :key="page" @click="changePage(page)" 
                            class="cursor-pointer select-none relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 hover:bg-gray-200 hover:text-gray-500 focus:z-20 focus:outline-offset-0">
                            {{ page }}
                        </a>
                        <a v-if="page != paginate.last_page" @click="nextPage" 
                        class="cursor-pointer relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-200 focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">Next</span>
                            <Icon icon="fas fa-caret-right" class="text-lg" />
                        </a>
                        <a v-if="page != paginate.last_page" @click="changePage(paginate.last_page)"  
                            class="cursor-pointer relative inline-flex items-center rounded-r-md px-4 py-2 font-semibold text-gray-500 ring-1 ring-inset ring-gray-300 hover:bg-gray-200 focus:z-20 focus:outline-offset-0">
                            {{  paginate.last_page }}
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>

    td{
        @apply py-3 text-gray-500 text-sm;
    }

    th{
        @apply font-bold uppercase;
    }

</style>