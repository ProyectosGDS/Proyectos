<script setup>
    import { useRoute } from 'vue-router'

    const route = useRoute()

</script>

<template>
    <div class="hidden text-3xl md:flex gap-x-3 text-nowrap bg-color-1 rounded-lg text-white py-3 px-3 absolute left-full">
        <h1 class=" font-semibold">{{ route.name }}</h1>
    </div>
</template>