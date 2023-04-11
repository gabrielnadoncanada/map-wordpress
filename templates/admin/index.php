<base href="https://hydravion.test/wp-content/mu-plugins/map-booking/templates/admin/src/" target="_blank">

<div id="more-menu">
<div class="more-item" id="geojson">Export to GeoJSON</div>
<a href="https://github.com/alyssaxuu/mapus" target="_blank" class="more-item">GitHub repo</a>
<div class="more-item" id="logout">Log out</div>
</div>
<div id="sidebar">
<div id="map-details" class="section">
  <input id="map-name" value="Cool map 👻" disabled>
  <input id="map-description" value="Let's all collaborate here">
  <div id="more-vertical">
    <img src="assets/more-vertical.svg">
  </div>
</div>
<div class="section">
  <div class="section-name">Find nearby</div>
  <table>
    <tr>
      <th class="find-nearby" data-type="supermarket" data-color="#5EBE86"
          data-others="convenience,deli,organic,markerplace">
        <img src="assets/groceries-button.svg"><br>
        Groceries
      </th>
      <th class="find-nearby" data-type="fashion" data-color="#A564D2"
          data-others="beauty,art,bicycle,books,carpet,clothes,computer,cosmetics,department_store,electronics,fashion,florist,furniture,garden_centre,general,gift,hardware,jewelry,kiosk,mall,music,shoes,shopping_centre,sports,stationery,toys">
        <img src="assets/shopping-button.svg"><br>
        Shopping
      </th>
      <th class="find-nearby" data-type="restaurant" data-color="#4890E8"
          data-others="bar,fast-food,seafood,food,organic,deli,confectionery,bakery">
        <img src="assets/food-button.svg"><br>
        Food
      </th>
      <th class="find-nearby" data-type="bar" data-color="#F9D458" data-others="beverages,wine,pub,cafe">
        <img src="assets/drinks-button.svg"><br>
        Drinks
      </th>
    </tr>
    <tr>
      <th class="find-nearby" data-type="atm" data-color="#634FF1"
          data-others="bank,coworking,embassy,library,police,post_box,post_office">
        <img src="assets/services-button.svg"><br>
        Services
      </th>
      <th class="find-nearby" data-type="hospital" data-color="#E15F59"
          data-others="pharmacy,massage,optician,salon,hairdresser,clinic,dentist,doctors,gym">
        <img src="assets/health-button.svg"><br>
        Health
      </th>
      <th class="find-nearby" data-type="hotel" data-color="#AC6C48"
          data-others="chalet,camp_site,caravan_site,guest_house,hostel,motel">
        <img src="assets/hotels-button.svg"><br>
        Hotels
      </th>
      <th class="find-nearby" data-type="station" data-color="#718390">
        <img src="assets/transport-button.svg"><br>
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
  <div class="section-name">Made by Alyssa X</div>
  <div id="attribution">© <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors</div>
</div>
</div>
<div id="drawing-controls" class="noselect">
<div id="cursor-tool" class="tool tool-active">
  <img src="assets/cursor-tool.svg">
</div>
<div id="pen-tool" class="tool">
  <img src="assets/pen-tool.svg">
</div>
<div id="eraser-tool" class="tool">
  <img src="assets/eraser-tool.svg">
</div>
<div id="marker-tool" class="tool">
  <img src="assets/marker-tool.svg">
</div>
<div id="path-tool" class="tool">
  <img src="assets/path-tool.svg">
</div>
<div id="area-tool" class="tool">
  <img src="assets/area-tool.svg">
</div>
<div id="color-picker">
  <div id="inner-color"></div>
  <div id="color-list">
    <div class="color" id="color1" data-color="#E15F59"></div>
    <div class="color" id="color2" data-color="#F29F51"></div>
    <div class="color" id="color3" data-color="#F9D458"></div>
    <div class="color" id="color4" data-color="#5EBE86"></div>
    <div class="color" id="color5" data-color="#4890E8"></div>
    <div class="color" id="color6" data-color="#634FF1"></div>
    <div class="color" id="color7" data-color="#A564D2"></div>
    <div class="color" id="color8" data-color="#222222"></div>
  </div>
</div>
</div>
<div id="location-control">
<img src="assets/location-icon.svg">
</div>
<div id="zoom-controls" class="noselect">
<div id="zoom-in"><img src="assets/zoomin.svg"></div>
<div id="zoom-out"><img src="assets/zoomout.svg"></div>
</div>
<div id="outline">
<div id="observing-name"></div>
</div>
<div id="mapDiv"></div>
