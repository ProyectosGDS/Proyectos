<script setup>

import { ref, computed, onMounted } from 'vue'

// -------------PROPERTIES--------------

const search = ref('')
const startIndex = ref(1)
const endIndex = ref(1)
const currentPage = ref(1)
const rowsPerPage = ref(12)
const sortColumn = ref('nombre')

const sortDir = ref('desc')
const sortType = ref(false)

const props = defineProps({
    data: {
        type : Array,
        default : () => []
    },

    loading: {
        type: Boolean,
        default: false
    }
})


// -------------COMPUTATED--------------



const data = computed(() => props.data)


const filteredData = computed(() => {
    currentPage.value = 1;

    return sortedItems.value.filter(item => item.nombre.toLowerCase().match(search.value.toLowerCase()))

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

onMounted(() => {

    setTimeout(() => {
        if (data.value.length === 0 && props.loading === true) {
            props.loading = false
        }
    }, 2000)
});

</script>

<template>
    <section class="lg:px-7">
        <!-- FILTERS -->
        <div class=" space-y-2 lg:space-y-0 lg:flex justify-between gap-5">
            <Input v-model="search" icon="fas fa-search" placeholder="Buscar curso .." />
        </div>
        <!-- END FILTERS -->
        <hr class="my-5">
        <!-- CARDS -->
        <Loading-Bar class="bg-blue-muni h-1" v-if="props.loading" />
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <template v-for="carrera in paginatedData">
                <Card v-if="carrera.deleted_at == null" class="overflow-hidden border border-blue-muni">
                    <template #header>
                        <img :src="carrera.imagen ? carrera?.urlImage : ''" 
                             :alt="carrera.nombre" 
                             class=" object-cover h-40 w-auto object-center">
                    </template>
                    <div class="p-4">
                        <span class=" text-wrap text-lg font-medium text-blue-muni">
                            {{ carrera.nombre }}
                        </span>
                        <p>
                            {{ carrera.descripcion.slice(0,110) }}
                            ...
                        </p>
                    </div>
                    <template #footer>
                        <div class="p-4">
                            <slot name="action" :item="carrera"></slot>
                        </div>
                    </template>
                </Card>
            </template>
        </div>
        <!-- END CARDS -->
        <br>
        <!-- PAGINATION -->
        <div class="flex items-center justify-between pb-4">
            <!-- RESPONSIVE MOBILE BUTTONS -->
            <div class="flex flex-1 justify-between md:hidden">
                <a @click="(currentPage == 1) ? currentPage = 1 : currentPage--"
                    class="relative inline-flex items-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Anterior
                </a>
                <a @click="(currentPage == totalPages) ? currentPage = totalPages : currentPage++"
                    class="relative ml-3 inline-flex items-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                    Siguiente
                </a>
            </div>

            <div class="hidden md:flex md:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-gray-500">
                        Mostrando
                        <span class="font-medium">{{ startIndex + 1 }}</span>
                        a
                        <span class="font-medium">{{ (endIndex >= filteredData.length) ? filteredData.length : endIndex}}</span>
                        de
                        <span class="font-medium">{{ filteredData.length }}</span>
                        resultados
                    </p>
                </div>
                <div v-show="filteredData.length >= 11 && displayedPages.length > 1">
                    <nav class="flex gap-3">
                        <a v-if="currentPage > 4" @click="setCurrentPage(1)"
                            class="pagination">
                            <Icon icon="fas fa-angles-left" class="text-xs" />
                        </a>
                        <a v-if="currentPage > 4" @click="(currentPage == 1) ? currentPage = 1 : currentPage--"
                            class="pagination">
                            <span class="sr-only">Previous</span>
                            <Icon icon="fas fa-angle-left" class="text-xs" />

                        </a>
                        <a :class="page === currentPage ? ' scale-125 z-10 ' + props.color : ''"
                            v-for="page in displayedPages" :key="page" @click="setCurrentPage(page)"
                            class="pagination">
                            {{ page }}
                        </a>
                        <a v-if="currentPage < (totalPages - 1)"
                            @click="(currentPage == totalPages) ? currentPage = totalPages : currentPage++"
                            class="pagination">
                            <span class="sr-only">Next</span>
                            <Icon icon="fas fa-angle-right" class="text-xs" />
                        </a>
                        <a v-if="currentPage < (totalPages - 3)" @click="setCurrentPage(totalPages)"
                            class="pagination">
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
    @apply py-3 text-gray-800;
}

th {
    @apply font-semibold uppercase text-violet-300;
}

.pagination {
    @apply rounded-full bg-blue-muni text-white h-8 w-8 flex items-center justify-center cursor-pointer select-none;
}
</style>
