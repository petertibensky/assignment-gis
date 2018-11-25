<div class="container">
    <div class="panel ">
        <div class="panel-body">
            <div class="panel-content">
                <form class="form-inline" method="POST" action="{{ route('searchBusRoutesByNumber') }}">
                    {{ csrf_field() }}
                    <div class="form-row">
                        <div class="col-auto {{ $errors->has('busNumber') ? ' has-error' : '' }}">
                            <label for="street" class="control-label">Číslo spoju</label>

                            <div>
                                <input id="busNumber" type="text" class="form-control mb-2 mr-sm-2" name="busNumber"
                                       required
                                       autofocus value="{{ old('busNumber') }}">

                                @if ($errors->has('busNumber'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('busNumber') }}</strong>
                                    </span>
                                @endif

                            </div>
                        </div>
                        {{--<div class="col-auto">
                            <label for="direct" class="control-label">Príjazdová cesta z ulice</label>

                            <div>
                                <input id="direct" type="checkbox" class="form-control" name="direct"
                                       @if(is_array(old('direct')) && in_array(1, old('direct'))) checked @endif>

                            </div>
                        </div>--}}

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

