<script setup>
    import { computed, onBeforeMount } from 'vue'

    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useActividadesProgramaStore } from '@/stores/Asignaciones/actividades-programa'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useAuthStore } from '@/stores/auth'
    import { useAsignacionesActividadesProgramaStore } from '@/stores/Asignaciones/asignaciones-actividades-programa'

    import Actividad from './ActividadesPrograma/Actividad.vue'
    import Select from '@/components/Select.vue'
    

    const store = useActividadesProgramaStore()
    const asignaciones = useAsignacionesActividadesProgramaStore()
    const programas = useProgramasStore()
    const catalogos = useCatalogosStore()
    const auth = useAuthStore()


    const searchables = []

    asignaciones.headers.map(el => {
        searchables.push(el.key.toLowerCase().trim())
    })

    const searching_actividades = computed(() => {
        
        return asignaciones.actividades.filter((item) => {
            return searchables.some((column) => {
                const value = getObjectValue(item, column)
                return String(value).toLowerCase().includes(store.search.toLowerCase())
            })
        })
    } , { cache: true } )

    const getObjectValue  = (object, key) => {
        const keys = key.split('.')
        return keys.reduce((value, currentKey) => {
            return value && value[currentKey]
        }, object)
    }

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
        const yearsList = []
        for (let i = 0; i <= 3; i++) {
            yearsList.unshift(currentYear - i)
        }
        return yearsList
    })

    
    onBeforeMount(() => {
        const year = new Date()
        asignaciones.year = year.getFullYear()
        programas.fetch()
        catalogos.getCatalogosActividad()
    })

</script>

