<script setup>
import { RouterLink } from 'vue-router';
import { onClickOutside } from '@vueuse/core'
import { ref } from 'vue'

    const props = defineProps(['items','title'])
    const open = ref(false)
    const target = ref(null)


    onClickOutside(target, (event) => open.value = false)

</script>

<template>
    <div class="relative" ref="target">
        
        <button @click="open = !open" class="hover:border-b-2  ">
            {{ props.title }}
        </button>

        <div v-show="open" class="absolute bg-white py-4 px-2 rounded-md border shadow-xl mt-2">
            <ul>
                <RouterLink v-for="item in props.items" :to="item.route" @click="open = false">
                    <li class="hover:bg-gray-100">
                        <div class="flex items-center gap-4 mt-3 px-4 py-2">
                            <Icon :icon="item.icon" />
                            <span class=" whitespace-nowrap">{{ item.title }}</span>
                        </div>
                    </li>
                </RouterLink>
            </ul>
        </div>
    </div>
</template>

