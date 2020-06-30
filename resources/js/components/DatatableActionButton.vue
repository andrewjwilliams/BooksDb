<template>
    <button :class="classes" @click="click(data)" :title="meta.title" type='button'>
        <font-awesome-icon v-if="meta.icon" :icon="meta.icon"></font-awesome-icon>
        
        {{ name }}
    </button>
</template>

<script>
export default {
    props: {
        data: {},
        name: {},
		meta: {},
        click: {
            type: Function,
            default: () => {}
        },
        classes: {
            type: Object,
            default: () => ({ 
                'btn': true,
                'btn-primary': true,
                'btn-sm': true,
            }),
        },
    }
}
</script>
