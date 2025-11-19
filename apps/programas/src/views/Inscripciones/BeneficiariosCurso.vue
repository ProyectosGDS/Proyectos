<script setup>
    import { computed, onBeforeMount } from 'vue'
    import { useBeneficiariosCursoStore } from '@/stores/Inscripciones/beneficiarios-curso'
    import { useProgramasStore } from '@/stores/Catalogos/programas'
    import { useCatalogosStore } from '@/stores/Catalogos/catalogos'
    import { useBeneficiariosStore } from '@/stores/Inscripciones/beneficiarios'
    import { useInscripcionesCursoStore } from '@/stores/Inscripciones/inscripciones-curso'
    import { useAuthStore } from '@/stores/auth'
    import { useGlobalStore } from '@/stores/global'

    import Curso from './BeneficiarioCurso/Curso.vue'

    import DatosPersonales from './Beneficiario/DatosPersonales.vue'
    import Domicilio from './Beneficiario/Domicilio.vue'
    import Responsable from './Beneficiario/Responsable.vue'

    const auth = useAuthStore()
    const global = useGlobalStore()
    const store = useBeneficiariosCursoStore()
    const beneficiarios = useBeneficiariosStore()
    const programas = useProgramasStore()
    const catalogos = useCatalogosStore()
    const inscripciones = useInscripcionesCursoStore()

    function verifyCui () {
        const cui = beneficiarios.cui;
        clearCui()
        if(!cui){
            beneficiarios.messageCui = 'Ingrese cui'
            beneficiarios.success = true
            return false 
        }

        if (cui.length !== 13 || !/^[0-9]{4}\s?[0-9]{5}\s?[0-9]{4}$/.test(cui)) {
            beneficiarios.messageCui = 'CUI no válido.'
            beneficiarios.success = false
            beneficiarios.nuevo_registro = false
            return false
        }

        const cleanCui = cui.replace(/\s/g, '');
        const depto = parseInt(cleanCui.substring(9, 11), 10);
        const muni = parseInt(cleanCui.substring(11, 13), 10);
        const numero = cleanCui.substring(0, 8);
        const verificador = parseInt(cleanCui.substring(8, 9), 10);

        const munisPorDepto = [
            { id: 1, cantidad: 17 }, { id: 2, cantidad: 8 }, { id: 3, cantidad: 16 },
            { id: 4, cantidad: 16 }, { id: 5, cantidad: 13 }, { id: 6, cantidad: 14 },
            { id: 7, cantidad: 19 }, { id: 8, cantidad: 8 }, { id: 9, cantidad: 24 },
            { id: 10, cantidad: 21 }, { id: 11, cantidad: 9 }, { id: 12, cantidad: 30 },
            { id: 13, cantidad: 32 }, { id: 14, cantidad: 21 }, { id: 15, cantidad: 8 },
            { id: 16, cantidad: 17 }, { id: 17, cantidad: 14 }, { id: 18, cantidad: 5 },
            { id: 19, cantidad: 11 }, { id: 20, cantidad: 11 }, { id: 21, cantidad: 7 },
            { id: 22, cantidad: 17 }
        ];

        if (depto === 0 || muni === 0 || depto > munisPorDepto.length || muni > munisPorDepto[depto - 1].cantidad) {
            beneficiarios.messageCui = 'CUI no válido.'
            beneficiarios.success = false
            beneficiarios.nuevo_registro = false
            return false
        }

        const total = numero.split('').reduce((acc, digit, index) => acc + digit * (index + 2), 0)


        if (total % 11 === verificador) {
            beneficiarios.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
            beneficiarios.getBeneficiarioUnico(cleanCui)
            beneficiarios.beneficiario.cui = cleanCui    
            return true
        }

        beneficiarios.messageCui = 'CUI no válido.'
        beneficiarios.success = false
        beneficiarios.nuevo_registro = false
        return false
    }

    function clearCui() {
        if(beneficiarios.cui == '') {
            beneficiarios.nuevo_registro = false
            beneficiarios.errors = []
            beneficiarios.beneficiario = {
                sexo : 'M',
                domicilio : {
                    departamento_id : 7,
                    grupo_zona : {},
                },
                datos_academicos : {},
                datos_medicos : {},
                responsable : {},
                emergencia : {},
                estado : 'V',
            }
        }
    }

    const currentYear = new Date().getFullYear();

    const years = computed(() => {
      const yearsList = []
      for (let i = 0; i <= 1; i++) {
        yearsList.unshift(currentYear - i)
      }
      yearsList.push(currentYear + 1)
      return yearsList
    })

    const searchables = []

    inscripciones.headers.map(el => {
        searchables.push(el.key.toLowerCase().trim())
    })

    const beneficiarios_curso = computed(() => {
        
        return inscripciones.beneficiarios.filter((item) => {
            return searchables.some((column) => {
                const value = global.getNestedValue(item, column)
                return String(value).toLowerCase().includes(store.search.toLowerCase())
            })
        })
    } , { cache: true } )
  
    onBeforeMount(() => {

        if(['5','8'].includes(auth.dependencia_id)) {
            catalogos.getEscuelas(auth.dependencia_id)
        }else {
            programas.fetch()
        }

        const year = new Date()
        inscripciones.year = year.getFullYear() + 1
        catalogos.getCatalogoBeneficiario()
    })

