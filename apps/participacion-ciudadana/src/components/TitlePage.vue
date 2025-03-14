<script setup>
    import { useRoute } from 'vue-router';
    import { useGlobalStore } from '../stores/global'
    const store = useGlobalStore()

    const router = useRoute()

</script>

<template>

    <div class="text-blue-muni font-bold text-3xl text-center flex gap-3">
        <Icon :icon="store.titlePage.icon" />
        <h1 class="whitespace-nowrap" >{{ router.name }}</h1>
    </div>
</template>