<script setup>
    import { computed, onMounted, watchEffect } from 'vue'

    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useCursosModuloStore } from '@/stores/Asignaciones/cursos-modulo'
    import { useAuthStore } from '@/stores/auth'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useAsignacionesCursosModuloStore } from '@/stores/Asignaciones/asignaciones-cursos-modulo'
    import { useGlobalStore } from '@/stores/global'
    import { useHorariosStore } from '@/stores/Catalogos/horarios'

    import Curso from './CursosModulo/Curso.vue'
    import Modulo from './CursosModulo/Modulo.vue'
    import Instructor from './CursosModulo/Instructor.vue'
    import Horario from './CursosModulo/Horario.vue'
    import Select from '@/components/Select.vue'
        
    const asignaciones = useAsignacionesCursosModuloStore()
    const store = useCursosModuloStore()
    const programas = useProgramasStore()
    const catalogos = useCatalogosStore()
    const horarios = useHorariosStore()
    const auth = useAuthStore()
    const global = useGlobalStore()


    const searchables = []

    asignaciones.headers.map(el => {
        searchables.push(el.key.toLowerCase().trim())
    })

    const searching_cursos = computed(() => {
        return asignaciones.cursos.filter((item) => {
            return searchables.some((column) => {
                const value = global.getNestedValue(item, column)
                return String(value).toLowerCase().includes(store.search.toLowerCase()) 
            })
        })
    } , { cache: true } ) 
        
    onMounted(() => {
     
        if(auth.dependencia_id && auth.dependencia_id == 5){
            catalogos.getEscuelas()
        } else {
            programas.fetch()
        }
        
        catalogos.getTemporalidades()
        catalogos.getCatalogosCurso()
    })
</script>

