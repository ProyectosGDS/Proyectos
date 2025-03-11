<script setup>
    import SideBar from '@/components/SideBar.vue'
    import UserCard from '@/components/UserCard.vue'
</script>

<template>
    <main class="h-screen flex">
        <SideBar />
        <UserCard class="absolute top-2 right-2"/>
        <section class="flex-1 h-svh overflow-auto">
            <article class="pt-20 p-3">
                <RouterView />
            </article>
        </section>
    </main>
</template>
