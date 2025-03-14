<script setup>
    import { ref } from 'vue'

    const props = defineProps(['items'])
    
    const isHover = ref(false) 

</script>

<template>
    <div @mouseleave="isHover = false" class="border-2 rounded shadow-xl">
        <div class=" p-5 cursor-pointer relative overflow-hidden" @mouseenter="isHover = true" >
            <Icon icon="fa-solid fa-people-group" class="text-6xl text-blue-800" />
            <h1 class="text-xl text-blue-900 font-bold uppercase">curso</h1>
        </div>
        <Transition name="fade">
            <div class="border-b-2 border-x-2 rounded-b-lg p-5 bg-white absolute z-30" v-if="isHover">
                <ul>
                    <li v-for="item in props.items" class="hover:bg-blue-500 cursor-pointer uppercase">
                        {{ item }}
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
    .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease;
    }

    .fade-enter-from, .fade-leave-to {
    opacity: 0;
    }
</style>