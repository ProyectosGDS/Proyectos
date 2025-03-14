<script setup>
    import { onMounted, ref } from 'vue';
    import BackTop from '../components/BackTop.vue'
    import NavBar from '../components/NavBar.vue'
    import Footer from '../components/Footer.vue'
    import Calendar from '@/components/Calendar.vue'
    import Carousel from '../components/Carousel.vue'

    import { useEventosStore } from '@/stores/eventos'

    const eventoStore = useEventosStore()


    

    const date = ref(new Date());

    onMounted(()=>{
        eventoStore.fetch()
    })
    
</script>

<template>
    <main class="pt-8">
        <NavBar :index="0" />
        <div class="h-screen flex flex-col">
            <section class="flex-1 container mx-auto py-8">
                <div class="lg:flex gap-5">
                    <Card class="bg-white p-4 w-full">
                        <RouterView />
                    </Card>
                    <Card class="bg-white p-4">
                        <h1 class="text-xl font-semibold text-color-9">Proximas actividades</h1>
                        <br>
                        <div class="grid gap-3 w-full">
                            
                            <Carousel />
                            
                            <div>
                                <Calendar :events="eventoStore.eventos" :min="true" />
                            </div>
                            <div>
                                <!-- <ul>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>Lorem ipsum dolor sit amet </span>
                                    </li>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>voluptatum amet, quaerat a doloribus</span>
                                    </li>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>Numquam necessitatibus aliquid ! </span>
                                    </li>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>fugiat id aliquid accusamus odio!</span>
                                    </li>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>laboriosam odio! Accusantium</span>
                                    </li>
                                    <li class="font-medium cursor-pointer flex gap-2">
                                        <input type="checkbox">
                                        <span>voluptatum amet, quaerat a doloribus</span>
                                    </li>
                                </ul> -->
                            </div>
                        </div>
                    </Card>
                </div>
                <BackTop />
            </section>
            <div class="flex-none bg-white text-color-9 flex items-center justify-center h-16 md:h-36">
                <span class="text-lg md:text-4xl text-nowrap ">
                    Juntos 
                    <span class="font-semibold">
                        construimos
                    </span> 
                    la ciudad para 
                    <span class="font-semibold italic">
                        vivir mejor.
                    </span>
                </span>
            </div>
            <Footer class="flex-none" />
        </div>
    </main>
</template>
