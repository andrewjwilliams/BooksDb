<template>
	<div>
		<datatable-action-button 
			v-for="item in meta.buttons"  
			v-bind:name="item.name"
			v-bind:classes="item.classes" 
			v-bind:meta="item.meta" 
			v-bind:click="item.handler" 
			:data="data"
			v-bind:key="item.id"
		>		
		</datatable-action-button>
	</div>
</template>

<script>

export default {
    props: {
        data: {},
        name: {},
		meta: {
			type: Object,
			default: () => ({
				buttons: {
					type: Array,
					default: () => ({ 
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
					})
				}
			})
		}
	}
}
</script>