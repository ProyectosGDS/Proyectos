<script setup>
    import { onMounted } from 'vue'
    import { useCursosStore } from '@/stores/cursos'
    import LoadingBar from '@/components/LoadingBar.vue';

    const store = useCursosStore()

    const toggle = (item) => {
        store.detalleCurso(item)
    }

    const zonas = [
        '1',
        '2',
        '3',
        '5',
        '6',
        '7',
        '8',
        '10',
        '11',
        '12',
        '13',
        '14',
        '16',
        '17',
        '18',
        '19',
        '21',
        '24',
        '25',
    ]

    // onMounted(() => {
    //     store.fetch()
    // })
</script>

<template>
    <!-- <div class="p-4">
        <Data-Table 
            :headers="store.headers" 
            :data="store.cursos" 
            color="text-color-9"
            :rowSelected="true"
            @selectRow="toggle" >
        </Data-Table>    
        <LoadingBar v-if="store.loading" class="h-1 bg-color-4" />
    </div> -->

    <div>
        <div class="grid xl:grid-cols-4 gap-4">
            <div v-for="zona in zonas" :key="zona"
                class="bg-white p-4 border-2 rounded-lg">
                <div class="flex gap-4">
                    <div class="border-2 rounded-full flex items-center justify-center size-24">
                        <img 
                            class="w-auto h-20 object-contain object-center"
                            src="@/assets/img/logo.png" 
                            alt="MuniGuate"
                        >
                    </div>
                    <div>
                        <h3 class="text-xl font-medium text-color-1 mt-2">Zona {{ zona }} </h3>
                        <p class="text-sm text-gray-600">Cursos técnico productivo</p>
                    </div>
                </div>
                <div class="flex justify-center">
                    <Button 
                        text="Ver cursos"
                        color="primary"
                        class="btn-primary"
                        @click="store.detalleCurso(zona)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    td {
        @apply py-3 text-gray-800 px-4 text-sm;
    }

    tr {
        @apply cursor-pointer hover:bg-violet-50 text-sm;
    }
</style>