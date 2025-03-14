<script setup>
    const props = defineProps({
        color : {
            type : String,
            default : 'bg-gray-200',
        }
    })
</script>

<template>
    <div class="w-full my-5 overflow-auto">
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