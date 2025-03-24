<script setup>
    import { computed, onBeforeMount } from 'vue'

    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useCursosProgramaStore } from '@/stores/Asignaciones/cursos-programa'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useAuthStore } from '@/stores/auth'
    import { useAsignacionesCursosProgramaStore } from '@/stores/Asignaciones/asignaciones-cursos-programa'
    import { useRequisitosStore } from '@/stores/Catalogos/requisitos'

    import Curso from './CursosPrograma/Curso.vue'
    import Instructor from './CursosPrograma/Instructor.vue'
    import Horario from './CursosPrograma/Horario.vue'
    import Sede from './CursosPrograma/Sede.vue'
    import Select from '@/components/Select.vue'
    

    const store = useCursosProgramaStore()
    const asignaciones = useAsignacionesCursosProgramaStore()
    const programas = useProgramasStore()
    const catalogos = useCatalogosStore()
    const requisitos = useRequisitosStore()
    const auth = useAuthStore()


    const searchables = []

    asignaciones.headers.map(el => {
        searchables.push(el.key.toLowerCase().trim())
    })

    const searching_cursos = computed(() => {
        
        return asignaciones.cursos.filter((item) => {
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

    
    onBeforeMount(() => {
        programas.fetch()
        catalogos.getCatalogosCurso()
        requisitos.fetch()
    })

</script>

<template>
    <Card v-if="auth.checkPermission('ver cursos programa')" class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2">
            <div class="space-y-4 xl:pr-8">
                <div class="flex items-center gap-2">
                    <Input @change="asignaciones.fetch(asignaciones.programa_id)"  v-model="asignaciones.programa_id" option="select" title="*seleccione programas" :error="store.errorsDetails.hasOwnProperty('programa_id')">
                        <option value=""></option>
                        <template v-for="programa in programas.programas">
                            <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                        </template>
                    </Input>
                    <Icon v-if="asignaciones.programa_id" @click="asignaciones.fetch(asignaciones.programa_id)" icon="fas fa-arrows-rotate" class="icon-button btn-secondary" :class="{'animate-spin' : asignaciones.loading.fetch }" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('curso')" v-model="store.curso.curso.nombre" option="label" title="*seleccione curso" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('curso_id')" readonly />
                    <Icon @click="store.removeItem('curso')" v-if="store.curso.curso.nombre" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('instructor')" v-model="store.curso.instructor.nombre" option="label" title="*seleccione instructor" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('instructor_id')" readonly />
                    <Icon @click="store.removeItem('instructor')" v-if="store.curso.instructor.nombre" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('sede')" v-model="store.curso.sede.nombre_completo" option="label" title="*seleccione sede" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('sede_id')" readonly />
                    <Icon @click="store.removeItem('sede')" v-if="store.curso.sede.nombre_completo" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('horario')" v-model="store.curso.horario.nombre_completo" option="label" title="*seleccione horario" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('horario_id')" readonly />
                    <Icon @click="store.removeItem('horario')" v-if="store.curso.horario.nombre_completo" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <Input v-model="store.curso.temporalidad" option="select" title="*seleccione temporalidad" :error="store.errorsDetails.hasOwnProperty('temporalidad_id')">
                    <option value=""></option>
                    <option v-for="temporalidad in catalogos.catalogos_curso.temporalidades" :value="JSON.stringify(temporalidad)">{{ temporalidad.nombre }}</option>
                </Input>
                <Input v-model="store.curso.seccion" option="label" title="sección" maxlength="45" :error="store.errorsDetails.hasOwnProperty('seccion')" />
                <Input v-model="store.curso.capacidad" option="label" title="*Capacidad" type="number" min="0" :error="store.errorsDetails.hasOwnProperty('capacidad')" />
                <div class="grid xl:flex gap-4">
                    <Input v-model="store.curso.fecha_inicial" option="label" title="*inicia" type="date" :error="store.errorsDetails.hasOwnProperty('fecha_inicial')" />
                    <Input v-model="store.curso.fecha_final" option="label" title="*termina" type="date" :error="store.errorsDetails.hasOwnProperty('fecha_final')" />
                </div>
                <div class="flex justify-evenly text-color-4">
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="store.curso.modalidad" value="PRESENCIAL" name="modalidad">
                        <span>PRESENCIAL</span>
                    </label>
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="store.curso.modalidad" value="VIRTUAL" name="modalidad">
                        <span>VIRTUAL</span>
                    </label>
                    <label class="flex gap-2 cursor-pointer">
                        <input type="radio" v-model="store.curso.modalidad" value="HIBRIDA" name="modalidad">
                        <span>HIBRIDA</span>
                    </label>
                </div>
                <Validate-Errors :errors="store.errorsDetails" v-if="store.errorsDetails != 0" />
                <div class="flex justify-center gap-4">
                    <Button @click="store.addCurso()" icon="fas fa-plus" class="btn-primary" title="Agregar al programa" :disabled="store.editDetails" />
                </div>
            </div>
            <div class="xl:pl-8">
                <h1 class="text-center text-2xl font-medium text-gray-500">
                    Cantidad de cursos : {{ asignaciones.cursos.length }}
                </h1>
                <div class="flex items-center gap-4">
                    <Input v-model="store.search" icon="fas fa-search" type="search" placeholder="Buscar curso .. " class="h-11" />
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar excel cursos programa')" @click="asignaciones.exportExcel" :icon="asignaciones.loading.excel ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="asignaciones.loading.excel ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="asignaciones.loading.excel" />
                    </Tool-Tip>
                </div>
                <br>
                <div class="grid" v-if="asignaciones.loading.fetch" >
                    <Loading-Bar class="bg-color-4 h-1"/>
                    <h1 class="text-center text-gray-400 text-xs animate-pulse">Cargando data ...</h1>
                </div>
                <div class="h-[40rem] overflow-y-auto">
                    <div class="grid gap-4">
                        <template v-for="(asignacion,index) in searching_cursos">
                            <div class="flex gap-2">
                                <Card class="p-4 w-full" :class="{'bg-green-200' : asignacion.id && asignacion.estado == 'A', 'bg-red-200' : asignacion.id && asignacion.estado == 'I', 'bg-gray-200' : !asignacion.hasOwnProperty('id') }">
                                    <div class="grid grid-cols-2 gap-1 text-xs uppercase">
                                        <span>
                                            <span class="font-medium">ID ASIGNACIÓN: </span>
                                            <span>{{ asignacion.id ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">TEMPORALIDAD: </span>
                                            <span>{{ asignacion.temporalidad }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">CURSO: </span>
                                            <span>{{ asignacion.curso }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">INSTRUCTOR: </span>
                                            <span>{{ asignacion.instructor }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">HORARIO: </span>
                                            <span>{{ asignacion.horario }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">SECCIÓN: </span>
                                            <span>{{ asignacion.seccion }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">CAPACIDAD: </span>
                                            <span>{{ asignacion.capacidad }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">MODALIDAD: </span>
                                            <span>{{ asignacion.modalidad }}</span>
                                        </span>
                                        <span class="col-span-2">
                                            <span class="font-medium">SEDE: </span>
                                            <span>{{ asignacion.sede }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">INICIA: </span>
                                            <span>{{ asignacion.fecha_inicial ?? '' }}</span>
                                        </span>
                                        <span>
                                            <span class="font-medium">TERMINA: </span>
                                            <span>{{ asignacion.fecha_final ?? '' }}</span>
                                        </span>
                                    </div>
                                </Card>
                                <div class="grid items-center pr-4">
                                    <Icon v-if="!asignacion.hasOwnProperty('id')" @click="store.removeCurso(index)" icon="fas fa-trash" class="icon-button btn-danger" />
                                    <template v-if="asignacion.estado == 'A'">
                                        <Icon v-if="auth.checkPermission('desactivar cursos programa')" @click="asignaciones.disabledCurso(asignacion)" icon="fas fa-xmark" title="Desactivar curso" class="icon-button btn-danger" />
                                    </template>
                                    <Icon v-if="auth.checkPermission('asignar requisitos curso')" @click="asignaciones.assignRequirements(asignacion)" icon="fas fa-list-check" class="icon-button btn-secondary" title="Asignar requisitos" />
                                    <template v-if="asignacion.hasOwnProperty('id')">
                                        <Icon v-if="auth.checkPermission('editar cursos programa')" @click="asignaciones.show(asignacion.id)" icon="fas fa-pencil" title="editar" class="icon-button btn-secondary" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <br>
                <div v-if="asignaciones.cursos.length" class="flex justify-center gap-4">
                    <Button v-if="auth.checkPermission('crear cursos programa')" @click="asignaciones.store" text="Asignar cursos nuevos al programa" icon="fas fa-plus" class="btn-primary" :loading="asignaciones.loading.store"/>
                </div>
            </div>
        </div>
    </Card>

    <Modal :open="store.modal.curso" title="Cursos" icon="fas fa-book">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Curso />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('curso')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="store.modal.instructor" title="Instructores" icon="fas fa-chalkboard-user">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Instructor />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('instructor')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="store.modal.sede" title="Sedes" icon="fas fa-school">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Sede />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('sede')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="store.modal.horario" title="Horarios" icon="fas fa-clock">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Horario />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('horario')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de deshabilitar el curso con el id:?</p>
                <h1 class="text-center font-semibold">{{ asignaciones.curso?.id }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.disabled" text="Sí, deshabilitar" icon="fas fa-xmark" class="btn-danger" :loading="asignaciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.edit" title="Editar curso" icon="fas fa-book" class="xl:w-1/2">
        <template #close>
            <Icon @click="asignaciones.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <Loading-Bar v-if="asignaciones.loading.show" class="h-1 bg-color-4" />
        <div class="grid gap-4">
            <div v-if="asignaciones.curso.estado == 'I'" class="flex justify-end">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Activar</span>
                    <Switch v-model="asignaciones.curso.estado" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['A','I']" />
                    <span class="text-sm text-gray-500">Inactivo</span>
                </div>
            </div>
            <Input v-model="asignaciones.curso.programa_id" @change="asignaciones.getModulosPrograma" option="select" title="*seleccione programa" disabled readonly>
                <option value=""></option>
                <template v-for="programa in programas.programas">
                    <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                </template>
            </Input>
            <Select v-model="asignaciones.curso.curso_id" title="*seleccione curso" :items="catalogos.catalogos_curso.cursos" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('curso_id')" />
            <Select v-model="asignaciones.curso.instructor_id" title="*seleccione instructor" :items="catalogos.catalogos_curso.instructores" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('instructor_id')" />
            <Select v-model="asignaciones.curso.sede_id" title="*seleccione sede" :items="catalogos.catalogos_curso.sedes" :fields="['id','nombre_completo']" :error="asignaciones.errors.hasOwnProperty('sede_id')" />
            <Select v-model="asignaciones.curso.horario_id" title="*seleccione horario" :items="catalogos.catalogos_curso.horarios" :fields="['id','nombre_completo']" :error="asignaciones.errors.hasOwnProperty('horario_id')" />
            <Input v-model="asignaciones.curso.temporalidad_id" option="select" title="*seleccione temporalidad" :error="asignaciones.errors.hasOwnProperty('temporalidad_id')">
                <option value=""></option>
                <option v-for="temporalidad in catalogos.catalogos_curso.temporalidades" :value="temporalidad.id">{{ temporalidad.nombre }}</option>
            </Input>
            <Input v-model="asignaciones.curso.seccion" option="label" title="sección" maxlength="45" :error="asignaciones.errors.hasOwnProperty('seccion')" />
            <Input v-model="asignaciones.curso.capacidad" option="label" title="*Capacidad" type="number" min="0" :error="asignaciones.errors.hasOwnProperty('capacidad')" />
            <div class="grid xl:flex gap-3">
                <Input v-model="asignaciones.curso.fecha_inicial" option="label" title="inicia" type="date" :error="asignaciones.errors.hasOwnProperty('fecha_inicial')" />
                <Input v-model="asignaciones.curso.fecha_final" option="label" title="termina" type="date" :error="asignaciones.errors.hasOwnProperty('fecha_final')" />
            </div>
            <div class="flex justify-evenly">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">PÚBLICO</span>
                    <Switch v-model="asignaciones.curso.publico" class="h-auto w-14 bg-red-400 has-[:checked]:bg-green-500" :values="['S','N']" />
                    <span class="text-sm text-gray-500">PRIVADO</span>
                </div>
            </div>
            <div class="flex justify-evenly text-color-4">
                <label class="flex gap-2 cursor-pointer">
                    <input type="radio" v-model="asignaciones.curso.modalidad" value="PRESENCIAL" name="modalidad">
                    <span>PRESENCIAL</span>
                </label>
                <label class="flex gap-2 cursor-pointer">
                    <input type="radio" v-model="asignaciones.curso.modalidad" value="VIRTUAL" name="modalidad">
                    <span>VIRTUAL</span>
                </label>
                <label class="flex gap-2 cursor-pointer">
                    <input type="radio" v-model="asignaciones.curso.modalidad" value="HIBRIDA" name="modalidad">
                    <span>HIBRIDA</span>
                </label>
            </div>
        </div>
        <Validate-Errors :errors="asignaciones.errors" v-if="asignaciones.errors != 0" />
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.validateDuplicateCourseList" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="asignaciones.loading.update" />
        </template>
    </Modal>
    
    <Modal :open="asignaciones.modal.requisitos" title="Asignar requisitos a curso" icon="fas fa-list-check">
        <template #close>
            <Icon @click="asignaciones.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div>
            <Input v-model="asignaciones.curso.curso" option="label" title="Curso seleccionado" readonly disabled/>
            <br>
            <details open class="border rounded-md border-color-4 text-color-1 uppercase p-4">
                <summary>Requisitos disponibles</summary>
                <br>
                <div class="grid grid-cols-2 gap-4">
                    <label v-for="requisito in requisitos.requisitos" class="flex items-center gap-2"> 
                        <input type="checkbox" v-model="asignaciones.selected_requirements" :value="requisito.id">
                        <span>{{ requisito.nombre }}</span>
                    </label>
                </div>
            </details>
        </div>
        
        <Validate-Errors :errors="asignaciones.errors" v-if="asignaciones.errors != 0" />
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.assign" text="Asignar" icon="fas fa-check" class="btn-primary" :loading="asignaciones.loading.update" />
        </template>
    </Modal>

</template>

