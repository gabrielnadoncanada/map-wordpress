<template>
	<div id="more-menu">
    <div class="more-item" id="geojson">Export to GeoJSON</div>
    <div class="more-item" id="import-geojson">
        <label for="file-geojson">Import GeoJSON</label>
        <input type="file" id="file-geojson" accept=".json">
    </div>
    <div class="more-item" id="logout">Log out</div>
	</div>
	<Sidebar/>
	 <div id="drawing-controls" class="noselect">
    <div
		    v-for="tool in tools"
		    :key="tool.name"
		    class="tool"
		    :id="tool.name + '-tool'"
		    :class="{ 'tool-active': selectedTool === tool.name }"
		    @click="selectTool(tool.name)"
		    @mouseover="showTooltip"
		    @mouseleave="hideTooltip"
    >
      <img :src="'assets/img/' + tool.name + '-tool.svg'">
	  <div class="tooltip" :id="tool.name + '-tooltip'">{{ tool.tooltip }}</div>
    </div>
    <div id="color-picker">
      <div id="inner-color" @click="toggleColor"></div>
      <div id="color-list">
        <div
		        v-for="(color, index) in colors"
		        :key="'color' + (index + 1)"
		        class="color"
		        :id="'color' + (index + 1)"
		        :data-color="color"
		        @click="switchColor"
        ></div>
      </div>
    </div>
  </div>
  <div id="mapDiv" class="map-container">
    <l-map :zoom="zoom" :center="center" @ready="mapReady">
      <l-tile-layer
		      :max-zoom="maxZoom"
		      :zoom-control="zoomControl"
		      :min-zoom="minZoom"
		      :no-wrap="noWrap"
		      :url="url"
		      :attribution="attribution"
      />
	    <!--      <l-marker v-for="(marker, idx) in markers" :key="idx" :lat-lng="marker"></l-marker>-->
	    <!--      <l-polyline v-for="(line, idx) in polylines" :key="idx" :lat-lngs="line.points" :color="'red'"></l-polyline>-->
	    <!--      <l-polygon v-for="(polygon, idx) in polygons" :key="idx" :lat-lngs="polygon.points" :color="'blue'"></l-polygon>-->
    </l-map>
  </div>
</template>

<script>
import L from 'leaflet';
import {LMap, LTileLayer, LMarker, LPolyline, LPolygon} from "@vue-leaflet/vue-leaflet";
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import "leaflet/dist/leaflet.css";
import Sidebar from "./SidebarComp.vue";
import ZoomComp from "./ZoomComp.vue";

