<template>
  <div class="search-bar">
    <form @submit.prevent="submitSearch">
      <input
		      type="text"
		      placeholder="Search"
		      v-model="search"
      />
      <button type="submit">Submit</button>
    </form>
  </div>
</template>


<script>
export default {
	data() {
		return {
			search: "",
		};
	},
	methods: {
		submitSearch() {
			this.$emit("search", this.search);
		},
	},
};
</script>
