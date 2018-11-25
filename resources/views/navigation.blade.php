<ul class="nav nav-tabs">
    <li class="{{ $tab == 'menu1' ? 'active' : '' }}"><a data-toggle="tab" href="#menu1">Vyhľadávanie zastávok v okolí budovy</a></li>
    <li class="{{ $tab == 'menu2' ? 'active' : '' }}"><a data-toggle="tab" href="#menu2">Zobrazenie trasy medzi budovami</a></li>
    <li class="{{ $tab == 'menu3' ? 'active' : '' }}"><a data-toggle="tab" href="#menu3">Vyhľadávanie autobusových spojov podľa ulice</a></li>
    <li class="{{ $tab == 'menu4' ? 'active' : '' }}"><a data-toggle="tab" href="#menu4">Vyhľadávanie autobusových spojov podľa čísla spoju</a></li>
</ul>

<div class="tab-content">
    <div id="menu1" class="tab-pane fade {{ $tab == 'menu1' ? 'in active' : '' }}">
        @include('searchbarForm')
    </div>
    <div id="menu2" class="tab-pane fade {{ $tab == 'menu2' ? 'in active' : '' }}">
        @include('searchRouteForm')
    </div>
    <div id="menu3" class="tab-pane fade {{ $tab == 'menu3' ? 'in active' : '' }}">
        @include('searchBusRoute')
    </div>
    <div id="menu4" class="tab-pane fade {{ $tab == 'menu4' ? 'in active' : '' }}">
        @include('searchbusRouteByBusNumber')
    </div>

</div>