<script setup>
    import { onMounted } from 'vue'
    import { useModulosStore } from '@/stores/modulos'
    import LoadingBar from '@/components/LoadingBar.vue';

    const store = useModulosStore()

    onMounted(() => {
        store.fetch()
    })
</script>

<template>
    <div class="p-4">
        <Data-Table :headers="store.headers" :data="store.modulos" color="text-color-9">
            <template #tbody="{items}">
                <tr 
                    v-for="item in items" 
                    @click="store.detallesPrograma(item)" 
                    title="Click para mas detalles">
                    
                    <td>{{ item.id }}</td>
                    <td>{{ item.programa }}</td>
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