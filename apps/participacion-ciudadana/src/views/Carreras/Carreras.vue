<script setup>

    import { onMounted } from 'vue'
    import { useCarrerasStore } from '@/stores/carreras'
    import DataCard from '@/components/DataCardCarreras.vue'

    const store = useCarrerasStore()

    onMounted(() => store.fetch())

</script>

<template>
    <DataCard :data="store.carreras" :Loading="store.loading.fetch">
        <template #action="{item}">
            <Button @click="store.detalleCarrera(item)" icon="fas fa-arrow-right" text="Saber mas ..." class="bg-blue-muni text-white rounded-full" />
        </template>
    </DataCard>
</template>