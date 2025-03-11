<script setup>
    import { onBeforeMount } from 'vue'
    import { useHorariosStore } from '@/stores/Catalogos/horarios'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'

    const store = useCursosModuloStore()
    const horarios = useHorariosStore()

    function selectHorario(item) {
        store.detalles.horario = item
    }

    onBeforeMount(() => {
        horarios.fetch()
    })
</script>

<template>
    <!-- <div class="grid gap-4">
        <Input v-model="horarios.horario.hora_inicial" option="label" title="hora inicio" type="time" :error="horarios.errors.hasOwnProperty('hora_inicio')" />
        <Input v-model="horarios.horario.hora_final" option="label" title="hora final" type="time" :error="horarios.errors.hasOwnProperty('hora_final')" />
        <hr class="my-4">
        <h1 class="text-gray-500 font-medium text-lg">Seleccione los días</h1>
        <div class="flex gap-4">
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="lun">
                <span>LUN</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="mar">
                <span>MAR</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="mie">
                <span>MIE</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="jue">
                <span>JUE</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="vie">
                <span>VIE</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="sab">
                <span>SAB</span>
            </label>
            <label class="flex gap-1 cursor-pointer">
                <input v-model="horarios.dias" type="checkbox" value="dom">
                <span>DOM</span>
            </label>
        </div>
    </div>
    <Validate-Errors :errors="horarios.errors" v-if="horarios.errors != 0" />
    <div class="flex justify-center">
        <Button @click="horarios.store" text="Crear horario" icon="fas fa-clock" class="btn-primary" :loading="horarios.loading.store"/>
    </div>
    <hr class="my-4"> -->
    <Data-Table 
        :headers="horarios.headers" 
        :data="horarios.horarios"
        :loading="horarios.loading.fetch"
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectHorario" 
        :itemsSelected="store.detalles.horario" 
    />
</template>
