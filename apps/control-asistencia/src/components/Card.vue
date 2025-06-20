<script setup>
 
</script>

<template>
    <div class="rounded-xl flex flex-col">
        <slot name="header"></slot>
        <div class="flex-1">
            <slot></slot>    
        </div>
        <slot name="footer"></slot>
    </div>
</template>

