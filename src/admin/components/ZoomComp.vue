<template>
		<div id="zoom-controls" class="noselect">
    <div id="zoom-in" @click="zoomIn"><img src="assets/img/zoomin.svg"></div>
    <div id="zoom-out" @click="zoomOut"><img src="assets/img/zoomout.svg"></div>
	</div>
</template>


<script>
export default {
	props: {
		map: {
			required: true
		}
	},
	methods: {
		zoomIn() {
			this.map.zoomIn();
		},
		zoomOut() {
			this.map.zoomOut();
		},
	},
};
</script>