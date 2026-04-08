<script setup>
    import { computed, onBeforeMount } from 'vue'
    import { useBeneficiariosProgramaStore } from '@/stores/Inscripciones/beneficiarios-programa'
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useAuthStore } from '@/stores/auth'

    const store = useBeneficiariosProgramaStore()
    const programas = useProgramasStore()
    const auth = useAuthStore()

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
        const yearsList = []
        for (let i = 0; i <= 1; i++) {
            yearsList.unshift(currentYear - i)
        }
        yearsList.push(currentYear + 1)
        return yearsList
    })

    onBeforeMount(() => {
        const year = new Date()
        store.year = year.getFullYear()
        programas.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div class="grid xl:flex gap-4 items-center">
            <Input v-model="store.year" option="select" title="*seleccione año" :error="store.errors.hasOwnProperty('year')">
                <option v-for="year in years" :value="year">{{ year }}</option>
            </Input>
            <Input @change="store.fetch(store.programa_id)" v-model="store.programa_id" option="select" title="*seleccione programa">
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.id + ' - '+programa.nombre }}</option>
                </template>
                <option value="0">TODOS</option>
            </Input>
            <Button @click="store.fetch(store.programa_id)" text="Consultar" icon="fas fa-search" class="btn-primary flex-none" :loading="store.loading.fetch" />
        </div>
        <Card class="bg-gray-200 p-4 text-2xl text-center w-72 border-black border-2 border-dashed mx-auto my-4">
            BENEFICIARIOS UNICOS : {{ store.beneficiarios.total_beneficiario_unico }}
        </Card>

        <Data-Table v-if="auth.checkPermission('ver beneficiarios programa')" 
            :headers="store.headers"
            :data="store.beneficiarios?.beneficiarios_inscritos" :loading="store.loading.fetch"
            :excel="auth.checkPermission('exportar excel beneficiarios programa')"
            :pdf="true" >
            <template #estado="{ item }">
                <Badge  :color="item.estado == 'A' ? 'green' : 'red'" :text="item.estado == 'A' ? 'Activo' : 'Inactivo'" />
            </template>
            <template #becado="{ item }">
                <span v-if="item.becado == 1" class="text-xs">BC</span>
                <span v-else-if="item.becado == 2" class="text-xs">BP</span>
                <span v-else-if="item.becado == 3" class="text-xs">BR</span>
                <span v-else class="text-xs">NB</span>
            </template>
        </Data-Table>
    </Card>
</template>
