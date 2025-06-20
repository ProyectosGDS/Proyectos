<script setup>
    const props = defineProps(['errors'])
</script>
<template>
    <div class="text-red-500 font-bold p-2 border-red-500 border rounded bg-red-300 my-4">
        <h1 class="text-xl">Ups! algo salio mal</h1>
        <ul class="list-disc ml-6 text-sm">
            <li v-for="error in props.errors">{{ error[0] }}</li>
        </ul>
    </div>
</template>