<script setup>
    import { onBeforeMount } from 'vue'
    import {usePaginasStore} from '@/stores/paginas'
    import { useAuthStore } from '@/stores/auth'

    const store = usePaginasStore()
    const auth = useAuthStore()
    

    onBeforeMount(() => {
        store.fetch()
    })

</script>

<template>
    <Card class="bg-white px-4 py-8">
        <div v-if="auth.checkPermission('crear pagina')" class="flex justify-center">
            <Tool-Tip message="Nuevo pagina" class="-mt-7 text-color-4">
                <Button @click="store.modal.new = true" icon="fas fa-plus" class="btn-primary" />
            </Tool-Tip>
        </div>
        <Data-Table v-if="auth.checkPermission('ver paginas')" :headers="store.headers" :data="store.paginas" :loading="store.loading.fetch" :excel="auth.checkPermission('exportar excel paginas')">
            <template #vista-icono="{item}">
                <Icon :icon="item.icon" class="text-color-4" />
            </template>
            <template #actions="{item}">
                <Drop-Down-Button icon="fas fa-ellipsis-v" >
                    <ul>
                        <li v-if="auth.checkPermission('editar pagina')" @click="store.edit(item)" class="text-color-4">Editar</li>
                        <li v-if="auth.checkPermission('eliminar pagina')" @click="store.remove(item)" class="text-red-400">Eliminar</li>
                    </ul>
                </Drop-Down-Button>
            </template>
        </Data-Table>
    </Card>

    <Modal :open="store.modal.new" title="Crear pagina" icon="fas fa-globe">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.pagina.titulo" option="label" title="*titulo" maxlength="80" :error="store.errors.hasOwnProperty('titulo')" />
            <Input v-model="store.pagina.link" option="label" title="link" maxlength="80" :error="store.errors.hasOwnProperty('link')" />
            <div class="flex gap-2 items-center">
                <Input v-model="store.pagina.icon" option="label" title="icono" maxlength="45" :error="store.errors.hasOwnProperty('icon')" />
                <Icon :icon="store.pagina.icon" class="text-color-4 text-4xl" />
            </div>
            <Input v-model="store.pagina.orden" option="label" title="orden" type="number" min="1" :error="store.errors.hasOwnProperty('orden')" />
            <Input v-model="store.pagina.pagina_id" option="select" title="paginas" :error="store.errors.hasOwnProperty('pagina_id')">
                <option value=""></option>
                <option v-for="pagina in store.paginas" :value="pagina.id">{{ pagina.titulo }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.store" text="Guardar" icon="fas fa-save" class="btn-primary" :loading="store.loading.store" />
        </template>
    </Modal>
    <Modal :open="store.modal.edit" title="Editar pagina" icon="fas fa-globe">
        <template #close>
            <Icon @click="store.resetData" icon="fas fa-xmark" class="cursor-pointer text-white" />
        </template>
        <div class="grid gap-4">
            <Input v-model="store.pagina.titulo" option="label" title="*titulo" maxlength="80" :error="store.errors.hasOwnProperty('titulo')" />
            <Input v-model="store.pagina.link" option="label" title="link" maxlength="80" :error="store.errors.hasOwnProperty('link')" />
            <div class="flex gap-2 items-center">
                <Input v-model="store.pagina.icon" option="label" title="icono" maxlength="45" :error="store.errors.hasOwnProperty('icon')" />
                <Icon :icon="store.pagina.icon" class="text-color-4 text-4xl" />
            </div>
            <Input v-model="store.pagina.orden" option="label" title="orden" type="number" min="1" :error="store.errors.hasOwnProperty('orden')" />
            <Input v-model="store.pagina.pagina_id" option="select" title="paginas" :error="store.errors.hasOwnProperty('pagina_id')">
                <option value=""></option>
                <option v-for="pagina in store.paginas" :value="pagina.id">{{ pagina.titulo }}</option>
            </Input>
        </div>
        <Validate-Errors :errors="store.errors" v-if="store.errors != 0" />
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.update" text="Actualizar" icon="fas fa-arrows-rotate" class="btn-primary" :loading="store.loading.update" />
        </template>
    </Modal>
    <Modal :open="store.modal.delete">
        <div class="flex items-center justify-center gap-4">
            <Icon icon="fas fa-exclamation-triangle" class="text-orange-500 text-5xl" />
            <div>
                <p class="text-center text-lg">¿Estás seguro de eliminar la pagina?</p>
                <h1 class="text-center font-semibold">{{ store.pagina.titulo }}</h1>
            </div>
        </div>
        <template #footer>
            <Button @click="store.resetData" text="Cancelar" icon="fas fa-xmark" class="btn-secondary" />
            <Button @click="store.destroy" text="Sí, eliminar" icon="fas fa-trash" class="btn-danger" :loading="store.loading.destroy" />
        </template>
    </Modal>
</template>

<style scoped>
    li {
        @apply cursor-pointer text-xs text-nowrap hover:bg-slate-100 py-1 px-3 rounded-lg;
    }
</style>