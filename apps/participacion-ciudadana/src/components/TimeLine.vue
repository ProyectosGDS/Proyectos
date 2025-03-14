<script setup>
     const props = defineProps(['items'])
</script>

<template>
    <span v-for="(item,index) in items" :key="item.id" class="flex items-center">
        <div class="flex has-nuve cursor-pointer justify-center">
            <div class = 'nuve font-bold text-center bg-white mx-auto rounded-md border-2 p-2 shadow-lg -mt-20'>
                <div class="grid gap-2">
                    <span class="font-normal text-xs"> <b>Realizado por: </b>{{ item.asignado_a.smallname }}</span>
                    <span class="font-normal text-xs"> <b>Fecha realizado: </b>{{ item.created_at }}</span>
                    <span class="font-normal text-xs truncate"> <b>Observación: </b>{{ item.observacion }}</span>
                </div>
            </div>
            <small class="text-xs px-3 py-1 rounded-full text-center" 
                   :class="{
                        'text-green-600 bg-green-300' : ((index + 1) === items.length && item.status_id === '17'),
                        'text-blue-600 bg-blue-300' : ((index + 1) === items.length && item.status_id != '17'),
                        'text-gray-400 bg-gray-200' : ((index + 1) < items.length)
                   }">
                {{ item.status.nombre}}
            </small>
        </div>
        <Icon v-if="(index + 1) != items.length" icon="fa-solid fa-arrow-right-long" class="px-1" />
    </span>
</template>

<style>
    .nuve {
        @apply invisible absolute;
    }

    .has-nuve:hover .nuve {
        @apply visible z-30;
    }
</style>