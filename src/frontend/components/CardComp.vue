<template>
  <div class="item--card">
    <img :src="item.thumbnail" alt="item"/>
	  <div class="item--card-text">
			<div class="item--card-text-name">
				{{ item.title }}
			</div>
			<div class="item--card-text-description">
			  {{ item.excerpt }}
			</div>
		</div>
  </div>
</template>

<script>
export default {
	props: {
		item: Object,
	},
};
</script>
