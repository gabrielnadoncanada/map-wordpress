<template>
	<div class="sidebar">
  <SearchBar @search="searchItems"/>

    <h3>Filter Items</h3>
		 <form @submit.prevent="submitFilter">
       <label for="taxonomies">Select items categories:</label>
	    <select id="taxonomies" v-model="selectedTaxonomies" multiple>
	      <option v-for="taxonomy in taxonomies" :key="taxonomy.id" :value="taxonomy.id">
	        {{ taxonomy.name }}
	      </option>
	    </select>
      <button type="submit">Filter</button>
    </form>
		<div class="items--list">
			<Card v-for="item in filteredItems" :key="item.id" :item="item"/>
		</div>
	</div>
	<Map :items="filteredItems"/>
</template>

<script>
import SearchBar from "../components/SearchBarComp.vue";
import Card from "../components/CardComp.vue";
import Map from "../components/MapComp.vue";

export default {
	components: {
		SearchBar,
		Card,
		Map,
	},
	data() {
		return {
			taxonomies: [],
			selectedTaxonomies: [],
			items: [],
			filteredItems: [],
			search: "",
		};
	},
	async created() {
		await this.fetchTaxonomies();
		this.selectedTaxonomies = this.taxonomies.map((taxonomy) => taxonomy.id);
		await this.fetchItems();
	},
	methods: {
		searchItems(search) {
			this.fetchItems(search);
		},
		async fetchTaxonomies() {
			const response = await fetch("http://hydravion.test/wp-json/wp/v2/package_category");
			this.taxonomies = await response.json();
		},
		submitFilter() {
			this.fetchItems(this.search);
		},
		async fetchItems(search = "") {
			const lowerSearch = search.toLowerCase();
			const taxonomyIds = this.selectedTaxonomies.join(",");
			const response = await fetch(`http://hydravion.test/wp-json/package/v1/taxonomy/${taxonomyIds}`);
			const items = await response.json();

			if (search) {
				this.filteredItems = items.filter((item) =>
						item.title.toLowerCase().includes(lowerSearch)
				);
			} else {
				this.filteredItems = items;
			}
		}
	},

};
</script>
