<script setup>
    import { onBeforeMount } from 'vue'
    import { useProgramasStore } from '@/stores/Catalogos/programas'

    const programas = useProgramasStore()

    onBeforeMount(() => {
        programas.fetch()
    })
</script>

<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="flex gap-4 items-center">
            <Input option="select" title="*seleccione programa">
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Input option="select" title="*seleccione tipo">
                <option value=""></option>
                <option value="CURSO">CURSO</option>
                <option value="MODULO">MÓDULO</option>
            </Input>
            <Button text="Consultar" class="btn-primary" icon="fas fa-share" />
        </div>
    </Card>

    <!-- MODALES -->

</template>

