<div class="container">
    <div class="panel ">
        <div class="panel-body">
            <div class="panel-content">
                <form class="form-inline" method="POST" action="{{ route('searchBusStops') }}">
                    {{ csrf_field() }}
                    <div class="form-row">
                        <div class="col-auto {{ $errors->has('text') ? ' has-error' : '' }}">
                            <label for="text" class="control-label">Názov budovy</label>

                            <div>
                                <input id="text" type="text" class="form-control mb-2 mr-sm-2" name="text"
                                       required
                                       autofocus value="{{ old('text') }}">

                                @if ($errors->has('text'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('text') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>
                        <div class="col-auto">
                            <label for="distance" class="control-label">Maximálna vzdialenosť (v
                                metroch)</label>

                            <div>
                                <input id="distance" type="range" class="form-control mb-2 mr-sm-2" min="0"
                                       max="500" name="distance" value="{{ old('distance', 200) }}"
                                       oninput="this.form.distanceNumber.value=this.value">
                                <input id="distance-number" type="text" class="form-control" name="distanceNumber"
                                       value="{{ old('distance', 200) }}"
                                       oninput="this.form.distance.value=this.value" readonly size="3"/>

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                                Hľadať
                            </button>

                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