<template>
    <Card v-if="auth.checkPermission('ver cursos modulo')" class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2 xl:divide-x-2">
            <div class="space-y-4 xl:pr-8">
                <Input v-if="auth.user.dependencia_id == 5" @change="programas.getProgramasFromEscuelas(store.escuela)" v-model="store.escuela" option="select" title="*Seleccione una escuela" :error="store.errorsDetails.hasOwnProperty('escuela')">
                    <option selected></option>
                    <option v-for="escuela in catalogos.escuelas" :value="escuela">{{ escuela }}</option>
                </Input>
                <div class="flex items-center gap-3 relative">
                    <Input @change="store.removeItem('modulo')" v-model="store.programa_id" option="select" title="*seleccione programa" :error="store.errorsDetails.hasOwnProperty('programa_id')" :disabled="!programas.programas.length">
                        <option value=""></option>
                        <template v-for="programa in programas.programas">
                            <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                        </template>
                    </Input>
                    <Icon v-if="programas.loading.fetch" icon="fas fa-spinner" class="text-gray-400 animate-spin absolute right-5" />
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('modulo')" v-model="store.nombre_completo" option="label" title="*seleccione módulo / grado" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('modulo_id')" readonly />
                    <div class="grid gap-2">
                        <Icon @click="store.removeItem('modulo')" v-if="store.modulo.nombre" icon="fas fa-xmark" class="icon-button btn-danger" />
                        <Icon @click="asignaciones.fetch(store.modulo.id)" v-if="store.modulo.nombre" icon="fas fa-arrows-rotate" class="icon-button btn-secondary" :class="{'animate-spin': asignaciones.loading.fetch}" />
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Input @click="store.openModal('curso')" v-model="store.label_curso" option="label" title="*seleccione cursos" class="cursor-pointer" :error="store.errorsDetails.hasOwnProperty('curso_id')" readonly />
                    <Icon @click="store.removeItem('curso')" v-if="store.curso.curso.length" icon="fas fa-xmark" class="icon-button btn-danger" />
                </div>
                <Validate-Errors :errors="store.errorsDetails" v-if="store.errorsDetails != 0" />
                <div class="flex justify-center gap-4">
                    <Tool-Tip message="Agregar curso al listado" class="text-color-4 -mt-6">
                        <Button @click="store.addCurso()" icon="fas fa-plus" class="btn-primary" />
                    </Tool-Tip>
                </div>
            </div>
            <div class="xl:pl-8">
                <div class="flex justify-around">
                    <h1 class="text-2xl font-medium text-gray-500">
                        Asignar Instructor y Horario
                    </h1>
                    <h1 class="text-2xl font-medium text-gray-500">
                        Cursos : {{ asignaciones.cursos.length }}
                    </h1>
                </div>
                <div class="flex items-center gap-4 py-2">
                    <Input v-model="store.search" icon="fas fa-search" type="search" placeholder="Buscar curso .. " class="h-11" />
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar excel cursos modulo')" @click="asignaciones.exportExcel" :icon="asignaciones.loading.excel ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="asignaciones.loading.excel ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="asignaciones.loading.excel" />
                    </Tool-Tip>
                </div>
                <div class="grid" v-if="asignaciones.loading.fetch" >
                    <Loading-Bar class="bg-color-4 h-1"/>
                    <h1 class="text-center text-gray-400 text-xs animate-pulse">Cargando data ...</h1>
                </div>
                <div class="h-[42rem] overflow-y-auto">
                    <div class="grid gap-4">
                        <template v-for="(asignacion,index) in searching_cursos">
                            <div class="flex gap-2 pr-4">
                                <Card class="p-4 w-full" :class=" {'bg-green-200 text-green-700' : asignacion.modulo_id && asignacion.detalle_curso_id,'bg-gray-200 ' : !asignacion.modulo_id && !asignacion.detalle_curso_id, 'bg-red-200 text-red-700' : asignacion.curso.estado == 'I' ,'bg-orange-300 animate-pulse ' : store.IndexesError.includes(index) }">
                                    <div class="grid grid-cols-2 gap-1 text-xs uppercase">
                                        <span>
                                            <span class="flex gap-2 items-center">
                                                <Icon icon="fas fa-user"/>
                                                ID CURSO: 
                                                <span class="font-medium">{{ asignacion.detalle_curso_id }}</span>
                                            </span>
                                        </span>
                                        <span></span>
                                        <span>
                                            <span class="flex gap-2 items-center">
                                                <Icon icon="fas fa-book"/>
                                                CURSO: 
                                                <span class="font-medium">{{ asignacion.curso.curso.nombre }}</span>
                                            </span>
                                        </span>
                                        <span v-if="asignacion.curso.instructor.nombre">
                                            <span class="flex gap-2 items-center">
                                                <Icon icon="fas fa-person-chalkboard"/>
                                                INSTRUCTOR: 
                                                <span class="font-medium">{{ asignacion.curso.instructor.nombre }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex gap-2 items-center">
                                                <Icon icon="fas fa-clock"/>
                                                HORARIOS: 
                                            </span>
                                            <ul class=" list-decimal pl-4">
                                                <li v-for="horario in asignacion.curso.horarios">{{ horario.nombre_completo }}</li>
                                            </ul>
                                        </span>
                                        <span class="col-span-2">
                                            <span class="flex gap-2 items-center">
                                                <Icon icon="fas fa-school"/>
                                                SEDE: 
                                                <span class="font-medium">{{ asignacion.curso.sede.nombre_completo }}</span>
                                            </span>
                                        </span>
                                    </div>
                                </Card>
                                <div class="grid items-center">
                                    <Icon v-if="!asignacion.detalle_curso_id && !asignacion.modulo_id" @click="store.openModalDetails('instructor',index)" icon="fas fa-chalkboard-user" class="icon-button btn-secondary"/>
                                    <Icon v-if="!asignacion.detalle_curso_id && !asignacion.modulo_id" @click="store.openModalDetails('horarios',index)" icon="fas fa-clock" class="icon-button btn-secondary" title="Asignar horario" />
                                    <Icon v-if="!asignacion.detalle_curso_id && !asignacion.modulo_id" @click="store.removeCurso(index)" icon="fas fa-trash" class="icon-button btn-danger" title="Remover de la lista" />
                                    <template v-if="asignacion.detalle_curso_id && asignacion.modulo_id" >
                                        <Icon @click="asignaciones.showHorarios(asignacion.detalle_curso_id)" icon="fas fa-clock" class="icon-button btn-secondary" title="Asignar horario" />
                                    </template>
                                    <template v-if="asignacion.detalle_curso_id && asignacion.modulo_id">
                                        <Icon v-if="auth.checkPermission('editar cursos modulo')" @click="asignaciones.show(asignacion.detalle_curso_id)" icon="fas fa-pencil" class="icon-button btn-secondary" title="Editar curso" />
                                    </template>
                                    <template v-if="asignacion.detalle_curso_id && asignacion.modulo_id && asignacion.curso.estado == 'A'">
                                        <Icon v-if="auth.checkPermission('desactivar cursos modulo')" @click="asignaciones.disabled(asignacion)" icon="fas fa-xmark" class="icon-button btn-danger" title="Deshabilitar curso" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <br>
                <div v-if="asignaciones.cursos.length" class="flex justify-center gap-4">
                    <Button @click="store.validateDataCourse" text="Asignar cursos nuevos al módulo" icon="fas fa-plus" class="btn-primary" :loading="asignaciones.loading.store"/>
                </div>
            </div>
        </div>
    </Card>

    <!-- MODALES -->
    <Modal :open="store.modal.modulo" title="Modulos" icon="fas fa-folder-tree">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <Modulo />
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedItem('modulo')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="store.modal.curso" title="Cursos" icon="fas fa-book">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
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
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Instructor />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectDetails('instructor')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="store.modal.horarios" title="Horarios" icon="fas fa-clock">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Horario />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectDetails('horarios')" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.edit" title="Editar curso asignado" icon="fas fa-book" class="w-1/2">
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
            <Select v-model="asignaciones.curso.curso_id" title="*seleccione curso" :items="catalogos.catalogos_curso.cursos" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('curso_id')" />
            <Select v-model="asignaciones.curso.instructor_id" title="*seleccione instructor" :items="catalogos.catalogos_curso.instructores" :fields="['id','nombre']" :error="asignaciones.errors.hasOwnProperty('instructor_id')" />
            <div>
                <fieldset class="border border-gray-200 text-color-1 p-4 rounded-lg">
                    <legend class="px-2 font-medium text-color-4">HORARIOS</legend>
                    <ul class="disc">
                        <li v-for="horario in asignaciones.curso.horarios">{{ horario.nombre_completo }}</li>
                    </ul>
                </fieldset>
            </div>
        </div>
        <Validate-Errors :errors="asignaciones.errors" v-if="asignaciones.errors != 0" />
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.validateDuplicateCourseList" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="asignaciones.loading.update" />
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.disabled">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de desactivar el curso con el id: ?</p>
                <h1 class="text-center font-semibold">{{ asignaciones.curso.detalle_curso_id }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.disabledCurso" text="Sí, desactivar" icon="fas fa-trash" class="btn-danger" :loading="asignaciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="asignaciones.modal.horarios" title="Editar horarios" icon="fas fa-clock">
        <template #close>
            <Icon @click="asignaciones.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Data-Table 
                :headers="horarios.headers" 
                :data="horarios.horarios"
                :loading="horarios.loading.fetch"
                :excel="false" 
                :rowsPerPage="5" 
                :multiSelect="true"
                @selectdAllItems="asignaciones.selectHorario" 
                :itemsSelected="asignaciones.horarios" 
            />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="asignaciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="asignaciones.syncHorarios()" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary"/>
        </template>
    </Modal>
</template>