</script>

<template>
    <Card v-if="auth.checkPermission('ver inscribir curso')" class="bg-white p-4 xl:p-8">
        <div class="grid xl:grid-cols-2 xl:divide-x-2">
            <div class="space-y-4 xl:pr-8">
                <div class="flex gap-3">
                    <Input v-model="inscripciones.year" option="select" title="*seleccione año inscripción" :error="store.errors.hasOwnProperty('year')">
                        <option v-for="year in years" :value="year">{{ year }}</option>
                    </Input>
                    <Input v-if="['5','8'].includes(auth.user.dependencia_id)" @change="programas.getProgramasFromEscuelas(JSON.parse(store.escuela).id)" v-model="store.escuela" option="select" title="*Seleccione una escuela" :error="store.errorsDetails.hasOwnProperty('escuela')">
                        <option selected></option>
                        <option v-for="escuela in catalogos.escuelas" :value="JSON.stringify(escuela)">{{ escuela.nombre }}</option>
                    </Input>
                </div>
                <Input @change="store.removeCurso" v-model="inscripciones.programa_id" option="select" title="*seleccione programas" :error="store.errors.hasOwnProperty('programa_id')">
                    <option value=""></option>
                    <template v-for="programa in programas.programas">
                        <option v-if="programa.estado == 'A'" :value="programa.id">{{ programa.nombre }}</option>
                    </template>
                </Input>
                <div class="flex items-center gap-2">
                    <Input @click="store.openCursos" v-model="store.label_curso" option="label" title="*seleccione curso" class="cursor-pointer" :error="store.errors.hasOwnProperty('curso_id')" readonly />
                    <div class="grid gap-2">
                        <Icon @click="store.removeCurso" v-if="store.curso.curso" icon="fas fa-xmark" class="icon-button btn-danger" />
                        <Icon @click="inscripciones.fetch(store.curso.id)" v-if="store.curso.curso" icon="fas fa-arrows-rotate" class="icon-button btn-secondary" :class="{'animate-spin' : inscripciones.loading.fetch}" />
                    </div>
                </div>
                <div>
                    <label class="flex gap-2 cursor-pointer">
                        <input v-model="store.curso.asignacion_extra" type="checkbox" class="size-6">
                        <span>Asignación extra</span>
                    </label>
                </div>
                <div class="col-span-2">
                    <div>
                        <div class="relative">
                            <Input @keypress.enter="verifyCui()" v-model="beneficiarios.cui" option="label" title="*Cui" maxlength="13" type="search" :class="{'focus:border-red-400 border-red-400 focus:outline-red-400': !beneficiarios.success, 'focus:border-green-500 border-green-500 focus:outline-green-400' : beneficiarios.success }" placeholder="Ingrese CUI y presione ENTER" required />
                            <Icon v-if="beneficiarios.loading.show" icon="fas fa-spinner" class="animate-spin absolute top-3 right-3 text-gray-500" />
                        </div>
                        <small :class="beneficiarios.success ? 'text-green-400' : 'text-red-400'">{{ beneficiarios.messageCui }}</small>
                    </div>
                </div>
                <div v-if="!beneficiarios.nuevo_registro">
                    <Input v-model="beneficiarios.beneficiario.nombre_completo" option="label" title="Beneficiario" readonly disabled />
                </div>
                <div v-else>
                    <div v-if="[7,4,5].includes(beneficiarios.codeFetchBeneficiario)">
                        <DatosPersonales />
                        <Domicilio />
                        <Responsable v-if="beneficiarios.beneficiario.edad < 18" />
                    </div>
                </div>
                <Validate-Errors :errors="store.errorsDetails" v-if="store.errorsDetails != 0" />
                <Validate-Errors :errors="beneficiarios.errors" v-if="beneficiarios.errors != 0" />
                <div class="flex justify-center gap-4">
                    <Tool-Tip v-if="!beneficiarios.nuevo_registro" message="Agregar beneficiario al curso" class="-mt-6 text-color-4">
                        <Button @click="store.addBeneficiario()" icon="fas fa-plus" class="btn-primary" />
                    </Tool-Tip>
                    <Tool-Tip v-else message="Agregar beneficiario al curso" class="-mt-6 text-color-4">
                        <Button v-if="[7,4,5].includes(beneficiarios.codeFetchBeneficiario)" @click="store.saveAddBeneficiario()" icon="fas fa-save" text="Guardar y agregar beneficiario" class="btn-primary" :loading="beneficiarios.loading.store" />
                    </Tool-Tip>
                </div>
            </div>
            <div class="xl:pl-8">
                <h1 class="text-center text-2xl font-medium text-gray-500">
                    Cupo : {{ inscripciones.cupo }}
                </h1>
                <div class="flex items-center gap-4">
                    <Input v-model="store.search" icon="fas fa-search" type="search" placeholder="Buscar beneficiario .. " class="h-11" />
                    <Tool-Tip message="Excel" class="-mt-7 text-color-4">
                        <Icon v-if="auth.checkPermission('exportar excel inscripciones curso')" @click="inscripciones.exportExcel" :icon="inscripciones.loading.excel ? 'fas fa-spinner' : 'fas fa-file-excel'" class="icon-button p-2 btn-success" :class="inscripciones.loading.excel ? 'animate-spin bg-gray-300 text-gray-500' : ''" :disabled="inscripciones.loading.excel" />
                    </Tool-Tip>
                </div>
                <br>
                <div class="grid" v-if="inscripciones.loading.fetch" >
                    <Loading-Bar class="bg-color-4 h-1"/>
                    <h1 class="text-center text-gray-400 text-xs animate-pulse">Cargando data ...</h1>
                </div>
                <div class="h-[40rem] overflow-y-auto">
                    <div class="grid gap-4 pr-4">
                        <template v-for="(inscripcion,index) in beneficiarios_curso">
                            <div class="flex gap-2">
                                <Card class="p-4 w-full" :class="{'bg-green-200 text-green-700' : inscripcion.id && inscripcion.estado == 'A', 'bg-red-200 text-red-700' : inscripcion.id && inscripcion.estado == 'I', 'bg-gray-200' : !inscripcion.hasOwnProperty('id') }">
                                    <div class="grid xl:grid-cols-2 gap-2 text-xs uppercase">
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-image-portrait" />
                                                ID INSCRIPCIÓN: 
                                                <span class="font-medium">{{ inscripcion.id ?? '' }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-calendar-days" />
                                                AÑO INSCRIPCIÓN: 
                                                <span class="font-medium">{{ inscripcion.anio_inscripcion }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-id-card" />
                                                CUI: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.cui }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-key" />
                                                INTERLOCUTOR: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.interlocutor ?? null }}</span>
                                            </span>
                                        </span> 
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-user" />
                                                BENEFICIARIO: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.nombre_completo }}</span>
                                            </span>
                                        </span>                                       
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-cake-candles" />
                                                EDAD: 
                                                <span class="font-medium">{{ inscripcion.beneficiario.edad + ' años' }}</span>
                                            </span>
                                        </span>
                                        <span>
                                            <span class="flex items-center gap-1">
                                                <Icon icon="fas fa-medal" />
                                                BECADO: 
                                                <span class="font-medium">{{ inscripcion.becado ? 'Sí' : 'No'  }}</span>
                                            </span>
                                        </span>
                                    </div>
                                </Card>
                                <div class="grid">
                                    <Icon @click="store.removeInscripcion(inscripcion,index)" icon="fas fa-trash" class="icon-button btn-danger" />
                                    <template v-if="inscripcion.id">
                                        <Icon v-if="auth.checkPermission('asignar beca curso')" @click="inscripciones.showBeca(inscripcion)" icon="fas fa-medal" class="icon-button btn-secondary" title="Asignar beca" />
                                    </template>
                                    <template v-if="auth.checkPermission('desactivar inscripcion curso')">
                                        <Icon v-if="inscripcion.id" @click="store.changeEstadoInscripcion(inscripcion)" :icon="inscripcion.estado == 'A' ? 'fas fa-xmark' : 'fas fa-check'" class="icon-button" :class="inscripcion.estado == 'A' ? 'btn-danger' : 'btn-success'" :title="inscripcion.estado == 'A' ? 'Deshabilitar' : 'Habilitar'" />
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div v-if="inscripciones.beneficiarios.length && inscripciones.cupo > 0" class="flex justify-center gap-4">
                    <Button 
                        v-if="auth.checkPermission('crear inscripcion curso')" 
                        @click="inscripciones.store" 
                        text="Inscribir beneficiarios nuevos al curso" 
                        icon="fas fa-plus" 
                        class="btn-primary absolute bottom-4" 
                        :loading="inscripciones.loading.store"
                    />
                </div>
            </div>
        </div>
    </Card>

    <!-- MODALES -->
   
    <Modal :open="store.modal.cursos" title="Cursos" icon="fas fa-book">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="horarior-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Curso />
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.selectedCurso" text="Seleccionar" icon="fas fa-check" class="btn-primary"/>
        </template>
    </Modal>

    <Modal :open="inscripciones.modal.beca" title="Asignar beca" icon="fas fa-medal">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-medal" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de asignar la beca a:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="inscripciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="inscripciones.assingBeca" text="Si, asignar" icon="fas fa-check" class="btn-primary" :loading="inscripciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="inscripciones.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de eliminar la inscripción de:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="inscripciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="inscripciones.destroy" text="Sí, elminar" icon="fas fa-trash" class="btn-danger" :loading="inscripciones.loading.destroy" />
        </template>
    </Modal>

    <Modal :open="inscripciones.modal.disabled">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de deshabilitar/habilitar la inscripción de:?</p>
                <h1 class="text-center font-semibold">{{ inscripciones.inscripcion?.beneficiario?.nombre_completo }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="inscripciones.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="inscripciones.update" :text="inscripciones.inscripcion?.estado == 'A' ? 'Sí, habilitar' : 'Sí, deshabilitar'" :icon="inscripciones.inscripcion?.estado == 'A' ? 'fas fa-check' : 'fas fa-xmark'" class="btn-danger" :loading="inscripciones.loading.update" />
        </template>
    </Modal>
</template>