export default {
	components: {
		ZoomComp,
		LMap,
		LTileLayer,
		LMarker,
		LPolyline,
		LPolygon,
		Sidebar
	},
	data() {
		return {
			zoom: 5,
			maxZoom: 18,
			minZoom: 3,
			noWrap: true,
			zoomControl: false,
			center: [46.8139, -71.208],
			url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
			objects: [],
			map: null,
			selectedTool: "",
			currentid: 0,
			linedistance: 0,
			linelastcoord: [0, 0],
			cursorcoords: [0, 0],
			enteringdata: false,
			drawing: false,
			erasing: false,
			markerson: false,
			lineon: false,
			color: "#634FF1",
			colors: ["#EC1D43", "#EC811D", "#ECBE1D", "#B6EC1D", "#1DA2EC", "#781DEC", "#CF1DEC", "#222222"],
			tools: [
				{name: "cursor", action: "cursorTool", tooltip: "Move (V)"},
				{name: "pen", action: "penTool", tooltip: "Pencil (P)"},
				{name: "eraser", action: "eraserTool", tooltip: "Eraser (E)"},
				{name: "marker", action: "markerTool", tooltip: "Marker (M)"},
				{name: "path", action: "pathTool", tooltip: "Line (L)"},
				{name: "area", action: "areaTool", tooltip: "Area (A)"},
			],
		};
	},
	methods: {
		mapReady(map) {
			this.map = map;
			this.map.setMaxBounds([[84.67351256610522, -174.0234375], [-58.995311187950925, 223.2421875]]);
			this.map.removeControl(this.map.zoomControl);
			L.PM.setOptIn(true);
			var followcursor = L.marker([0, 0], {pane: "overlayPane", interactive: false}).addTo(this.map);
			followcursor.setOpacity(0);
			var tooltip = followcursor.bindTooltip("", {
				permanent: true,
				offset: [5, 25],
				sticky: true,
				className: "hints",
				direction: "right"
			}).addTo(this.map);
			followcursor.closeTooltip();

			this.map.on('pm:drawstart', ({workingLayer}) => {
				followcursor.openTooltip();
				followcursor.setTooltipContent("Click to place first vertex");

				workingLayer.on('pm:vertexadded', e => {
					if (e.shape == "Polygon") {
						followcursor.setTooltipContent("Click on first vertex to finish");

						this.linelastcoord = e.layer._latlngs[e.layer._latlngs.length - 1];

						if (e.layer._latlngs.length == 1) {
							// // If this is the first vertex, get a key and add the new shape in the database
							// currentid = db.ref("rooms/" + room + "/objects").push().key;
							// db.ref("rooms/" + room + "/objects/" + currentid).set({
							// 	color: color,
							// 	initlat: e.layer._latlngs[0].lat,
							// 	initlng: e.layer._latlngs[0].lng,
							// 	user: user.uid,
							// 	type: "area",
							// 	session: session,
							// 	name: "Area",
							// 	desc: "",
							// 	distance: 0,
							// 	area: 0,
							// 	completed: false,
							// 	path: ""
							// });
							// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
							// 	set: [linelastcoord.lat, linelastcoord.lng]
							// });
							this.objects.push({
								id: this.currentid,
								local: true,
								color: this.color,
								name: "Area",
								desc: "",
								trigger: "",
								distance: 0,
								area: 0,
								layer: "",
								type: "area",
								completed: false
							});
						} else {
							// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
							// 	set: [linelastcoord.lat, linelastcoord.lng]
							// })
						}
					} else if (e.shape == "Line") {
						this.lineon = true;
						this.linedistance = 0;
						this.linelastcoord = e.layer._latlngs[e.layer._latlngs.length - 1];
						if (e.layer._latlngs.length == 1) {
							// currentid = db.ref("rooms/" + room + "/objects").push().key;
							// db.ref("rooms/" + room + "/objects/" + currentid).set({
							// 	color: color,
							// 	initlat: e.layer._latlngs[0].lat,
							// 	initlng: e.layer._latlngs[0].lng,
							// 	user: user.uid,
							// 	type: "line",
							// 	session: session,
							// 	name: "Line",
							// 	desc: "",
							// 	distance: 0,
							// 	completed: false,
							// 	path: ""
							// });
							// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
							// 	set: [linelastcoord.lat, linelastcoord.lng]
							// });
							this.objects.push({
								id: this.currentid,
								local: true,
								color: this.color,
								name: "Line",
								desc: "",
								trigger: "",
								distance: 0,
								layer: "",
								type: "line",
								completed: false
							});
						} else {
							e.layer._latlngs.forEach((coordinate, index) => {
								if (index !== 0) {
									this.linedistance += e.layer._latlngs[index - 1].distanceTo(coordinate);
								}
							});

							followcursor.setTooltipContent((this.linedistance / 1000) + "km | Double click to finish");
							// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
							// 	set: [linelastcoord.lat, linelastcoord.lng]
							// })
						}
					}
				});
			});

			this.map.on('pm:drawend', e => {
				this.lineon = false;
				followcursor.closeTooltip();
				this.cursorTool();
			});

			this.map.on('pm:create', e => {
				this.enteringdata = true;
				var inst = this.objects.filter((result) => {
					return result.id === this.currentid;
				})[0];

				inst.distance = parseFloat(turf.length(e.layer.toGeoJSON()).toFixed(2));
				inst.layer = e.layer;
				if (inst.type == "area") {
					inst.area = parseFloat((turf.area(e.layer.toGeoJSON()) * 0.000001).toFixed(2));

					var temppath = [];
					Object.values(e.layer.getLatLngs()[0]).forEach(function (a) {
						temppath.push([Object.values(a)[0], Object.values(a)[1]]);
					})

					// db.ref("rooms/" + room + "/objects/" + currentid).update({
					// 	path: temppath,
					// 	area: inst.area
					// })
				} else if (inst.type == "line") {
					var temppath = [];
					Object.values(e.layer.getLatLngs()).forEach(function (a) {
						temppath.push([Object.values(a)[0], Object.values(a)[1]]);
					})

					// db.ref("rooms/" + room + "/objects/" + currentid).update({
					// 	path: temppath
					// })
				}

				var centermarker = L.marker(e.layer.getBounds().getCenter(), {
					zIndexOffset: 9999,
					interactive: false,
					pane: "overlayPane"
				});

				centermarker.bindTooltip('<label for="shape-name">Name</label><input value="' + inst.name + '" id="shape-name" name="shape-name" /><label for="shape-desc">Description</label><textarea id="shape-desc" name="description"></textarea><br><div id="buttons"><button class="cancel-button">Cancel</button><button class="save-button">Save</button></div><div class="arrow-down"></div>', {
					permanent: true,
					direction: "top",
					interactive: true,
					bubblingMouseEvents: false,
					className: "create-shape-flow create-form",
					offset: L.point({x: -15, y: 18})
				});

				centermarker.setOpacity(0);
				centermarker.addTo(this.map);
				centermarker.openTooltip();

				document.getElementById("shape-name").focus();
				document.getElementById("shape-name").select();

				inst.trigger = centermarker;

				e.layer.on("click", (e) => {
					if (!this.erasing) {
						centermarker.setLatLng(this.cursorcoords);
						centermarker.openTooltip();
					} else {
						inst.trigger.remove();
						e.layer.remove();
						// db.ref("rooms/" + room + "/objects/" + inst.id).remove();
						this.objects = this.objects.filter(function (e) {
							return e.id !== inst.id;
						});
						const element = document.querySelector(`.annotation-item[data-id='${inst.id}']`);
						if (element) {
							element.remove();
						}
					}
				});

				centermarker.on('tooltipclose', (e) => {
					if (this.enteringdata) {
						this.cancelForm();
					}

					const parentElement = document.querySelector(`.annotation-item[data-id='${inst.id}']`);
					if (parentElement) {
						const childElement = parentElement.querySelector('.annotation-name span');
						if (childElement) {
							childElement.classList.remove('annotation-focus');
						}
					}
				});
			});

			this.map.addEventListener('mousedown', (event) => {
				this.mousedown = true;
				let lat = Math.round(event.latlng.lat * 100000) / 100000;
				let lng = Math.round(event.latlng.lng * 100000) / 100000;
				this.cursorcoords = [lat, lng];
				if (this.drawing) {
					this.startDrawing(lat, lng);
				}
			});

			this.map.addEventListener('click', (event) => {
				let lat = Math.round(event.latlng.lat * 100000) / 100000;
				let lng = Math.round(event.latlng.lng * 100000) / 100000;
				this.cursorcoords = [lat, lng];

				this.createMarker(lat, lng);
				if (this.drawing) {
					this.startDrawing(lat, lng);
				}

			});
			this.map.addEventListener('mouseup', (event) => {
				this.mousedown = false;
			})
			this.map.addEventListener('mousemove', (event) => {
				let lat = Math.round(event.latlng.lat * 100000) / 100000;
				let lng = Math.round(event.latlng.lng * 100000) / 100000;
				this.cursorcoords = [lat, lng];

				followcursor.setLatLng([lat, lng]);
				if (this.mousedown && this.drawing) {
					this.objects.filter((result) => {
						return result.id === this.currentid;
					})[0].line.addLatLng([lat, lng]);

					// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
					// 	set: [lat, lng]
					// })
				}

				if (this.lineon) {
					followcursor.setTooltipContent(((this.linedistance + this.linelastcoord.distanceTo([lat, lng])) / 1000).toFixed(2) + "km | Double click to finish");
				}
				if (typeof lat != undefined && typeof lng != undefined) {
					if (!this.dragging) {
						// db.ref('rooms/' + room + '/users/' + user.uid).update({
						// 	lat: lat,
						// 	lng: lng,
						// 	view: [map.getBounds().getCenter().lat, map.getBounds().getCenter().lng]
						// });
					} else {
						// db.ref('rooms/' + room + '/users/' + user.uid).update({
						// 	view: [map.getBounds().getCenter().lat, map.getBounds().getCenter().lng]
						// });
					}
				}

			});
			this.map.addEventListener('zoom', (event) => {
				// db.ref('rooms/' + room + '/users/' + user.uid).update({
				// 	view: [map.getBounds().getCenter().lat, map.getBounds().getCenter().lng],
				// 	zoom: map.getZoom()
				// });
			});
			this.map.addEventListener('movestart', (event) => {
				this.dragging = true;
			});
			this.map.addEventListener('moveend', (event) => {
				this.dragging = false;
			});

			document.addEventListener("click", (event) => {
				if (event.target && event.target.classList.contains("save-button")) {
					this.saveForm(event);
				}
				if (event.target && event.target.classList.contains("delete-layer")) {
					this.deleteLayer(event);
				}
			});
		},
		cancelForm() {
			this.enteringdata = false;
			var inst = this.objects.filter((result) => {
				return result.id === this.currentid;
			})[0];

			// Delete existing popup (for inputting data)
			inst.trigger.unbindTooltip();
			inst.completed = true;
			if (inst.type == "area") {
				// Create a popup showing info about the area
				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/area-icon.svg">' + inst.area + ' km&sup2;</h3></div><div class="shape-data"><h3><img src="assets/perimeter-icon.svg">' + inst.distance + ' km</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: -15, y: 18})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	area: inst.area,
				// 	distance: inst.distance,
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			} else if (inst.type == "line") {
				// Create a popup showing info about the line
				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/distance-icon.svg">' + inst.distance + ' km</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: -15, y: 18})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	distance: inst.distance,
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			} else if (inst.type == "marker") {
				// Create a popup showing info about the marker
				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/marker-small-icon.svg">' + inst.lat.toFixed(5) + ', ' + inst.lng.toFixed(5) + '</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: 0, y: -35})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			}

			this.renderObjectLayer(inst);
			const parentElement = document.querySelector(`.annotation-item[data-id='${inst.id}']`);
			if (parentElement) {
				const childElement = parentElement.querySelector('.annotation-name span');
				if (childElement) {
					childElement.classList.add('annotation-focus');
				}
			}

			window.setTimeout(function () {
				inst.trigger.openTooltip();
			}, 200)
		},
		saveForm(e) {
			this.enteringdata = false;
			var inst = this.objects.filter((result) => {
				return result.id === this.currentid;
			})[0];

			inst.name = this.sanitize(document.querySelector("#shape-name").value);
			inst.desc = this.sanitize(document.querySelector("#shape-desc").value);
			inst.completed = true;


			// inst.trigger.unbindTooltip();
			if (inst.type == "area") {

				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/img/area-icon.svg">' + inst.area + ' km&sup2;</h3></div><div class="shape-data"><h3><img src="assets/img/perimeter-icon.svg">' + inst.distance + ' km</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: -15, y: 18})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	area: inst.area,
				// 	distance: inst.distance,
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			} else if (inst.type == "line") {
				// Create a popup showing info about the line
				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/img/distance-icon.svg">' + inst.distance + ' km</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: -15, y: 18})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	distance: inst.distance,
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			} else if (inst.type == "marker") {
				inst.trigger.bindTooltip('<h1>' + inst.name + '</h1><h2>' + inst.desc + '</h2><div class="shape-data"><h3><img src="assets/img/marker-small-icon.svg">' + inst.lat.toFixed(5) + ', ' + inst.lng.toFixed(5) + '</h3></div><div class="arrow-down"></div>', {
					permanent: false,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow",
					offset: L.point({x: 0, y: -35})
				});
				// db.ref("rooms/" + room + "/objects/" + currentid).update({
				// 	name: inst.name,
				// 	desc: inst.desc,
				// 	completed: true
				// })
			}

			this.renderObjectLayer(inst);
			document.querySelector('.annotation-item[data-id="' + inst.id + '"] .annotation-name span').classList.add('annotation-focus');
			window.setTimeout(function () {
				inst.trigger.openTooltip();
			}, 200)

		},
		renderObjectLayer(object) {
			const parentElement = document.getElementById('annotations-list');
			const newElement = document.createElement('div');

			if (document.querySelectorAll('.annotation-item[data-id="' + object.id + '"]').length === 0) {
				if (object.type == "line") {
					if (parentElement && newElement) {
						const icon = '<svg class="annotation-icon" width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="23" height="23" rx="5" fill="' + object.color + '"/><path d="M14.5 8.5L8.5 14.5" stroke="white" stroke-width="1.5" stroke-linecap="square"/><path d="M15.8108 8.53378C16.7176 8.53378 17.4527 7.79868 17.4527 6.89189C17.4527 5.9851 16.7176 5.25 15.8108 5.25C14.904 5.25 14.1689 5.9851 14.1689 6.89189C14.1689 7.79868 14.904 8.53378 15.8108 8.53378Z" stroke="white" stroke-width="1.5"/><circle cx="6.89189" cy="15.8108" r="1.64189" stroke="white" stroke-width="1.5"/></svg>'
						newElement.innerHTML = '<div class="annotation-item" data-id="' + object.id + '"><div class="annotation-name"><img class="annotation-arrow" src="assets/img/arrow.svg">' + icon + '<span>' + object.name + '</span><img class="delete-layer" src="assets/img/delete.svg"></div><div class="annotation-details annotation-closed"><div class="annotation-description">' + object.desc + '</div><div class="annotation-data"><div class="annotation-data-field"><img src="assets/img/distance-icon.svg">' + object.distance + ' km</div></div></div></div>';
						parentElement.insertBefore(newElement, parentElement.firstChild);
					}
				} else if (object.type == "area") {
					const icon = '<svg class="annotation-icon" width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="23" height="23" rx="5" fill="' + object.color + '"/><path d="M15.3652 8.5V13.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/><path d="M8.5 15.3649H13.5" stroke="white" stroke-width="1.5" stroke-linecap="round"/><path d="M14.5303 9.03033C14.8232 8.73744 14.8232 8.26256 14.5303 7.96967C14.2374 7.67678 13.7626 7.67678 13.4697 7.96967L14.5303 9.03033ZM7.96967 13.4697C7.67678 13.7626 7.67678 14.2374 7.96967 14.5303C8.26256 14.8232 8.73744 14.8232 9.03033 14.5303L7.96967 13.4697ZM13.4697 7.96967L7.96967 13.4697L9.03033 14.5303L14.5303 9.03033L13.4697 7.96967Z" fill="white"/><circle cx="15.365" cy="6.85135" r="1.60135" stroke="white" stroke-width="1.5"/><circle cx="15.365" cy="15.3649" r="1.60135" stroke="white" stroke-width="1.5"/><circle cx="6.85135" cy="15.3649" r="1.60135" stroke="white" stroke-width="1.5"/></svg>';
					newElement.innerHTML = '<div class="annotation-item" data-id="' + object.id + '"><div class="annotation-name"><img class="annotation-arrow" src="assets/img/arrow.svg">' + icon + '<span>' + object.name + '</span><img class="delete-layer" src="assets/img/delete.svg"></div><div class="annotation-details annotation-closed"><div class="annotation-description">' + object.desc + '</div><div class="annotation-data"><div class="annotation-data-field"><img src="assets/img/area-icon.svg">' + object.area + ' km&sup2;</div><div class="annotation-data-field"><img src="assets/img/perimeter-icon.svg">' + object.distance + ' km</div></div></div></div>';
					parentElement.insertBefore(newElement, parentElement.firstChild);
				} else if (object.type == "marker") {
					const icon = '<svg class="annotation-icon" width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="23" height="23" rx="5" fill="' + object.color + '"/><path d="M16.0252 11.2709C16.0252 14.8438 11.3002 17.9063 11.3002 17.9063C11.3002 17.9063 6.5752 14.8438 6.5752 11.2709C6.5752 10.0525 7.07301 8.8841 7.95912 8.0226C8.84522 7.16111 10.047 6.67712 11.3002 6.67712C12.5533 6.67712 13.7552 7.16111 14.6413 8.0226C15.5274 8.8841 16.0252 10.0525 16.0252 11.2709Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M11.2996 12.8021C12.1695 12.8021 12.8746 12.1166 12.8746 11.2709C12.8746 10.4252 12.1695 9.73962 11.2996 9.73962C10.4298 9.73962 9.72461 10.4252 9.72461 11.2709C9.72461 12.1166 10.4298 12.8021 11.2996 12.8021Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
					newElement.innerHTML = '<div class="annotation-item" data-id="' + object.id + '"><div class="annotation-name"><img class="annotation-arrow" src="assets/img/arrow.svg">' + icon + '<span>' + object.name + '</span><img class="delete-layer" src="assets/img/delete.svg"></div><div class="annotation-details annotation-closed"><div class="annotation-description">' + object.desc + '</div><div class="annotation-data"><div class="annotation-data-field"><img src="assets/img/marker-small-icon.svg">' + object.lat.toFixed(5) + ', ' + object.lng.toFixed(5) + '</div></div></div></div>';
					parentElement.insertBefore(newElement, parentElement.firstChild);
				}
			} else {
				const layer = document.querySelector('.annotation-item[data-id="' + object.id + '"]');
				if (object.type == "line") {
					layer.querySelector('.annotation-name span').innerHTML = object.name;
					layer.querySelector('.annotation-description').innerHTML = object.desc;
					layer.querySelector('.annotation-data').innerHTML = '<div class="annotation-data-field"><img src="assets/img/distance-icon.svg">' + object.distance + ' km</div>';
				} else if (object.type == "area") {
					layer.querySelector('.annotation-name span').innerHTML = object.name;
					layer.querySelector('.annotation-description').innerHTML = object.desc;
					layer.querySelector('.annotation-data').innerHTML = '<div class="annotation-data-field"><img src="assets/img/area-icon.svg">' + object.area + ' km&sup2;</div><div class="annotation-data-field"><img src="assets/img/perimeter-icon.svg">' + object.distance + ' km</div>';
				} else if (object.type == "marker") {
					layer.querySelector('.annotation-name span').innerHTML = object.name;
					layer.querySelector('.annotation-description').innerHTML = object.desc;
					layer.querySelector('.annotation-data').innerHTML = '<div class="annotation-data-field"><img src="assets/img/marker-small-icon.svg">' + object.lat.toFixed(5) + ', ' + object.lng.toFixed(5) + '</div>';
				}
			}
		},
		sanitize(string) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#x27;',
				"/": '&#x2F;',
			};
			const reg = /[&<>"'/]/ig;
			return string.replace(reg, (match) => (map[match]));
		},
		startDrawing(lat, lng) {
			var line = L.polyline([[lat, lng]], {color: this.color});

			// this.currentid = db.ref("rooms/" + room + "/objects").push().key;
			// db.ref("rooms/" + room + "/objects/" + currentid).set({
			// 	color: color,
			// 	initlat: lat,
			// 	initlng: lng,
			// 	user: user.uid,
			// 	type: "draw",
			// 	session: session,
			// 	completed: true
			// });
			// db.ref("rooms/" + room + "/objects/" + currentid + "/coords/").push({
			// 	set: [lat, lng]
			// })

			this.objects.push({
				id: this.currentid,
				line: line,
				local: true,
				completed: true,
				type: "draw"
			});
			line.addTo(this.map);

			this.objects.forEach((inst) => {
				inst.line.on("click", (event) => {
					if (this.erasing) {
						inst.line.remove();
						// db.ref("rooms/" + room + "/objects/" + inst.id).remove();
						this.objects = Array.from(this.objects).filter((e) => {
							return e.dataset.id != inst.id;
						});

						const elementToRemove = document.querySelector(`.annotation-item[data-id='${inst.id}']`);
						if (elementToRemove) {
							elementToRemove.remove();
						}
					}
				});
				inst.line.on("mouseover", (event) => {
					if (this.erasing) {
						inst.line.setStyle({opacity: .3});
					}
				});
				inst.line.on("mouseout", function (event) {
					inst.line.setStyle({opacity: 1});
				});
			});
		},
		createMarker(lat, lng) {
			if (this.markerson) {
				this.cursorTool();

				var marker_icon = L.divIcon({
					html: '<svg width="30" height="30" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M23 44.0833C23 44.0833 40.25 32.5833 40.25 19.1666C40.25 14.5916 38.4326 10.204 35.1976 6.96903C31.9626 3.73403 27.575 1.91663 23 1.91663C18.425 1.91663 14.0374 3.73403 10.8024 6.96903C7.56741 10.204 5.75 14.5916 5.75 19.1666C5.75 32.5833 23 44.0833 23 44.0833ZM28.75 19.1666C28.75 22.3423 26.1756 24.9166 23 24.9166C19.8244 24.9166 17.25 22.3423 17.25 19.1666C17.25 15.991 19.8244 13.4166 23 13.4166C26.1756 13.4166 28.75 15.991 28.75 19.1666Z" fill="' + this.color + '"/></svg>',
					iconSize: [30, 30],
					iconAnchor: [15, 30],
					shadowAnchor: [4, 62],
					popupAnchor: [-3, -76]
				});
				var marker = L.marker([lat, lng], {
					icon: marker_icon,
					direction: "top",
					interactive: true,
					pane: "overlayPane"
				});

				marker.bindTooltip('<label for="shape-name">Name</label><input value="Marker" id="shape-name" name="shape-name" /><label for="shape-desc">Description</label><textarea id="shape-desc" name="description"></textarea><br><div id="buttons"><button class="cancel-button">Cancel</button><button class="save-button">Save</button></div><div class="arrow-down"></div>', {
					permanent: true,
					direction: "top",
					interactive: false,
					bubblingMouseEvents: false,
					className: "create-shape-flow create-form",
					offset: L.point({x: 0, y: -35})
				});
				marker.addTo(this.map);
				marker.openTooltip();

				// Create a new key for the marker, and add it to the database
				// this.currentid = db.ref("rooms/" + room + "/objects").push().key;
				var key = this.currentid;
				// db.ref("rooms/" + room + "/objects/" + currentid).set({
				// 	color: color,
				// 	lat: lat,
				// 	lng: lng,
				// 	user: user.uid,
				// 	type: "marker",
				// 	m_type: "none",
				// 	session: session,
				// 	name: "Marker",
				// 	desc: ""
				// });
				this.objects.push({
					id: this.currentid,
					color: this.color,
					name: "Marker",
					m_type: "none",
					desc: "",
					lat: lat,
					lng: lng,
					marker: marker,
					trigger: marker,
					completed: true,
					type: "marker"
				});

				marker.on('tooltipclose', (e) => {
					if (this.enteringdata) {
						this.cancelForm();
					} else {
						const element = document.querySelector(`.annotation-item[data-id='${key}'] .annotation-name span`);
						if (element) {
							element.classList.remove("annotation-focus");
						}
					}
				});

				marker.on('click', (e) => {
					console.log(e);
					if (!this.erasing) {
						marker.openTooltip();
					} else {
						marker.remove();
						// db.ref("rooms/" + room + "/objects/" + inst.id).remove();
						this.objects = objects.filter(function (e) {
							return e.id != key;
						});
					}
				})
			}
		},
		deleteLayer(e) {
			e.preventDefault();
			e.stopPropagation();
			const annotationItem = e.target.closest('.annotation-item');
			const id = annotationItem.getAttribute("data-id");
			const inst = this.objects.find(x => x.id === id);
			console.log(this.objects);
			const elementToRemove = document.querySelector(`.annotation-item[data-id='${id}']`);
			if (elementToRemove) {
				elementToRemove.remove();
			}
			if (inst.type != "marker") {
				inst.trigger.remove();
				inst.line.remove();
				// db.ref("rooms/" + room + "/objects/" + inst.id).remove();
				this.objects = objects.filter(function (e) {
					return e.id != inst.id;
				});
			} else {
				inst.marker.remove();
				// db.ref("rooms/" + room + "/objects/" + inst.id).remove();
				this.objects = objects.filter(function (e) {
					return e.id != inst.id;
				});
			}
		},
		showTooltip(e) {
			let target = e.target.classList.contains("tool") ? e.target : e.target.parentElement;
			let tooltip = target.querySelector(".tooltip");
			if (tooltip) {
				tooltip.classList.add("show");
			}
		},
		hideTooltip(e) {
			let target = e.target.classList.contains("tool") ? e.target : e.target.parentElement;
			let tooltip = target.querySelector(".tooltip");
			if (tooltip) {
				tooltip.classList.remove("show");
			}
		},
		selectTool(toolName) {
			if (this[toolName + "Tool"]) {
				this.resetTools();
				this[toolName + "Tool"]();
			}
		},
		resetTools() {
			this.selectedTool = "";
			this.drawing = false;
			this.erasing = false;
			this.markerson = false;
			this.lineon = false;
			this.map.pm.disableDraw();
			this.map.pm.disableGlobalRemovalMode();
			this.map.pm.disableGlobalDragMode();
		},
		cursorTool() {
			this.resetTools();
			this.map.dragging.enable();
			this.selectedTool = "cursor";
		},
		penTool() {
			this.resetTools();
			this.map.dragging.disable();
			this.drawing = true;
			this.selectedTool = "pen";
			this.showAnnotations();
		},
		eraserTool() {
			this.resetTools();
			this.markerson = true;
			this.selectedTool = "eraser";
			this.showAnnotations();
		},
		markerTool() {
			this.resetTools();
			this.markerson = true;
			this.selectedTool = "marker";
			this.showAnnotations();
		},
		pathTool() {
			this.resetTools();
			this.map.pm.setGlobalOptions({pinning: true, snappable: true});
			this.map.pm.setPathOptions({
				color: this.color,
				fillColor: this.color,
				fillOpacity: 0.4,
			});
			this.map.pm.enableDraw('Line', {
				tooltips: false,
				snappable: true,
				templineStyle: {color: this.color},
				hintlineStyle: {color: this.color, dashArray: [5, 5]},
				pmIgnore: false,
				finishOn: 'dblclick',
			});
			this.showAnnotations();
		},
		areaTool() {
			this.resetTools();
			this.map.pm.setGlobalOptions({pinning: true, snappable: true});
			this.map.pm.setPathOptions({
				color: this.color,
				fillColor: this.color,
				fillOpacity: 0.4,
			});
			this.map.pm.enableDraw('Polygon', {
				tooltips: false,
				snappable: true,
				templineStyle: {color: this.color},
				hintlineStyle: {color: this.color, dashArray: [5, 5]},
				pmIgnore: false
			});
			this.showAnnotations();
		},
		toggleColor() {
			document.getElementById("color-list").classList.toggle("color-list-active");
		},
		switchColor(e) {
			this.color = e.target.dataset.color;
			document.getElementById("inner-color").style.backgroundColor = this.color;
			this.toggleColor();
		},
		showAnnotations() {
			document.querySelector(".leaflet-overlay-pane").style.visibility = "visible";
			document.querySelector(".leaflet-overlay-pane").style.pointerEvents = "all";
			document.querySelector(".leaflet-tooltip-pane").style.visibility = "visible";
			document.querySelector(".leaflet-tooltip-pane").style.pointerEvents = "all";
			document.getElementById("hide-annotations").classList.remove('hidden-annotations')
			document.getElementById("hide-annotations").innerHTML = "Hide all";
		}
	}
};
</script>