<template>
    <Card v-if="auth.checkPermission('ver actividades programa')" class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2">
            <div class="space-y-4 xl:pr-8">
                <div class="flex items-center gap-2">
                    <Input v-model="asignaciones.year" option="select" title="*seleccione año" :error="store.errors.hasOwnProperty('year')">
                        <option v-for="year in years" :value="year">{{ year }}</option>
                    </Input>
                    <Input @change="asignaciones.fetch(asignaciones.programa_id)" v-model="asignaciones.programa_id" option="select" title="*seleccione programas" :error="store.errors.hasOwnProperty('programa_id')">
                        <option value=""></option>
                        <template v-for="programa in programas.programas">
                            <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                        </template>
                    </Input>
                    <Icon v-if="asignaciones.programa_id" @click="asignaciones.fetch(asignaciones.programa_id)" icon="fas fa-arrows-rotate" class="icon-button btn-secondary" :class="{'animate-spin' : asignaciones.loading.fetch }" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('actividad')" v-model="store.actividad.actividad.nombre" option="label" title="*seleccione actividad" class="cursor-pointer" :error="store.errors.hasOwnProperty('actividad_id')" readonly />
                    <Icon @click="store.removeItem('actividad')" v-if="store.actividad.actividad.nombre" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <Input v-model="store.actividad.tipo_actividad" option="select" title="*seleccione tipo actividad" :error="store.errors.hasOwnProperty('tipo_actividad_id')">
                    <option value=""></option>
                    <option v-for="tipo in catalogos.catalogos_actividad.tipos_actividades" :value="JSON.stringify(tipo)">{{ tipo.nombre }}</option>
                </Input>
                <Input v-model="store.actividad.responsable" option="label" title="nombre completo responsable" maxlength="80" :error="store.errors.hasOwnProperty('responsable')" />
                <div class="grid xl:flex gap-4">
                    <Input v-model="store.actividad.zona" option="select" title="seleccione zona" :error="store.errors.hasOwnProperty('zona_id')">
                        <option value=""></option>
                        <option v-for="zona in catalogos.catalogos_actividad.zonas" :value="JSON.stringify(zona)">{{ zona.descripcion }}</option>
                    </Input>
                    <Input v-model="store.actividad.distrito" option="select" title="seleccione distrito" :error="store.errors.hasOwnProperty('distrito_id')">
                        <option value=""></option>
                        <option v-for="distrito in catalogos.catalogos_actividad.distritos" :value="JSON.stringify(distrito)">{{ distrito.nombre }}</option>
                    </Input>
                </div>
                <Input v-model="store.actividad.direccion" option="label" title="dirección" maxlength="45" :error="store.errors.hasOwnProperty('direccion')" />
                <div class="grid xl:flex gap-4">
                    <Input v-model="store.actividad.hora_inicio" option="label" title="hora inicio" type="time" :error="store.errors.hasOwnProperty('hora_inicio')" />
                    <Input v-model="store.actividad.hora_final" option="label" title="hora final" type="time" :error="store.errors.hasOwnProperty('hora_final')" />
                </div>
                <div class="grid xl:flex gap-4">
                    <Input v-model="store.actividad.fecha_inicial" option="label" title="*inicia" type="date" :error="store.errors.hasOwnProperty('fecha_inicial')" />
                    <Input v-model="store.actividad.fecha_final" option="label" title="*termina" type="date" :error="store.errors.hasOwnProperty('fecha_final')" />
                </div>

                <Validate-Errors :errors="store.errors" v-if="Object.keys(store.errors).length && !store.errors.hasOwnProperty('seleccion')" />
                <div class="flex justify-center gap-4">
                    <Tool-Tip message="Agrear actividad al listado" class="-mt-6 text-color-4">
                        <Button @click="store.addActividad()" icon="fas fa-plus" class="btn-primary" :disabled="store.editDetails" />
                    </Tool-Tip>
                </div>
            </div>
            <div class="xl:pl-8">
                <h1 class="text-center text-2xl font-medium text-gray-500">
                    Cantidad de actividades : {{ asignaciones.actividades.length }}
                </h1>
                <div class="flex items-center gap-4">
                    <Input v-model="store.search" icon="fas fa-search" type="search" placeholder="Buscar actividad .. " class="h-11" />
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar excel actividades programa')" @click="asignaciones.exportExcel" :icon="asignaciones.loading.excel ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="asignaciones.loading.excel ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="asignaciones.loading.excel" />
                    </Tool-Tip>
                </div>
                <br>
                <div class="grid" v-if="asignaciones.loading.fetch" >
                    <Loading-Bar class="bg-color-4 h-1"/>
                    <h1 class="text-center text-gray-400 text-xs animate-pulse">Cargando data ...</h1>
                </div>
                <div class="h-[40rem] overflow-y-auto">
                    <div class="grid gap-4 pr-4">
                        <template v-for="(asignacion,index) in searching_actividades">
                            <div class="flex gap-2">
                                <Card class="p-4 w-full" :class="{'bg-green-200 text-green-700' : asignacion.id && asignacion.estado_actividad_id == 1, 'bg-orange-200 text-orange-700' : asignacion.id && asignacion.estado_actividad_id == 2,'bg-red-200 text-red-800' : asignacion.id && asignacion.estado_actividad_id == 3, 'bg-gray-200 text-black' : !asignacion.hasOwnProperty('id') }">
                                    <div class="grid grid-cols-2 gap-1 text-xs uppercase">
                                        <span>
                                            <span class="font-medium">ID ASIGNACIÓN: </span>
                                            <span>{{ asignacion.id ?? '' }}</span>
                                        </span>
                                        <span></span>
                                        <span>
                                            <span class="font-medium">TIPO: </span>
                                            <span>{{ asignacion.tipo }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">ESTADO: </span>
                                            <span>{{ asignacion.estado }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">ACTIVIDAD: </span>
                                            <span>{{ asignacion.actividad }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">RESPONSABLE: </span>
                                            <span>{{ asignacion.responsable ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">HORARIO: </span>
                                            <span>{{ asignacion.horario ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">FECHA: </span>
                                            <span>{{ asignacion.fechas ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">ZONA: </span>
                                            <span>{{ asignacion.zona ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">DISTRITO: </span>
                                            <span>{{ asignacion.distrito ?? '' }}</span>
                                        </span>
                                        <span class="col-span-2">
                                            <span class="font-medium">DIRECCIÓN: </span>
                                            <span>{{ asignacion.direccion ?? '' }}</span>
                                        </span>
                                    </div>
                                </Card>
                                <div class="grid items-center">
                                    <Icon v-if="!asignacion.id" @click="store.removeCurso(index)" icon="fas fa-trash" class="icon-button btn-danger" />
                                    <Icon v-if="auth.checkPermission('eliminar actividades programa') && asignacion.id" @click="asignaciones.remove(asignacion)" icon="fas fa-trash" class="icon-button btn-danger" />
                                    <template v-if="asignacion.hasOwnProperty('id')">
                                        <Icon v-if="auth.checkPermission('editar actividades programa')" @click="asignaciones.show(asignacion)" icon="fas fa-pencil" class="icon-button btn-secondary" title="Editar" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <br>
                <div v-if="asignaciones.actividades.length" class="flex justify-center gap-4">
                    <Button v-if="auth.checkPermission('crear actividades programa')" @click="asignaciones.store" text="Asignar actividades nuevas al programa" icon="fas fa-plus" class="btn-primary" :loading="asignaciones.loading.store"/>
                </div>
            </div>
        </div>
    </Card>

    <Modal :open="store.modal.actividad" title="Actividad" icon="fas fa-bicycle">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Actividad />
        </div>
        <Validate-Errors :errors="store.errors" v-if="Object.keys(store.errors).length && store.errors.hasOwnProperty('seleccion')" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('actividad')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de eliminar la actividad asignada con el id:?</p>
                <h1 class="text-center font-semibold">{{ asignaciones.actividad?.id }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.destroy" text="Sí, eliminar" icon="fas fa-xmark" class="btn-danger" :loading="asignaciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.show" title="Editar actividad" icon="fas fa-bicycle" class="xl:w-1/2">
        <template #close>
            <Icon @click="asignaciones.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <Loading-Bar v-if="asignaciones.loading.show" class="h-1 bg-color-4" />
        <div class="grid gap-4">
            <Input v-model="asignaciones.actividad.programa_id" option="select" title="*seleccione programa" disabled readonly>
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Select v-model="asignaciones.actividad.actividad_id" title="*seleccione actividad" :items="catalogos.catalogos_actividad.actividades" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('actividad_id')" />
            <Input v-model="asignaciones.actividad.tipo_actividad_id" option="select" title="*seleccione tipo actividad" :error="store.errors.hasOwnProperty('tipo_actividad_id')">
                <option value=""></option>
                <option v-for="tipo in catalogos.catalogos_actividad.tipos_actividades" :value="tipo.id">{{ tipo.nombre }}</option>
            </Input>
            <Input v-model="asignaciones.actividad.responsable" option="label" title="nombre completo responsable" maxlength="80" :error="asignaciones.errors.hasOwnProperty('responsable')" />
            <div class="grid xl:flex gap-3">
                <Select v-model="asignaciones.actividad.zona_id" title="seleccione zona" :items="catalogos.catalogos_actividad.zonas" :fields="['id','descripcion']" :error="asignaciones.errors.hasOwnProperty('zona_id')" /> 
                <Select v-model="asignaciones.actividad.distrito_id" title="seleccione distrito" :items="catalogos.catalogos_actividad.distritos" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('distrito_id')" />
            </div>
            <Input v-model="asignaciones.actividad.direccion" option="label" title="dirección" maxlength="100" :error="asignaciones.errors.hasOwnProperty('direccion')" />
            <div class="grid xl:flex gap-3">
                <Input v-model="asignaciones.actividad.hora_inicio" option="label" title="hora inicio" type="time" :error="asignaciones.errors.hasOwnProperty('hora_inicia')" />
                <Input v-model="asignaciones.actividad.hora_final" option="label" title="hora final" type="time" :error="asignaciones.errors.hasOwnProperty('hora_final')" />
            </div>
            <div class="grid xl:flex gap-3">
                <Input v-model="asignaciones.actividad.fecha_inicial" option="label" title="*inicia" type="date" :error="asignaciones.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="asignaciones.actividad.fecha_final" option="label" title="*termina" type="date" :error="asignaciones.errors.hasOwnProperty('fecha_final')" />
            </div>
            <Input v-if="asignaciones.actividad.hasOwnProperty('id')" v-model="asignaciones.actividad.estado_actividad_id" option="select" title="*seleccione estado" :error="store.errors.hasOwnProperty('estado_actividad_id')">
                <option value=""></option>
                <option v-for="estado in catalogos.catalogos_actividad.estados_actividades" :value="estado.id">{{ estado.nombre }}</option>
            </Input>
        </div>
        <pre>
            {{ asignaciones.actividad }}
        </pre>
        <hr>
        <pre>
            {{ asignaciones.actividades }}
        </pre>
        <Validate-Errors :errors="asignaciones.errors" v-if="asignaciones.errors != 0" />
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.validateDuplicateActivityList()" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="asignaciones.loading.update" />
        </template>
    </Modal>

</template>

