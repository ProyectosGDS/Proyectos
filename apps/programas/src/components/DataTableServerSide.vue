<script setup>
    import { ref,computed, onMounted, watchEffect} from 'vue'
    import axios from 'axios'
    import Tabla from './Tabla.vue'
    import LoadingBar from './LoadingBar.vue'
    import { useGlobalStore } from '@/stores/global'

    const global = useGlobalStore()

    const props = defineProps({
        headers:{
            type : Array,
            default : () => []
        }, 
        src: {
            type : String,
            default : ''
        },
        color:{
            type : String,
            default:'bg-color-1 text-color-4'
        },
        reload : {
            type : Boolean,
            default : false
        }
    })

    const emits = defineEmits(['reloadData'])

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

    const fetch = async (page) => {
        loading.value = true
        try {
            const response = await axios.get(props.src,{
                params : {
                    page : page,
                    per_page : paginate.value.per_page,
                    search : search.value,
                    order : order.value,
                    column : column.value
                }
            })

            const { data: rows, ...pagination } = response.data
            data.value = rows
            paginate.value = pagination

            if(props.reload == true ) {
                emits('reloadData',false)
            }

        } catch (error) {
            global.manejarError(error)
        } finally {
            loading.value = false
        }
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
                const dia = fecha.getfecha().padStart(2,'0')
                const mes = fecha.getMonth.padStart(2,'0')
                const anio = fecha.getFullYear()
                const h = fecha.getHours().padStart(2,'0')
                const mi = fecha.getMinutes()
                const s = fecha.getSeconds()

                result = `${anio}-${mes}-${dia} ${h}:${mi}:${s}`

                break
            default:
                result = value
                break
        }
        return result
    }

    const getObjectValue = (object, key) => {
        const keys = key.split('.')
        return keys.reduce((value, currentKey) => {
            return value && value[currentKey]
        }, object)
    }

    watchEffect(() => {

        if(props.reload) {
            fetch()
        }
    })
    
    onMounted(() => {
        if (!props.reload) {
            fetch()
        }

        setTimeout(() => {
            if (data.value.length === 0 && props.loading === true) {
                props.loading = false
            }
        }, 2000)
    })


</script>

<template>
    <section class="px-4 lg:px-7">
        <div class="md:flex md:items-center md:justify-between">
            <div  class="text-color-4 flex items-center">
                <span>Mostrar</span>
                <select v-model="paginate.per_page" @change="fetch" class="text-center w-full focus:outline-none ring-0">
                    <option>5</option>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span>registros</span>
            </div>
            <div class="flex items-center gap-1">
                <Input icon="fas fa-search" class="h-10" type="search" placeholder="Buscar ..." v-model="search" @keyup="fetch" />
            </div>
        </div>

        <!-- MOBILE CARDS -->
        <div class="grid gap-4 lg:hidden py-4">
            <Card v-for="item in data" :key="item.id" class="bg-violet-50 p-2">
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

        <Tabla class="hidden lg:block">
            <template #thead>
                <tr>
                    <th v-for="(head, index) in props.headers"
                        @click="(head.sort == false) ? null : sort(head.key)" 
                        :key="index" 
                        class="px-4 py-3.5 text-color-4 text-sm font-normal cursor-pointer select-none" 
                        :width="head.width" 
                        :align="head.align ?? 'left'"
                        :hidden="head.hidden">
                        {{ head.title }}
                    </th>
                </tr>
            </template>
            <template #tbody>
                <tr v-if="loading">
                    <td align="center" :colspan="props.headers.length" class="px-10">
                        <Loading-Bar class="h-1 bg-color-4" />
                        <span class="animate-ping">Cargando data ....</span>
                    </td>
                </tr>
                <tr v-for="item in data" :key="item.id" class="hover:bg-violet-50 text-gray-800 select-none">
                    <td v-for="(head,index) in props.headers" class="px-4" :class="head.class" :align="head.align" :width="head.width" :key="index">
                        <slot :name="head.key" :item="item">
                            {{ typeValue(getObjectValue(item,head.key),head.type) }}
                            <span v-if="head.text">
                                {{ head.text }}
                            </span>
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
                    <p class="text-xs text-color-4">
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
                    <nav class="flex gap-x-2">
                        <a v-if="page > 4" @click="changePage(1)"
                            class="cursor-pointer relative flex items-center px-3 py-1.5 font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <Icon icon="fas fa-angles-left" class="text-xs" />
                        </a>
                        <a v-if="page != 1" @click="previousPage" 
                        class="cursor-pointer relative flex items-center px-3 py-1.5 text-sm font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <span class="sr-only">Previous</span>
                            <Icon icon="fas fa-angle-left" class="text-xs" />
                        </a>
                        <a :class=" page === paginate.current_page ? ' z-10 ' + props.color : '' " 
                            v-for="page in displayedPages" :key="page" @click="changePage(page)" 
                            class="cursor-pointer select-none relative flex items-center px-3 py-1.5 text-color-4 text-sm font-semibold rounded-full hover:bg-gray-200 hover:text-color-4">
                            {{ page }}
                        </a>
                        <a v-if="page != paginate.last_page" @click="nextPage" 
                        class="cursor-pointer relative flex items-center px-3 py-1.5 text-sm font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <span class="sr-only">Next</span>
                            <Icon icon="fas fa-angle-right" class="text-xs" />
                        </a>
                        <a v-if="page != (paginate.last_page)" @click="changePage(paginate.last_page)"  
                            class="cursor-pointer relative flex items-center px-3 py-1.5 font-semibold text-color-4 rounded-full hover:bg-gray-200">
                            <Icon icon="fas fa-angles-right" class="text-xs" />
                        </a>
                    </nav>
                </div>
            </div>
        </div>

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