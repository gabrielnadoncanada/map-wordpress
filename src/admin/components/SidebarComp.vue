<!-- src/components/Sidebar.vue -->
<template>
	<div id="sidebar">
	  <div id="map-details" class="section">
	      <input id="map-name" value="Cool map 👻" disabled>
	      <input id="map-description" value="Let's all collaborate here">
	      <div id="more-vertical">
	          <img src="assets/img/more-vertical.svg">
	      </div>
	  </div>
	  <div class="section">
	      <div class="section-name">Catégories</div>
	      <table>
	          <tr>
	              <th class="find-nearby" data-type="supermarket" data-color="#5EBE86"
	                  data-others="convenience,deli,organic,markerplace">
	                  <img src="assets/img/groceries-button.svg"><br>
	                  Groceries
	              </th>
	              <th class="find-nearby" data-type="fashion" data-color="#A564D2"
	                  data-others="beauty,art,bicycle,books,carpet,clothes,computer,cosmetics,department_store,electronics,fashion,florist,furniture,garden_centre,general,gift,hardware,jewelry,kiosk,mall,music,shoes,shopping_centre,sports,stationery,toys">
	                  <img src="assets/img/shopping-button.svg"><br>
	                  Shopping
	              </th>
	              <th class="find-nearby" data-type="restaurant" data-color="#4890E8"
	                  data-others="bar,fast-food,seafood,food,organic,deli,confectionery,bakery">
	                  <img src="assets/img/food-button.svg"><br>
	                  Food
	              </th>
	              <th class="find-nearby" data-type="bar" data-color="#F9D458" data-others="beverages,wine,pub,cafe">
	                  <img src="assets/img/drinks-button.svg"><br>
	                  Drinks
	              </th>
	          </tr>
	          <tr>
	              <th class="find-nearby" data-type="atm" data-color="#634FF1"
	                  data-others="bank,coworking,embassy,library,police,post_box,post_office">
	                  <img src="assets/img/services-button.svg"><br>
	                  Services
	              </th>
	              <th class="find-nearby" data-type="hospital" data-color="#E15F59"
	                  data-others="pharmacy,massage,optician,salon,hairdresser,clinic,dentist,doctors,gym">
	                  <img src="assets/img/health-button.svg"><br>
	                  Health
	              </th>
	              <th class="find-nearby" data-type="hotel" data-color="#AC6C48"
	                  data-others="chalet,camp_site,caravan_site,guest_house,hostel,motel">
	                  <img src="assets/img/hotels-button.svg"><br>
	                  Hotels
	              </th>
	              <th class="find-nearby" data-type="station" data-color="#718390">
	                  <img src="assets/img/transport-button.svg"><br>
	                  Transport
	              </th>
	          </tr>
	      </table>
	  </div>
	  <div class="section" id="annotations-section">
	      <div id="annotations-header">
	          <div class="section-name">Annotations</div>
	          <div id="hide-annotations">Hide all</div>
	      </div>
	      <div id="annotations-list">
	      </div>
	  </div>
	  <div class="section" id="attribution-section">
	      <div class="section-name">Made by Alyssa X

	      <button class="save-button">save</button>

	      </div>
	      <div id="attribution">© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>
	          contributors
	      </div>
	  </div>
	</div>
</template>

<script>
export default {
	props: {
		markers: {
			type: Array,
			default: () => [],
		},
		polylines: {
			type: Array,
			default: () => [],
		},
		polygons: {
			type: Array,
			default: () => [],
		},
	},
};
</script>
