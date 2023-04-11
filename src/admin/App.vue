<!-- src/App.vue -->
<template>
	<div id="overlay"></div>
	<Map/>
</template>

<script>
import Map from "./components/MapComp.vue";

export default {
	components: {
		Map,
	},
};
</script>
