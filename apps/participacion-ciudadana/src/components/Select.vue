<script setup>
    import { computed, ref } from 'vue';
    import UserPhoto from './UserPhoto.vue';
    const props = defineProps(['items'])
    const open = ref(false)
    const search = ref('')

    const openOptions = () => {
        open.value = search.value.length > 0 ? true : false
    }

    const selected = ref([])

    const resultSearch = computed(() => props.items)

    const searching = computed(() => {
        return resultSearch.value.filter(item => item.fullname.toLowerCase().match(search.value.toLowerCase())).slice(0,5)
    })

    const addItem = (item) => {
        selected.value.push(item)
        document.getElementById('search').select()
    }

    function deleteItem (index) {
        selected.value.splice(index,1)
        document.getElementById('search').select()
    }



</script>

<template>
    <div class="relative border-2 rounded">
        <div class="overflow-hidden">
            <ul class="flex flex-wrap gap-2">
                <li v-for="(select,index) in selected" class=" whitespace-break-spaces">
                    <div class="flex items-center relative">
                        <small class="bg-blue-400 text-xs rounded-full whitespace-nowrap tracking-tight px-2 py-2 text-blue-100">{{ select.smallname ?? '' }}</small>
                        <button @click="deleteItem(index)" class="rounded-full bg-red-500 text-xs w-4 h-4 flex items-center justify-center text-red-100 absolute -right-2 top-0 ">x</button>
                    </div>
                </li>
            </ul>
            <input id="search" @keyup="openOptions" type="text" class="pl-3 focus:outline-none h-9 w-full" placeholder="Seleccione algo" v-model="search">
        </div>
        <Transition>
            <ul v-if="open" class="w-full bg-white border-2 rounded mt-1 py-4 overflow-auto absolute">
                <li v-for="user in searching" class="flex items-center justify-between cursor-pointer hover:bg-gray-100 py-1 px-4" @click="addItem(user)">
                    <UserPhoto :user="user" class="h-10 w-10" />
                    <span>{{ user.fullname }}</span>
                    <Icon icon="fas fa-check" class="text-green-500 text-2xl" />
                </li>
            </ul>
        </Transition>
    </div>

</template>