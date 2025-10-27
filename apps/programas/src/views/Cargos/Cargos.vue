<script setup>
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useCargosStore } from '@/stores/Cargos/cargos'
    import { onMounted } from 'vue'

    const store = useCargosStore()
    const auth = useAuthStore()
    const catalogos = useCatalogosStore()
    const dependencia_id = JSON.parse(localStorage.getItem('dependencia_id'))

    onMounted(() => {
        catalogos.getEscuelas(dependencia_id)
    })

</script>
<template>
    <Card class="bg-white p-4 xl:p-8">
        <div class="grid justify-items-center items-center w-max">
            <Input type="month" title="Mes a generar" />
            <br>
            <div class="grid grid-cols-3 gap-4">
                <!-- <Input @change="store.getProgramasEscuela"
                    v-model="store.escuela_id"
                    option="select" 
                    title="Seleccione escuela"
                    >
                    <option value=""></option>
                    <template v-for="escuela in catalogos.escuelas">
                        <option v-if="escuela.objeto_contrato" :value="escuela.id">{{ escuela.nombre }}</option>
                    </template>
                </Input> -->
                <template v-for="escuela in catalogos.escuelas">
                    <Button v-if="escuela.objeto_contrato" 
                        :text="escuela.nombre" 
                        class="btn-primary" 
                        @click="store.getProgramasEscuela(escuela.id)"
                        :loading="store.loading.programasEscuela"
                    />
                </template>
            </div>
            <br>
            <ul>
                <li v-for="programa in store.programasEscuela" class="flex justify-between gap-4 px-4 items-center hover:bg-gray-200">
                    <span>
                        {{ programa.nombre }}
                    </span>
                    <Button text="Generar partidas" class="btn-primary"/>
                </li>
            </ul>
        </div>
    </Card>
</template>