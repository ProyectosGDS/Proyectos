<script setup>
    import { useRoute } from 'vue-router'    
    
    const routes = useRoute()
    const props = defineProps(['index']);

</script>

<template>

    <nav class="mb-5 text-blue-muni uppercase">
        <div class="flex justify-center items-center space-x-10">
            <template v-for="route in routes.matched[props.index].children">
                <RouterLink :to="{name : route.name}" active-class="border-b-2 border-lime-muni font-bold" class="focus:outline-none" >
                    {{ route.name }} 
                </RouterLink>
            </template>
        </div>
    </nav>
</template>
