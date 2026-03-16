<script setup>

    import BackTop from '../components/BackTop.vue'
    import NavBar from '../components/NavBar.vue'
    import Footer from '../components/Footer.vue'
    
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
