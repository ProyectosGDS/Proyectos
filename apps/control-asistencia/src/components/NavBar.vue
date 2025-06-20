<script setup>
    import { useRoute } from 'vue-router'    
    const routes = useRoute()
    const props = defineProps(['index']);
</script>

<template>
    <nav class="text-blue-muni uppercase h-16">
        <div class="flex container mx-auto px-2 md:px-0">
            <img src="/img/logo.png" alt="Municipalidad de Guatemala" class="h-20">
            <div class="flex-1 flex justify-center items-center gap-x-4 md:gap-x-10">
                <template v-for="route in routes.matched[props.index].children">
                    <RouterLink :to="{name : route.name}" active-class="border-b-2 border-lime-muni font-medium" class="focus:outline-none select-none" >
                        {{ route.name }} 
                    </RouterLink>
                </template>
            </div>
        </div>
    </nav>
</template>