<script setup>
    const props = defineProps({
        color : {
            type : String,
            default : 'bg-gray-200',
        }
    })
</script>

<template>
    <div class="my-5">
        <table class="w-full table-auto">
            <thead>
                <slot name="thead"></slot>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <slot name="tbody"></slot>
            </tbody>
            <tfoot>
                <slot name="tfoot"></slot>
            </tfoot>
        </table>
    </div>
</template>

<style scoped>

    td{
        @apply py-3 px-3;
    }

    th{
        @apply font-bold uppercase py-2 px-3;
    }
</style>