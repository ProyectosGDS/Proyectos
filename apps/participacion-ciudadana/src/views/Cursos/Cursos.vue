<script setup>
    import { onMounted } from 'vue'
    import { useCursosStore } from '@/stores/cursos'
    import LoadingBar from '@/components/LoadingBar.vue';

    const store = useCursosStore()

    onMounted(() => {
        store.fetch()
    })
</script>

<template>
    <div class="p-4">
        <Data-Table :headers="store.headers" :data="store.cursos" color="text-color-9">
            <template #tbody="{items}">
                <tr v-for="item in items" @click="item.tipo == 'MODULO' ? store.detalleModulo(item) : store.detalleCurso(item)" title="Click para mas detalles">
                    <td>{{ item.id }}</td>
                    <td>{{ item.modulo_curso }}</td>
                    <td>{{ item.tipo }}</td>
                    <td>{{ item.sede }}</td>
                    <td>{{ item.temporalidad }}</td>
                    <td>{{ item.modalidad }}</td>
                </tr>
            </template>
        </Data-Table>    
        <LoadingBar v-if="store.loading" class="h-1 bg-color-4" />
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