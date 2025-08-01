<script setup>
    import { onBeforeMount } from 'vue'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
    import { useModulosStore } from '@/stores/Catalogos/modulos'
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import Select from '@/components/Select.vue'

    const store = useCursosModuloStore()
    const modulos = useModulosStore()
    const programas = useProgramasStore()
    const catalogos = useCatalogosStore()

    function selectModulo(item) {
        store.detalles.modulo = item
    }

    onBeforeMount(() => {
        modulos.fetch(store.programa_id)
        modulos.modulo.programa_id = store.programa_id
        modulos.programa_id = store.programa_id
        catalogos.getSedes()
        catalogos.getTemporalidades()
    })
</script>

<template>
    <details class=" py-4">
        <summary class="text-color-4 text-lg m-3 cursor-pointer hover:bg-gray-100 rounded-lg font-medium">CREAR NUEVO MÓDULO</summary>
        <div class="grid gap-4">
            <div class="grid xl:flex gap-4">
                <Input v-model="modulos.modulo.nombre" option="label" title="*Nombre módulo" maxlength="80" :error="modulos.errors.hasOwnProperty('nombre')" />
                <Input v-model="modulos.modulo.programa_id" option="select" title="*Seleccione programa" :error="modulos.errors.hasOwnProperty('programa_id')" readonly disabled>
                    <option value=""></option>
                    <template v-for="programa in programas.programas">
                        <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                    </template>
                </Input>
            </div>
            <Input v-model="modulos.modulo.descripcion" option="text-area" title="Descripción" placeholder="Describe el modulo ..." rows="3" maxlength="255" :error="modulos.errors.hasOwnProperty('descripcion')" />
            <div class="grid xl:flex gap-4">
                <Select v-model="modulos.modulo.sede_id" title="*seleccione sede" :items="catalogos.sedes" :fields="['id','nombre_completo']" :error="modulos.errors.hasOwnProperty('sede_id')" />
                <Input v-model="modulos.modulo.temporalidad_id" option="select" title="*seleccione temporalidad" :error="modulos.errors.hasOwnProperty('temporalidad_id')">
                    <option value=""></option>
                    <option v-for="temporalidad in catalogos.temporalidades" :value="temporalidad.id">{{ temporalidad.nombre }}</option>
                </Input>
            </div>
            <div class="grid xl:flex gap-4">
                <Input v-model="modulos.modulo.seccion" option="label" title="sección" maxlength="80" :error="modulos.errors.hasOwnProperty('seccion')" />
                <Input v-model="modulos.modulo.capacidad" option="label" title="*Capacidad" type="number" min="1" :error="modulos.errors.hasOwnProperty('capacidad')" />
            </div>
            <div class="grid xl:flex gap-4">
                <Input v-model="modulos.modulo.fecha_inicial" option="label" title="inicia" type="date" :error="modulos.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="modulos.modulo.fecha_final" option="label" title="termina" type="date" :error="modulos.errors.hasOwnProperty('fecha_final')" />
            </div>
            <div class="grid xl:flex justify-evenly gap-4 items-center">
                <div class="flex justify-evenly gap-3 text-color-4">
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="modulos.modulo.modalidad" value="PRESENCIAL" name="modalidad">
                        <span>PRESENCIAL</span>
                    </label>
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="modulos.modulo.modalidad" value="VIRTUAL" name="modalidad">
                        <span>VIRTUAL</span>
                    </label>
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="modulos.modulo.modalidad" value="HIBRIDA" name="modalidad">
                        <span>HIBRIDA</span>
                    </label>
                </div>
                <div>
                    <h1 class="uppercase text-color-4 text-center">*DE PAGA</h1>
                    <div class="flex items-center justify-center gap-1">
                        SÍ
                        <Switch class="w-auto h-6 bg-gray-400 has-[:checked]:bg-blue-500" :values="['S','N']" v-model="modulos.modulo.paga" :error="modulos.errors.hasOwnProperty('paga')" />
                        NO
                    </div>
                </div>
                <div class="flex justify-evenly gap-4">
                    <div class="flex justify-evenly">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500">PÚBLICO</span>
                            <Switch v-model="modulos.modulo.publico" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['S','N']" />
                            <span class="text-sm text-gray-500">PRIVADO</span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="modulos.modulo.paga == 'S'" class="flex gap-4">
                <Input option="label" title="Tarifa menor" type="number" v-model="modulos.modulo.tarifa_menor" :error="modulos.errors.hasOwnProperty('tarifa_menor')"  />
                <Input option="label" title="Tarifa mayor" type="number" v-model="modulos.modulo.tarifa_mayor" :error="modulos.errors.hasOwnProperty('tarifa_mayor')"  />
            </div>
            
            <Validate-Errors :errors="modulos.errors" v-if="modulos.errors != 0" />
            <div class="flex justify-center">
                <Button @click="modulos.store" text="Crear módulo" icon="fas fa-folder-tree" class="btn-primary" :loading="modulos.loading.store"/>
            </div>
        </div>
    </details>
    <Data-Table 
        :headers="modulos.headers" 
        :data="modulos.modulos"
        :loading="modulos.loading.fetch" 
        :excel="false" 
        :rowsPerPage="5" 
        :multiSelect="true" 
        @selectdAllItems="selectModulo" 
        :itemsSelected="store.detalles.modulo" 
    />
</template>

