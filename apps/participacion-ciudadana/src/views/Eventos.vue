<script setup>
    import { useEventosStore } from '@/stores/eventos'
    import Calendar from '@/components/Calendar.vue'

    const store = useEventosStore()

</script>

<template>
    <h1 class="text-2xl text-gray-500 font-semibold">Eventos</h1>
    <br>
    <Loading-Bar v-if="store.loading.fetch" class="bg-color-4 h-1" />
    <Calendar :events="store.eventos"/>
</template>